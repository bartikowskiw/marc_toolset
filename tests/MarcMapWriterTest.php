<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcMapWriter;
use Umlts\MarcToolset\MarcMapKeyCreator;

class customKeyCreator implements MarcMapKeyCreator {
    
    static function getKeys( \File_MARC_Record $record ) : array {
        if ( empty( $record->getField( '245' ) ) ) { return [ -1 ]; }
        
        $keys = [];
        $fields = $record->getFields( '245' );
        foreach ( $fields as $field ) {
            $keys[] = $field->getSubfield( 'a' )->getData();
        }
        return $keys;
    }
}

/**
 * @covers Umlts\MarcToolset\MarcMapWriter
 */
final class MarcMapWriterTest extends TestCase {

    public function testCanBeCreated() {
        $db = new SQLite3( ':memory:' );
        $mw = new MarcMapWriter( __DIR__ . '/data/random.mrc', $db );
        $this->assertInstanceOf( MarcMapWriter::class, $mw );
    }

    public function testWriting() {
        $db = new SQLite3( ':memory:' );
        $mw = new MarcMapWriter( __DIR__ . '/data/random.mrc', $db );
        $mw->map();
        $result = $db->query( 'SELECT COUNT(*) AS count FROM `record`' );
        $data = $result->fetchArray( SQLITE3_ASSOC );
        $this->assertEquals( $data['count'], 100 );
    }
    
    public function testCustomKeyCreator() {
        $db = new SQLite3( ':memory:' );
        $mw = new MarcMapWriter( __DIR__ . '/data/random.mrc', $db );
        $mw->setKeyCreator( new customKeyCreator() );
        $mw->map();
    
        $result = $db->query( 'SELECT COUNT(*) AS count FROM `record`' );
        $data = $result->fetchArray( SQLITE3_ASSOC );
        $this->assertEquals( $data['count'], 100 );
        
        $result = $db->query( '
            SELECT COUNT(*) AS count FROM `record` 
            WHERE `key` = \'Clostridium difficile on U.S. beef cow-calf operations.\'
        ' );
        $data = $result->fetchArray( SQLITE3_ASSOC );
        $this->assertEquals( $data['count'], 1 );
    }

}
