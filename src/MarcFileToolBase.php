<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcMagic;

/**
 * Base for MARC file based tools classes.
 **/
class MarcFileToolBase {

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
            $marc_file = $this->marc_file;git
        }

        // Check if file is an actual MARC file
        if ( MarcMagic::check( $marc_file ) === FALSE ) {
            throw new \InvalidArgumentException( 'Not a valid MARC file "' . $marc_file . '".' );
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

    /**
     * @return bool
     *   Returns if method has been called statically
     */
    protected final function staticCall() : bool {
        return !isset( $this );
    }

    /**
     * Opens the MARC file for further usage.
     *
     * @param string $marc_file
     */
    protected final function getMarc( string $marc_file ) {

        if ( self::staticCall() ) {                                    // Static call

            if ( empty( $marc_file ) ) {
                throw new \InvalidArgumentException( 'No MARC file given.' );
            }
            return self::initMarc( $marc_file );

        } else {                                                        // Called as method

            if ( empty( $this->marc_file ) ) {
                throw new \RuntimeException(
                    'MARC file not set. Use setFile( $path_to_marc_file ) first.'
                );
            }
            return $this->marc;

        }

    }

}
