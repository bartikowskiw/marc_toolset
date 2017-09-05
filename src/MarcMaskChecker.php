<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
use Umlts\MarcToolset\MarcMask;

/**
 * Checks if a MARC record or elements of the record (fields,
 * indicators, subfields and contents) match a MarcMask.
 */
class MarcMaskChecker {

    /**
     * @var MarcMask
     */
    private $mask;

    /**
     * Constructs the object.
     *
     * @param MarcMask $mask
     */
    public function __construct( MarcMask $mask ) {
        $this->mask = $mask;
    }

    /**
     * Checks if the MARC record matches the MarcMask.
     *
     * @param File_MARC_Record $record
     * @return bool
     *   Returns if MARC record matches the MarcMask
     */
    public function check( \File_MARC_Record $record ) : bool {
        $fields = $this->getMatchingFields( $record );
        if ( empty( $fields ) ) { return FALSE; }
        foreach ( $fields as $field ) {
            if ( $this->checkField( $field ) ) { return TRUE; }
        }
        return FALSE;
    }

    /**
     * Get fields that match a MarcMask, checks indicators too.
     *
     * @param File_MARC_Record $record
     * @return bool
     *   Returns matching fields
     */
    public function getMatchingFields( \File_MARC_Record $record ) : array {

        $fields = $record->getFields( $this->mask->getTag(), TRUE );
        if ( empty( $fields ) ) { return []; }
        foreach ( $fields as $key => $field ) {
            if ( !$this->checkIndicators( $field ) ) { unset( $fields[ $key ] ); }
        }
        return $fields;
    }

    /**
     * Checks if the field's indicators match.
     *
     * @param File_MARC_Field $field
     * @return bool
     *   Returns if the indicators match
     */
    public function checkIndicators( \File_MARC_Field $field ) : bool {

        // Control fields do not have indicators
        if ( $field->isControlField() ) { return TRUE; }

        return preg_match( '/' . $this->mask->getInd1() . '/', $field->getIndicator( 1 ) )
          && preg_match( '/' . $this->mask->getInd2() . '/', $field->getIndicator( 2 ) );
    }

    /**
     * Gets matching subfields of a field.
     *
     * @param Field_MARC_Field $field
     * @return array
     *   Returns array of matching File_MARC_Subfield object
     */
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

    /**
     * Checks if a field matches the MarcMask.
     *
     * @param File_MARC_Field
     * @return bool
     *   Returns if the field matches the mask.
     */
    public function checkField( \File_MARC_Field $field ) : bool {
        if ( $field->isDataField() ) {
            if ( !$this->checkIndicators( $field ) ) { return FALSE; }
            $subfields = $this->getMatchingSubfields( $field );
            if ( empty( $subfields ) ) { return FALSE; }
            return $this->checkSubfields( $subfields );
        } else {
            return $this->checkControlfield( $field );
        }
    }

    /**
     * Checks if the a MARC control field matches the MarcMask.
     *
     * @param File_MARC_Control_Field $field
     * @return bool
     *   Returns if the control field matches the mask.
     */
    public function checkControlfield( \File_MARC_Control_Field $field ) : bool {
        return preg_match( $this->mask->getRegexp(), $field->getData() ) > 0;
    }

    /**
     * Checks if the a MARC subfield matches the MarcMask.
     *
     * @param File_MARC_Subfield $field
     * @return bool
     *   Returns if the subfield matches the mask.
     */
    public function checkSubfield( \File_MARC_Subfield $subfield ) : bool {
        return preg_match( $this->mask->getRegexp(), $subfield->getData() ) > 0;
    }

    /**
     * Checks if the one of the MARC subfields match the MarcMask.
     *
     * @param array $field
     * @return bool
     *   Returns if any subfield matches the mask.
     */
    public function checkSubfields( array $subfields ) : bool {
        if ( empty( $subfields ) ) { return FALSE; }
        foreach ( $subfields as $subfield ) {
            if ( $this->checkSubfield( $subfield ) ) { return TRUE; }
        }
        return FALSE;
    }

    /**
     * Marks the matching parts of a MARC record. Uses ANSI coloring.
     * Notice: Changes the actual record - be careful when saving.
     *
     * @param File_MARC_Record $record
     * @return File_MARC_Record
     */
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

    /**
     * Marks matching part of an MARC Record element.
     *
     * @param File_MARC_Field|File_MARC_Subfield $el
     * @return File_MARC_Field|File_MARC_Subfield
     */
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

}
