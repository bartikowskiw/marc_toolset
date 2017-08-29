<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcMask;
use Umlts\MarcToolset\MarcMaskChecker;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks for records
 **/
class MarcFind extends MarcFileToolBase {

    private $mask;
    private $checker;

    public function __construct( string $marc_file = NULL, MarcMask $mask ) {
        parent::__construct( $marc_file );
        $this->setMask( $mask );
    }

    public function setMask( MarcMask $mask ) : self {
        $this->mask = $mask;
        $this->checker = new MarcMaskChecker( $mask );
        return $this;
    }

    public function echoDump( bool $ansi = TRUE, bool $mark_hits = TRUE ) : self {
        $first = TRUE;
        while ( TRUE ) {
            try {
                $record = $this->next();
                if ( !$first ) { echo self::sep; }
                if ( $mark_hits && $ansi ) {
                    $record = $this->checker->markMatching( $record );
                }
                echo MarcDump::dumpRecord( $record, $ansi );
                $first = FALSE;
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        return $this;
    }

    public function echoRaw() : self {
        while ( TRUE ) {
            try {
                $record = $this->next();
                echo $record->toRaw();
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        return $this;
    }

    public function next() {

        while ( $record = $this->marc->next() ) {
            if ( $this->checker->check( $record ) ) { return $record; }
        }

        throw new MarcRecordNotFoundException( 'Record not found.' );

    }

    public function __toString() {
        ob_start();
        $this->echoDump();
        $content = ob_get_clean();
        return $content;
    }

}
