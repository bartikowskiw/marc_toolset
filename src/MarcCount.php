<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;

/**
 * Counts the number of records in a MARC file.
 **/
class MarcCount extends MarcFileToolBase {


    /**
     * Counts records in a MARC file.
     *
     * @todo: This is freakin' slow. Improve maybe. Get rid of File_MARC
     * and use plain?
     *
     * @param string $marc_file
     *   Path to file. Optional when called as method. Needed if called
     *   statically.
     * @return int
     *   Returns number of records.
     */
    public final function count( string $marc_file = '' ) : int {

        $count = 0;
        $marc = self::getMarc( $marc_file );

        $first = true;
        while ( $record = $marc->nextRaw() ) { $count++; }

        return $count;
    }

}
