<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\FileMarcWrapper;
use Umlts\MarcToolset\MarcKeyCreator;

/**
 * Creates a SQLite database mapping the file position
 * to the OCLC number (MARC 001).
 *
 * This is helpful for big MARC files that consist of thousands or
 * millions of records.
 **/
class MarcMapWriter {

    /**
     * SQLite3 db object
     * @var SQLite3
     */
    private $db;

    /**
     * Query to create the db
     * @var string
     */
    private $create_query = '
        DROP TABLE IF EXISTS `record`;
        CREATE TABLE IF NOT EXISTS `record` (
            `id` integer PRIMARY KEY,
            `key` string,
            `fpos` integer
        )
    ';

    /**
     * MARC_file object
     * @var File_MARC
     */
    private $marc;

    /**
     * Counts the number of mapped records
     * @var int
     */
    private $records_mapped = 0;

    /**
     * Class creating the keys
     * @var MarcMapKeyCreator
     */
    private $key_creator;

    /**
     * Constructs object
     *
     * @param string $marc_file
     *   Path to the MARC file
     * @param SQLite3 $db
     *   SQLite3 DB object
     * @param MarcMapKeyCreator $key_creator
     *   A custom MarcMapKeyCreator
     */
    public function __construct(
        string $marc_file,
        \SQLite3 $db,
        MarcMapKeyCreator $key_creator = NULL
    ) {
        $this->db = $db;

        // Create table if necessary
        $this->db->exec( $this->create_query );

        // Open MARC source file
        try {
            $this->marc = new FileMARCWrapper( $marc_file, \File_MARC::SOURCE_FILE );
        } catch ( \File_MARC_Exception $e ) {
            if ( $e->getCode() === \File_MARC_Exception::ERROR_INVALID_FILE ) {
                throw new \InvalidArgumentException( 'Invalid file "' . $marc_file . '".' );
            } else {
                throw $e;
            }
        }

        if ( !empty( $key_creator ) ) { $this->setKeyCreator( $key_creator ); }
    }

    /**
     * Sets the mapper that creates the keys
     * @param MarcMapKeyCreator $key_creator
     * #return self
     */
    public function setKeyCreator( MarcMapKeyCreator $key_creator ) : self {
        $this->key_creator = $key_creator;
        return $this;
    }

    /**
     * Prepares the DB and tries to keep things as fast as possible.
     *
     * @return MarcMapWriter
     *   Returns this object
     */
    private function prepare() {
        // Trying to speed things up
        $this->db->exec( 'PRAGMA synchronous = OFF' );
        $this->db->exec( 'PRAGMA journal_mode = MEMORY' );
        $this->db->exec( 'BEGIN TRANSACTION' );

        return $this;
    }

    /**
     * Resets the db to reasonable default settings after everything
     * is done.
     *
     * @return MarcMapWriter
     *   Returns this object
     */
    private function finish() {
        // Back to normal
        $this->db->exec( 'END TRANSACTION' );
        $this->db->exec( 'PRAGMA synchronous = NORMAL' );
        $this->db->exec( 'PRAGMA journal_mode = DELETE' );

        return $this;
    }

    /**
     * Creates an index for the DB.
     *
     * @return MarcMapWriter
     *   Returns this object
     */
    private function index() {
        $this->db->exec( 'CREATE INDEX IF NOT EXISTS `key_' . uniqid() . '` ON record( `key` );' );
        return $this;
    }


    /**
     * Maps the records and writes the results into the DB.
     *
     * @return MarcMapWriter
     *   Returns this object
     */
    public function map() {

        $fpos = 0;

        // Prepare the db
        $this->prepare();

        // Create prepared statement
        $stmt = $this->db->prepare( '
            INSERT OR REPLACE INTO record ( key, fpos ) VALUES ( :key, :fpos )
        ' );

        while ( $raw_record = $this->marc->nextRaw() ) {

            if ( empty( $this->key_creator ) ) {
                $keys = MarcMapDefaultKeyCreator::getKeys( $raw_record );
            } else {
                $keys = $this->key_creator->getKeys( $raw_record );
            }

            if ( !empty( $keys ) ) {
                foreach ( $keys as $key ) {
                    $stmt->bindValue( ':key', $key, SQLITE3_TEXT );
                    $stmt->bindValue( ':fpos', $fpos, SQLITE3_INTEGER );
                    $stmt->execute()->finalize();
                }
            }

            // Save the file pointer position for the next run
            $fpos = $this->marc->ftell();

            $this->records_mapped++;
        }


        // Create indices
        $this->index();

        // Finish the db
        $this->finish();

        return $this;

    }

}
