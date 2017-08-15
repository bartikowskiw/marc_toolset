<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;

/**
 * Wraps File_MARC and exposes the file pointer to the outside
 * world.
 **/
class FileMarcWrapper extends \File_MARC {
    
    /**
     * @return int
     *   Returns position of the file pointer in the file
     **/
    public function ftell() : int { return ftell( $this->source ); }
    
}
