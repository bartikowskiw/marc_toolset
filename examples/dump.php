<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcDump;


$marc_file = __DIR__ . '/../data/random.mrc';
( new MarcDump( $marc_file ) )->dump();

MarcDump::dump( $marc_file );
