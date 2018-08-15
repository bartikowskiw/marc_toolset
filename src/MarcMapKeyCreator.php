<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

/**
 * Interface for classes creatung keys for the MarcMapWriter class.
 **/
interface MarcMapKeyCreator {
    /**
     * @param string $raw_record
     * @return string[]
     */
    static function getKeys( string $raw_record ) : array;
}
