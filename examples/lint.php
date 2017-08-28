<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcLint;


$marc_file = __DIR__ . '/../data/random.mrc';

( new MarcLint( $marc_file ) )->check();
echo "\n";

MarcLint::check( $marc_file );
