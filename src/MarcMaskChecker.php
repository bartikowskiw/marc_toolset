<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
use Umlts\MarcToolset\MarcMask;

class MarcMaskChecker {

    private $mask;

    public function __construct( MarcMask $mask ) {
        $this->mask = $mask;
    }

    public function check( \File_MARC_Record $record ) : bool{
        $fields = $this->getMatchingFields( $record );
        if ( empty( $fields ) ) { return FALSE; }
        foreach ( $fields as $field ) {
            if ( $this->checkField( $field ) ) { return TRUE; }
        }
        return FALSE;
    }

    public function getMatchingFields( \File_MARC_Record $record ) : array {

        $fields = $record->getFields( $this->mask->getTag(), TRUE );
        if ( empty( $fields ) ) { return []; }
        foreach ( $fields as $key => $field ) {
            if ( !$this->checkIndicators( $field ) ) { unset( $fields[ $key ] ); }
        }
        return $fields;
    }

    public function checkIndicators( \File_MARC_Field $field ) : bool {

        // Control fields do not have indicators
        if ( $field->isControlField() ) { return TRUE; }

        return preg_match( '/' . $this->mask->getInd1() . '/', $field->getIndicator( 1 ) )
          && preg_match( '/' . $this->mask->getInd2() . '/', $field->getIndicator( 2 ) );
    }

    public function getMatchingSubfields( \File_MARC_Field $field ) : array {
        $matching = [];
        $subfields = $field->getSubfields();
        foreach ( $subfields as $subfield ) {
            if ( preg_match( '/' . $this->mask->getSubfield() . '/i', $subfield->getCode() ) ) {
                $matching[] = $subfield;
            }
        }
        return $matching;
    }

    public function checkField( \File_MARC_Field $field ) : bool{
        if ( $field->isDataField() ) {
            if ( !$this->checkIndicators( $field ) ) { return FALSE; }
            $subfields = $this->getMatchingSubfields( $field );
            if ( empty( $subfields ) ) { return FALSE; }
            return $this->checkSubfields( $subfields );
        } else {
            return $this->checkControlfield( $field );
        }
    }

    public function markMatching( \File_MARC_Record $record ) : \File_MARC_Record {
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
            AnsiCodes::negative . '\0'  . AnsiCodes::reset,
            $data
        );
        $el->setData( $data );
        return $el;
    }

    public function checkControlfield( \File_MARC_Control_Field $field ) : bool {
        return preg_match( $this->mask->getRegexp(), $field->getData() ) > 0;
    }

    public function checkSubfield( \File_MARC_Subfield $subfield ) : bool {
        return preg_match( $this->mask->getRegexp(), $subfield->getData() ) > 0;
    }

    public function checkSubfields( array $subfields ) : bool {
        if ( empty( $subfields ) ) { return FALSE; }
        foreach ( $subfields as $subfield ) {
            if ( $this->checkSubfield( $subfield ) ) { return TRUE; }
        }
        return FALSE;
    }

}
