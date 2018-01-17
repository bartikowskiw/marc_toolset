<?php

declare( strict_types = 1 );
require_once __DIR__ . '/../vendor/autoload.php';

use File\MARC;
use Umlts\MarcToolset\MarcMapWriter;
use Umlts\MarcToolset\MarcMapReader;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;
use Umlts\MarcToolset\MarcMapKeyCreator;

$marc_file = __DIR__ . '/../data/random.mrc';

$db = new SQLite3( ':memory:' );
$mm = ( new MarcMapWriter( $marc_file, $db ) )->map();

$mr = new MarcMapReader( $marc_file, $db );
try {
    $records = $mr->get( 'OCM1bookssib026558126' );
    foreach ( $records as $record ) {
        echo MarcDump::formatDump( (string) $record );
    }
} catch ( MarcRecordNotFoundException $e ) {
    echo "Record not found.\n";
}

/**
 * Using custom key creator
 */

class customKeyCreator implements MarcMapKeyCreator {
    
    static function getKeys( \File_MARC_Record $record ) : array {
        // Default value for the case the 001 field is empty
        if ( empty( $record->getField( '245' ) ) ) { return [ -1 ]; }
        
        $keys = [];
        $fields = $record->getFields( '245' );
        foreach ( $fields as $field ) {
            $keys[] = $field->getSubfield( 'a' )->getData();
        }
        return $keys;
    }
}

$db = new SQLite3( ':memory:' );
$mm = ( new MarcMapWriter( $marc_file, $db ) )
        ->setKeyCreator( new customKeyCreator() )
        ->map();
$mr = new MarcMapReader( $marc_file, $db );

try {
    $records = $mr->get( '16th International Conference and Exhibition on Electricity Distribution, 2001. Part 1' );
    foreach ( $records as $record ) {
        echo MarcDump::formatDump( (string) $record );
    }
} catch ( MarcRecordNotFoundException $e ) {
    echo "Record not found.\n";
}
