<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;

/**
 * Dumps the (human readable content) of a MARC file to stdout.
 **/
class MarcLint extends MarcFileToolBase {

    public final function check( string $marc_file = '', bool $ansi=TRUE ) {

        $errors = 0;
        $marc_lint = new \File_MARC_Lint();
        $marc = self::getMarc( $marc_file );

        while ( $record = $marc->next() ) {
            $warnings = $marc_lint->checkRecord( $record );
            if ( !empty( $warnings ) ) {
                self::showWarnings( $record, $warnings );
            }
        }

        if ( !self::staticCall() ) { return $this; }
    }

    private final function showWarnings( \File_MARC_Record $record, array $warnings, bool $ansi=TRUE ) {
        if ( empty( $warnings ) ) { return; }

        $id = '[001 empty]';
        if ( !empty( $record->getField( '001' ) )
          && !empty( $record->getField( '001' )->getData() ) ) {
            $id = $record->getField( '001' )->getData();
        }

        if ( $ansi ) { echo self::ANSI_magenta; }
        echo "001\t$id\n";
        if ( $ansi ) { echo self::ANSI_reset; }

        foreach ( $warnings as $warning ) {
            $warning = preg_replace( '/(^[0-9]+)(:)\s*/m', '\1' . "\t", $warning );
            if ( $ansi ) {
                $warning = preg_replace(
                    '/(^[0-9]+)/m',
                    self::ANSI_yellow . '\1' . self::ANSI_reset,
                    $warning
                );
            }
            echo $warning . "\n";
        }
        echo "\n";
    }

}
