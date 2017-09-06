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
class MarcReplace extends MarcFileToolBase {

    private $mask;
    private $checker;
    private $replace;

    public function __construct( string $marc_file = NULL, MarcMask $mask, string $replace = '\0' ) {
        parent::__construct( $marc_file );
        $this->setMask( $mask );
        $this->setReplace( $replace );
    }

    public function setReplace( string $replace ) : self {
        $this->replace = $replace;
        return $this;
    }

    public function setMask( MarcMask $mask ) : self {
        $this->mask = $mask;
        $this->checker = new MarcMaskChecker( $mask );
        return $this;
    }

    public function echoRaw() {
        while ( TRUE ) {
            try {
                $record = $this->replaceNext( $this->replace );
                echo $record->toRaw();
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
    }

    public function echoDump( bool $ansi=TRUE, bool $mark_hits = TRUE ) : self {
        $first = TRUE;

        $replace = $this->replace;
        if ( $ansi && $mark_hits ) {
            $this->setReplace( AnsiCodes::negative . $this->replace . AnsiCodes::reset );
        }

        while ( TRUE ) {
            try {

                if ( !$first ) { echo self::sep; }
                $first = FALSE;

                $record = $this->next();
                echo MarcDump::dumpRecord( $record, $ansi );

            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }

        $this->replace = $replace;

        return $this;
    }

    public function next() : \File_MARC_Record {
        while ( $record = $this->marc->next() ) {
            if ( $this->checker->check( $record ) ) {
                return $this->replace( $record );
            }
        }

        throw new MarcRecordNotFoundException( 'Record not found.' );
    }

    public function replace( \File_MARC_Record $record ) {
        $fields = $this->checker->getMatchingFields( $record );
        foreach ( $fields as $field ) {
            if ( $field->isControlField() ) {
                $field = $this->replaceControlField( $field );
            } else {
                $field = $this->replaceDataField( $field );
            }
        }

        return $record;
    }

    private function replaceControlField(
      \File_MARC_Control_Field $field ) : \File_MARC_Control_Field {
        $tmp = preg_replace( $this->mask->getRegExp(), $this->replace, $field->getData() );
        $field->setData( $tmp );
        return $field;
    }

    private function replaceDataField( \File_MARC_Data_Field $field ) : \File_MARC_Data_Field {
        $subfields = $this->checker->getMatchingSubfields( $field );
        foreach ( $subfields as $subfield ) {
            $tmp = preg_replace( $this->mask->getRegExp(), $this->replace, $subfield->getData() );
            $subfield->setData( $tmp );
        }
        return $field;
    }

    public function __toString() {
        ob_start();
        $this->echoDump();
        $content = ob_get_clean();
        return $content;
    }

}
