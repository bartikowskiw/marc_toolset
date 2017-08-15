<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcCount;


$marc_file = __DIR__ . '/../data/random.mrc';

echo ( new MarcCount( $marc_file ) )->count();
echo "\n";

echo MarcCount::count( $marc_file );
