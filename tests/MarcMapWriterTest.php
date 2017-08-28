<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcMapWriter;

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

}
