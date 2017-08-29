<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcSearchMask;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks for records
 **/
class MarcFind extends MarcFileToolBase {

    private $mask;

    public function __construct( string $marc_file = NULL ) {
        parent::__construct( $marc_file );
        $this->mask = new MarcSearchMask();
    }

    public function setMask( MarcSearchMask $mask ) : self {
        $this->mask = $mask;
        return $this;
    }

    public function echoDump( bool $ansi = TRUE, bool $mark_hits = TRUE ) : self {
        $first = TRUE;
        while ( TRUE ) {
            try {
                $record = $this->next();
                if ( !$first ) { echo self::sep; }
                if ( $mark_hits ) {
                    $record = $this->markMatching( $record );
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

    public function __toString() {
        ob_start();
        $this->echoDump();
        $content = ob_get_clean();
        return $content;
    }

    public function next() {

        while ( $record = $this->marc->next() ) {
            $fields = $this->getMatchingFields( $record );
            if ( empty( $fields ) ) { continue; }
            foreach ( $fields as $field ) {
                if ( $this->checkField( $field ) ) { return $record; }
            }
        }

        throw new MarcRecordNotFoundException( 'Record not found.' );

    }

    public function getMatchingFields( \File_MARC_Record $record ) {

        $fields = $record->getFields( $this->mask->getTag(), TRUE );
        if ( empty( $fields ) ) { return []; }
        foreach ( $fields as $key => $field ) {
            if ( !$this->checkIndicators( $field ) ) { unset( $fields[ $key ] ); }
        }
        return $fields;
    }

    private function checkIndicators( \File_MARC_Field $field ) : bool {

        // Control fields do not have indicators
        if ( $field->isControlField() ) { return TRUE; }

        return preg_match( '/' . $this->mask->getInd1() . '/', $field->getIndicator( 1 ) )
          && preg_match( '/' . $this->mask->getInd2() . '/', $field->getIndicator( 2 ) );
    }

    private function getMatchingSubfields( \File_MARC_Field $field ) : array {
        $matching = [];
        $subfields = $field->getSubfields();
        foreach ( $subfields as $subfield ) {
            if ( preg_match( '/' . $this->mask->getSubfield() . '/i', $subfield->getCode() ) ) {
                $matching[] = $subfield;
            }
        }
        return $matching;
    }

    private function checkField( \File_MARC_Field $field ) : bool{
        if ( $field->isDataField() ) {
            if ( !$this->checkIndicators( $field ) ) { return FALSE; }
            $subfields = $this->getMatchingSubfields( $field );
            if ( empty( $subfields ) ) { return FALSE; }
            return $this->checkSubfields( $subfields );
        } else {
            return $this->checkControlfield( $field );
        }
    }

    private function markMatching( \File_MARC_Record $record ) : \File_MARC_Record {
        $fields = $record->getFields( $this->mask->getTag(), TRUE );
        if ( empty( $fields ) ) { return $record; }
        foreach ( $fields as $field ) {
            if ( $field->isControlField() ) {
                if ( $this->checkControlfield( $field ) ) {
                    $field = $this->markElement( $field );
                }
            } else {
                $subfields = $this->getMatchingSubfields( $field );
                foreach ( $subfields as $subfield ) {
                    if ( $this->checkSubfield( $subfield ) ) {
                        $subfield = $this->markElement( $subfield );
                    }
                }
            }
        }
        return $record;
    }

    private function markElement( $el ) {
        $data = $el->getData();
        $data = preg_replace(
            $this->mask->getRegexp(),
            self::ANSI_negative . '\0'  . self::ANSI_reset,
            $data
        );
        $el->setData( $data );
        return $el;
    }

    private function checkControlfield( \File_MARC_Control_Field $field ) : bool {
        return preg_match( $this->mask->getRegexp(), $field->getData() ) > 0;
    }

    private function checkSubfield( \File_MARC_Subfield $subfield ) : bool {
        return preg_match( $this->mask->getRegexp(), $subfield->getData() ) > 0;
    }

    private function checkSubfields( array $subfields ) : bool {
        if ( empty( $subfields ) ) { return FALSE; }
        foreach ( $subfields as $subfield ) {
            if ( $this->checkSubfield( $subfield ) ) { return TRUE; }
        }
        return FALSE;
    }

}
