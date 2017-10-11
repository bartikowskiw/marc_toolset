<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcSplit;

$marc_file = __DIR__ . '/../data/random.mrc';
$ms = new MarcSplit( $marc_file );
$ms
    ->setOutputDir( '/tmp' )
    ->setEnumLength( 3  )
    ->setEnumChars( implode( '', range( 'a', 'z' ) )  )
    ->split( 10 );


MarcSplit::split( 25, $marc_file );
