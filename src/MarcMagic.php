<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use Umlts\MarcToolset\AnsiCodes;

/**
 * Checks if the file is an actual MARC file.
 *
 * Some information on how to do this can be found here:
 * https://www.miskatonic.org/2012/05/28/marc-magic-for-file/
 *
 *
 **/
class MarcMagic {

    protected $marc_file;

    /**
     * @var string $marc_file
     */
    public function __construct( string $marc_file ) {
        $this->setFile( $marc_file );
    }

    /**
     * @string $marc_file
     * @return self
     */
    public function setFile( string $marc_file ) : self {
        if ( $this->checkFile( $marc_file ) === FALSE ) {
            throw new \InvalidArgumentException( 'Cannot read file ' . $marc_file );
        }
        $this->marc_file = $marc_file;
        return $this;
    }

    /**
     * @var string $marc_file
     * @return bool
     *   Returns if file is valid
     */
    protected final function checkFile( string $marc_file ) : bool {
        $fi = new \SplFileInfo( $marc_file );

        // Returns always TRUE for i.e. Streams
        if ( !$fi->isFile() ) { return TRUE; }

        return $fi->isFile() && $fi->isReadable();
    }

    /**
     * @return bool
     *   Returns if method has been called statically
     */
    protected final function staticCall() : bool {
        return !isset( $this );
    }

    /**
     * @return bool
     *   Returns if the leader is valid
     */
    public final function checkLeader( string $leader ) : bool {
        $regexp = [
            'authority' => '[acdnosx][z]',
            'bib' => '[acdnp][acdefgijkmoprt]',
            'classification' => '[acdn][w]',
            'community' => '[cdn][q]',
            'holding' => '[cdn][uvxy]',
        ];
        $p56 = implode( '|', $regexp );

        return preg_match( "/^\d{5}($p56)..[ a]22\d{5}...4500$/", $leader ) === 1;
    }

    /**
     * Checks the if the file is a valid MARC file.
     *
     * Checks just the leader for now.
     *
     * @param string $marc_file
     * @return bool
     */
    public final function check( string $marc_file = '' ) : bool {

        if ( self::staticCall() ) {
            if ( empty( $marc_file ) ) {
                throw new \InvalidArgumentException( 'No MARC file given.' );
            }
            if ( self::checkFile( $marc_file ) === FALSE ) {
                throw new \InvalidArgumentException( 'Invalid file: ' .  $marc_file );
            }
        } else {
            if ( empty( $marc_file ) ) { $marc_file = $this->marc_file; }
        }

        $fp = fopen( $marc_file, 'r' );
        $leader = fread( $fp, 24 );

        return self::checkLeader( $leader );
    }
}
