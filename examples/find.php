<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcFind;


$marc_file = __DIR__ . '/../data/random.mrc';

$records = ( new MarcFind( $marc_file ) )->find( '...', 'cow' );
echo "\n";

//MarcLint::check( $marc_file );
