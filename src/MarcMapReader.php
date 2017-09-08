<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks up a MARC record by a key (usually OCLC / MARC Tag 001) and
 * reads it. The Lookup DB can be created with the MarcMapWriter class.
 */
class MarcMapReader {

    /**
     * SQLite3 db object
     * @var SQLite3
     */
    private $db;

    /**
     * Path to MARC file
     * @var string
     */
    private $marc_file;

    /**
     * Constructs object
     *
     * @param string $marc_file
     *   Path to the MARC file
     * @param SQLite3 $db
     *   SQLite3 DB object
     */
    function __construct( string $marc_file, \SQLite3 $db ) {

        $this->db = $db;

        // Open MARC source file
        if ( !is_file( $marc_file ) ) {
            throw new \InvalidArgumentException( 'File "' . $marc_file . '" does not exist.' );
        }
        if ( !is_readable( $marc_file ) ) {
            throw new \InvalidArgumentException( 'File "' . $marc_file . '" is not readable.' );
        }

        $this->marc_file = $marc_file;
    }

    /**
     * Query the DB for the record.
     * This query assumes that there is only one record with this
     * OCLC number. However, if there are more then just the first one
     * in the results will be returned.
     *
     * @param string $key
     *   Key (MARC field 001) of record to look for (usually the OCLC)
     * @return array
     *   Returns the row. Empty if no match found.
     */
    private function query( string $key ) : array {

        $records = array();

        $stmt = $this->db->prepare( 'SELECT * FROM record WHERE key=:key' );
        $stmt->bindValue( ':key', $key, SQLITE3_TEXT );
        $result = $stmt->execute();

        while( $info = $result->fetchArray( SQLITE3_ASSOC ) ) {
            $infos[] = $info;
        }

        return $infos;
    }

    /**
     * Get the record with given key (usually the OCLC number).
     *
     * @param int $oclc
     *   Key (MARC field 001) of record to look for
     * @return File_MARC_Record
     *   Returns the Record
     * @throws MarcRecordNotFoundException
     *   If no match found
     */
    public function get( string $key ) : array {

        $records = [];

        $infos = $this->query( $key );
        if ( empty( $infos ) ) {
            throw new MarcRecordNotFoundException( 'Record #' . $key . ' not found.'  );
        }

        $fp = fopen( $this->marc_file, 'r' );

        foreach ( $infos as $info ) {
            fseek( $fp, $info['fpos'] );

            $len = intval( fread( $fp, 5 ) );
            fseek( $fp, -5, SEEK_CUR );
            $marc_binary = fread( $fp, $len );

            $marc = new \File_MARC( $marc_binary, \File_MARC::SOURCE_STRING );
            $records[] = $marc->next();
        }

        return $records;
    }
}
