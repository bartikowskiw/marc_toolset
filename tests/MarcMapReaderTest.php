<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcMapWriter;
use Umlts\MarcToolset\MarcMapReader;

/**
 * @covers Umlts\MarcToolset\MarcMapReader
 */
final class MarcMapReaderTest extends TestCase {

    public function testCanBeCreated() {
        $db = new SQLite3( ':memory:' );
        $mr = new MarcMapReader( __DIR__ . '/data/random.mrc', $db );
        $this->assertInstanceOf( MarcMapReader::class, $mr );
    }

    public function testReading() {
        // Create the data to read
        $db = new SQLite3( ':memory:' );
        $mw = ( new MarcMapWriter( __DIR__ . '/data/random.mrc', $db ) )->map();

        // Create reader
        $mr = new MarcMapReader( __DIR__ . '/data/random.mrc', $db );
        $record = (string) $mr->get( '983796227' )[0];

        $this->assertEquals( empty( $record ), FALSE );
        $this->assertNotEquals( FALSE, strpos( $record, 'Clostridium difficile on U.S. beef cow-calf operations.' ) );
    }

}
