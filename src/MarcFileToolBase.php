<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;

/**
 * Base for MARC file based tools classes.
 **/
class MarcFileToolBase {

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
     * Seperator inbetween the records
     * @var string
     */
    const sep = "\n\n";

    /**
     * Path to MARC file
     * @var string
     */
    protected $marc_file;

    /**
     * File_MARC object
     * @var File_MARC
     */
    protected $marc;

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
    protected function initMarc( string $marc_file = '' ) : \File_MARC {

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
    public function setFile( string $marc_file ) : self {
        $this->marc_file = $marc_file;
        $this->initMarc();
        return $this;
    }

}
