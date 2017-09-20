<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcReplace;
use Umlts\MarcToolset\MarcMask;


$marc_file = __DIR__ . '/../data/random.mrc';

$records = new MarcReplace( $marc_file, new MarcMask( '...', '.', '.', '.', 'beef' ), 'pork' );
echo $records;
echo "\n";

$records = new MarcReplace( $marc_file, new MarcMask( 'ldr', '.', '.', '.', '(.{5})c' ), '\1@' );
echo $records;
echo "\n";
