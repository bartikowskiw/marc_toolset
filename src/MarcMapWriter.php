<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\FileMarcWrapper;

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
        CREATE TABLE IF NOT EXISTS `record` (
            `id` integer PRIMARY KEY,
            `oclc` integer,
            `fpos` integer
        )
    ';

    /**
     * MARC_file object
     * @var File_MARC
     */
    private $marc;

    /**
     * Counts records with invalid OCLC numbers (= MARC field 001)
     * @var int
     */
    private $invalid_oclc = 0;

    /**
     * Counts the number of mapped records
     * @var int
     */
    private $records_mapped = 0;

    /**
     * Constructs object
     *
     * @param string $marc_file
     *   Path to the MARC file
     * @param SQLite3 $db
     *   SQLite3 DB object
     */
    public function __construct( string $marc_file, \SQLite3 $db ) {
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
        $this->db->exec( 'PRAGMA journal_mode = WAL' );

        return $this;
    }

    /**
     * Creates an index for the DB.
     *
     * @return MarcMapWriter
     *   Returns this object
     */
    private function index() {
        $this->db->exec( 'CREATE INDEX IF NOT EXISTS `oclc` ON record( `oclc` );' );
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
            INSERT OR REPLACE INTO record ( oclc, fpos ) VALUES ( :oclc, :fpos )
        ' );

        while( $record = $this->marc->next() ) {

            // Check if an id is given in MARC 001. If not, skip record.
            if ( empty( $record->getField( '001' ) ) ) {
                $this->invalid_oclc++;
                continue;
            }

            // Parse only the digits of the OCLC number
            preg_match( '/(?P<oclc>[0-9]+)$/', $record->getField( '001' )->getData(), $result );
            $oclc = $result['oclc'];

            $stmt->bindValue( ':oclc', $oclc, SQLITE3_INTEGER );
            $stmt->bindValue( ':fpos', $fpos, SQLITE3_INTEGER );
            $stmt->execute()->finalize();

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
