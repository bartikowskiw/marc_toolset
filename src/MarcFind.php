<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;


/**
 * Looks for records
 **/
class MarcFind extends MarcFileToolBase {

    public function find( string $marc_tag, string $regexp = '.*', bool $ansi = TRUE ) : self {
        $records = [];
        $first = TRUE;
        while ( TRUE ) {
            try {
                $record = $this->findNext( $marc_tag, $regexp );
                if ( $first ) {
                    $first = FALSE;
                } else {
                    echo self::sep;
                }
                echo MarcDump::formatDump( (string) $record, $ansi );
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        return $this;
    }


    public function findNext( string $marc_tag, string $regexp = '.*' ) {

        while ( $record = $this->marc->next() ) {
            if ( $this->findInRecord( $record, $marc_tag, $regexp ) ) {
                return $record;
            }
        }

        throw new MarcRecordNotFoundException( 'Record not found.' );

    }

    public function findInRecord( \File_MARC_Record $record, string $marc_tag, string $regexp ) : bool {
        $fields = $record->getFields( $marc_tag, TRUE );
        if ( empty( $fields ) ) { return FALSE; }

        foreach ( $fields as $field ) {
            if ( $this->findInField( $field, $marc_tag, $regexp ) ) { return TRUE; }
        }

        return FALSE;
    }

    public function findInField( \File_MARC_Field $field, string $marc_tag, string $regexp ) {
        if ( $field->isControlField() ) {
            return preg_match( '/' . $regexp . '/i', $field->getData() );
        } else {
            $subfields = $field->getSubfields();
            foreach ( $subfields as $subfield ) {
                if ( preg_match( '/' . $regexp . '/i', $subfield->getData() ) ) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
}
