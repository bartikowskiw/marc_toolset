<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;
use Umlts\MarcToolset\MarcMapKeyCreator;
use Umlts\MarcReader\MarcRecordReader;

/**
 * Returns the value of Marc field 001 as key.
 **/
class MarcMapDefaultKeyCreator implements MarcMapKeyCreator {
    /**
     * @param string $raw_record
     * @return string[]
     */
    static function getKeys( string $raw_record ) : array {
        try {
            return [ MarcRecordReader::get001( $raw_record ) ];
        } catch ( \RuntimeException $e ) {
            // Default value for the case the 001 field is empty
            return [ -1 ];
        }
    }
}
