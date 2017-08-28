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

        $static_call = !isset( $this );
        $count = 0;

        if ( $static_call ) {                                        // Static call

            if ( empty( $marc_file ) ) {
                throw new \InvalidArgumentException( 'No MARC file given.' );
            }
            $marc = self::initMarc( $marc_file );

        } else {                                                        // Called as method

            if ( empty( $this->marc_file ) ) {
                throw new \RuntimeException(
                    'MARC file not set. Use setFile( $path_to_marc_file ) first.'
                );
            }
            $marc = $this->marc;

        }

        $first = true;
        while ( $record = $marc->nextRaw() ) { $count++; }

        return $count;
    }

}
