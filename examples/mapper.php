<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcMapWriter;
use Umlts\MarcToolset\MarcMapReader;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;

$marc_file = __DIR__ . '/../data/random.mrc';

$db = new SQLite3( ':memory:' );
$mm = ( new MarcMapWriter( $marc_file, $db ) )->map();

$mr = new MarcMapReader( $marc_file, $db );
try {
    echo MarcDump::formatDump( (string) $mr->get( 983796227 ) );
} catch ( MarcRecordNotFoundException $e ) {
    echo "Record not found.\n";
}
