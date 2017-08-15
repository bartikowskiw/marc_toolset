<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks up a MARC record by OCLC (MARC Tag 001) number and reads it.
 * The Lookup DB can be created with the MarcMapWriter class.
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
     * @param int $oclc
     *   OCLC (MARC field 001) of record to look for
     * @return array
     *   Returns the row. Empty if no match found.
     */
    private function query( int $oclc ) : array {
        
        $stmt = $this->db->prepare( 'SELECT * FROM record WHERE oclc=:oclc' );
        $stmt->bindValue( ':oclc', $oclc, SQLITE3_TEXT );
        $result = $stmt->execute();
        
        $record = $result->fetchArray( SQLITE3_ASSOC );
        if ( $record === FALSE ) { return []; }
        return $record;
    }
    
    /**
     * Get the record with given OCLC number.
     * 
     * @param int $oclc
     *   OCLC (MARC field 001) of record to look for
     * @return File_MARC_Record
     *   Returns the Record
     * @throws MarcRecordNotFoundException
     *   If no match found
     */
    public function get( int $oclc ) : \File_MARC_Record {
        
        $info = $this->query( $oclc );
        if ( empty( $info ) ) {
            throw new MarcRecordNotFoundException( 'Record #' . $oclc . ' not found.'  );
        }
        
        $fp = fopen( $this->marc_file, 'r' );
        fseek( $fp, $info['fpos'] );

        $len = intval( fread( $fp, 5 ) );
        fseek( $fp, -5, SEEK_CUR );
        $marc_binary = fread( $fp, $len );

        $marc = new \File_MARC( $marc_binary, \File_MARC::SOURCE_STRING );
        return $marc->next();
    }
}
