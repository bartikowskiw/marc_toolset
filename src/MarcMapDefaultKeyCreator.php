<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;
use Umlts\MarcToolset\MarcMapKeyCreator;

use File\MARC;

/**
 * Returns the value of Marc field 001 as key.
 **/
class MarcMapDefaultKeyCreator implements MarcMapKeyCreator {
    /**
     * @param File_MARC_Record $record
     * @return string[]
     */
    static function getKeys( \File_MARC_Record $record ) : array {
        // Default value for the case the 001 field is empty
        if ( empty( $record->getField( '001' ) ) ) { return [ -1 ]; }
        
        return [ $record->getField( '001' )->getData() ];
    }
}
