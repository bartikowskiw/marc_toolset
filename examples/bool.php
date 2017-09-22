<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcBool;


$marc_file1 = __DIR__ . '/../data/random.mrc';
$marc_file2 = __DIR__ . '/../data/random2.mrc';

$b = new MarcBool( $marc_file1, $marc_file2 );
$b->boolNot()->echoDump();

echo "-----------\n";

$b = new MarcBool( $marc_file1, $marc_file2 );
$b->boolAnd()->echoDump();
