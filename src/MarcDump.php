<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
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
            AnsiCodes::yellow . '\1' . AnsiCodes::reset
            . '\2'
            . AnsiCodes::magenta . '\3 ' . AnsiCodes::reset,
            $dump
        );

        // Add ANSI Colors to Subfields
        $dump = preg_replace(
            '/_[a-z0-9]/m',
            AnsiCodes::dim . '\0 ' . AnsiCodes::reset,
            $dump
        );

        // Add ANSI Colors to Fields
        $dump = preg_replace(
            '/^(LDR)(.)(.*)/m',
            AnsiCodes::bold . '\1\2\3' . AnsiCodes::reset,
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

        $marc = self::getMarc( $marc_file );

        $first = true;
        while ( $record = $marc->next() ) {
            if ( !$first ) { echo self::sep; }
            $first = FALSE;
            self::dumpRecord( $record, $ansi );
        }

        if ( !self::staticCall() ) { return $this; }
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
