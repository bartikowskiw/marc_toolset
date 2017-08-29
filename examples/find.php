<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcFind;
use Umlts\MarcToolset\MarcSearchMask;


$marc_file = __DIR__ . '/../data/random.mrc';

$records = ( new MarcFind( $marc_file ) )
    ->setMask( new  MarcSearchMask( '630' ) )
    ->findAndDump();
echo "\n";
