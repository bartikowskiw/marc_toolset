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
class MarcReplace extends MarcFind {

    public function replaceAndEchoRaw( string $replace ) {
        while ( TRUE ) {
            try {
                $record = $this->replaceNext( $replace );
                echo $record->toRaw();
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
    }

    public function replaceAndEchoDump(
      string $replace, bool $ansi=TRUE, bool $mark_hits = TRUE ) {
        $first = TRUE;
        if ( $mark_hits ) {
            $replace = AnsiCodes::negative . $replace . AnsiCodes::reset;
        }
        while ( TRUE ) {
            try {

                if ( !$first ) { echo self::sep; }
                $first = FALSE;

                $record = $this->replaceNext( $replace );
                echo MarcDump::dumpRecord( $record, $ansi );

            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
    }

    public function replaceNext( string $replace ) : \File_MARC_Record {
        $record = $this->next();
        return $this->replace( $record, $replace );
    }

    public function replace( \File_MARC_Record $record, string $replace ) {
        $fields = $this->checker->getMatchingFields( $record );
        foreach ( $fields as $field ) {
            if ( $field->isControlField() ) {
                $field = $this->replaceControlField( $field, $replace );
            } else {
                $field = $this->replaceDataField( $field, $replace);
            }
        }

        return $record;
    }

    private function replaceControlField(
      \File_MARC_Control_Field $field, string $replace ) : \File_MARC_Control_Field {
        $tmp = preg_replace( $this->mask->getRegExp(), $replace, $field->getData() );
        $field->setData( $tmp );
        return $field;
    }

    private function replaceDataField(
      \File_MARC_Data_Field $field, string $replace ) : \File_MARC_Data_Field {
        $subfields = $this->checker->getMatchingSubfields( $field );
        foreach ( $subfields as $subfield ) {
            $tmp = preg_replace( $this->mask->getRegExp(), $replace, $subfield->getData() );
            $subfield->setData( $tmp );
        }
        return $field;
    }
}
