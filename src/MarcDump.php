<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;

/**
 * Dumps the (human readable content) of a MARC file to stdout.
 **/
class MarcDump extends MarcFileToolBase {

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
