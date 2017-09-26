<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcMagic;


$marc_file = __DIR__ . '/../data/random.mrc';

echo (int) ( new MarcMagic( $marc_file ) )->check();

echo (int) MarcMagic::check( $marc_file );
