<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcFind;
use Umlts\MarcToolset\MarcMask;
use Umlts\MarcToolset\MarcMaskChecker;

/**
 * @covers Umlts\MarcToolset\MarcFind
 */
final class MarcFindTest extends TestCase {

    public function testCanBeCreated() {
        $md = new MarcFind( __DIR__ . '/data/random.mrc', new MarcMask() );
        $this->assertInstanceOf( MarcFind::class, $md );
    }

    public function testFindAndDump() {
        $records = new MarcFind( __DIR__ . '/data/random.mrc', new MarcMask( '65.', '.', '0', '.', 'beef' ) );
        $content = (string) $records;

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, '983796227' ) );
    }

    public function testLeaderFind() {
        $records = new MarcFind( __DIR__ . '/data/random.mrc', new MarcMask( 'ldr', '.', '.', '.', '834c2m' ) );

        $this->assertEquals( true, !empty( $records ) );

        $record = $records->next();
        $this->assertEquals( 2, strpos( $record->getLeader(), '834c2m' ) );
    }

}
