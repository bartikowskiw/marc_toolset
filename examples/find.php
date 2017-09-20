<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcFind;
use Umlts\MarcToolset\MarcMask;


$marc_file = __DIR__ . '/../data/random.mrc';

/*$records = new MarcFind( $marc_file, new MarcMask( '...', '.', '.', '.', 'beef' ) );
echo $records;
echo "\n";*/

$records = new MarcFind( $marc_file, new MarcMask( 'leader', '.', '.', '.', '^.{5}c' ) );
echo $records;
echo "\n";
