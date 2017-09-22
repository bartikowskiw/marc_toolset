<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcDuplicates;


$marc_file = __DIR__ . '/../data/random3.mrc';
( new MarcDuplicates( $marc_file ) )->findDuplicates()->echoDump();

/*MarcDump::dump( $marc_file );*/
