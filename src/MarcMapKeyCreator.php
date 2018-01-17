<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;

/**
 * Interface for classes creatung keys for the MarcMapWriter class.
 **/
interface MarcMapKeyCreator {
    /**
     * @param File_MARC_Record $record
     * @return string[]
     */
    static function getKeys( \File_MARC_Record $record ) : array;
}
