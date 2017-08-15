<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;

/**
 * Dumps the (human readable content) of a MARC file to stdout.
 **/
class MarcDump {
    
    /**
     * ANSI control strings
     */
    const ANSI_red = "\e[31m";
    const ANSI_yellow = "\e[33m";
    const ANSI_magenta = "\e[95m";
    const ANSI_dim = "\e[2m";
    const ANSI_bold = "\e[1m";
    const ANSI_reset = "\e[0m";
    
    /**
     * Path to MARC file
     * @var string
     */
    private $marc_file;
    
    /**
     * Seperator inbetween the records
     * @var string
     */
    const sep = "\n\n";
    
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
    public function setFile( string $marc_file ) : MarcDump {
        $this->marc_file = $marc_file;
        $this->initMarc();
        return $this;
    }
    
    
    /**
     * Formats the output with ANSI color codes.
     * 
     * @param string $dump
     *   Dump of the File_MARC record
     * @return string
     *   Returns formatted string
     */
    public final function formatDump( string $dump ) : string {
        // Add ANSI Colors to Fields
        $dump = preg_replace(
            '/(^[0-9]+)(.)(..)(.)/m',
            self::ANSI_yellow . '\1' . self::ANSI_reset 
            . '\2'
            . self::ANSI_magenta . '\3 ' . self::ANSI_reset,
            $dump
        );
        
        // Add ANSI Colors to Subfields
        $dump = preg_replace(
            '/_[a-z0-9]/m',
            self::ANSI_dim . '\0 ' . self::ANSI_reset,
            $dump
        );
        
        // Add ANSI Colors to Fields
        $dump = preg_replace(
            '/^(LDR)(.)(.*)/m',
            self::ANSI_bold . '\1\2\3' . self::ANSI_reset,
            $dump
        );
        
        return $dump;
    }
    
    /**
     * Writes the MARC dump to the stdio.
     * 
     * @param string $marc_file
     *   Path to file. Optional when called as method. Needed if called
     *   statically.
     * @return Null|MarcDump
     *   Returns this object if called as method.
     */
    public final function dump( string $marc_file = '', bool $ansi = TRUE ) {
        
        $static_call = !isset( $this );
        
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
        while ( $record = $marc->next() ) {
            if ( !$first ) { echo self::sep; }
            $first = FALSE;
            self::dumpRecord( $record, $ansi );
        }        
        
        if ( !$static_call ) { return $this; }
    }
    
    /**
     * Writes the MARC record dump to the stdio.
     * 
     * @param File_MARC_Record $record
     *   MARC Record
     * @return Null|MarcDump
     *   Returns this object if called as method.
     */
    public final function dumpRecord( \File_MARC_Record $record, bool $ansi=TRUE ) {
        $static_call = !isset( $this );
        
        $dump = (string) $record;
        if ( $ansi ) { $dump = self::formatDump( $dump ); }
        echo $dump;
        if ( !$static_call ) { return $this; }
    }
}
