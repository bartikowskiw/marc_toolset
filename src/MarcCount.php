<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;

/**
 * Counts the number of records in a MARC file.
 **/
class MarcCount {
    
    /**
     * Path to MARC file
     * @var string
     */
    private $marc_file;
    
    /**
     * Constructor.
     * 
     * @param string $marc_file
     *   Path to MARC file to dump.
     */
    function __construct( string $marc_file = NULL ) {
        if ( !empty ( $marc_file ) ) {
            $this->setFile( $marc_file );
        }
    }
    
    /**
     * Creates the File_MARC object.
     */
    private function initMarc( string $marc_file = '' ) : \File_MARC {
        
        if ( empty( $marc_file ) && isset( $this ) ) {
            $marc_file = $this->marc_file;
        }
        
        // Open MARC source file
        try {
             $marc = new \File_MARC( $marc_file, \File_MARC::SOURCE_FILE );
        } catch ( \File_MARC_Exception $e ) {
            if ( $e->getCode() === \File_MARC_Exception::ERROR_INVALID_FILE ) {
                throw new \InvalidArgumentException( 'Invalid file "' . $marc_file . '".' );
            } else {
                throw $e;
            }
        }
        
        if ( isset( $this ) ) { $this->marc = $marc; }
        
        return $marc;
    }
    
    /**
     * Sets the path to the MARC file to dump.
     * 
     * @param string $marc_file
     *   Path to MARC file.
     * @return MarcDump
     *   Returns this object
     */
    public function setFile( string $marc_file ) : MarcCount {
        $this->marc_file = $marc_file;
        $this->initMarc();
        return $this;
    }
    
    /**
     * Counts records in a MARC file.
     * 
     * @todo: This is freakin' slow. Improve maybe. Get rid of File_MARC
     * and use plain 
     * 
     * @param string $marc_file
     *   Path to file. Optional when called as method. Needed if called
     *   statically.
     * @return int
     *   Returns number of records.
     */
    public final function count( string $marc_file = '' ) : int {
        
        $static_call = !isset( $this );
        $count = 0;
        
        if ( $static_call ) {                                        // Static call
            
            if ( empty( $marc_file ) ) {
                throw new \InvalidArgumentException( 'No MARC file given.' );
            }
            $marc = self::initMarc( $marc_file );
            
        } else {                                                        // Called as method
        
            if ( empty( $this->marc_file ) ) {
                throw new \RuntimeException(
                    'MARC file not set. Use setFile( $path_to_marc_file ) first.'
                );
            }
            $marc = $this->marc;
            
        }
        
        $first = true;
        while ( $record = $marc->nextRaw() ) { $count++; }        
        
        return $count;
    }
    
}
