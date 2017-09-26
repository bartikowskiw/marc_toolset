<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcMagic;

/**
 * @covers Umlts\MarcToolset\MarcCount
 */
final class MarcMagicTest extends TestCase {

    public function testCanBeCreated() {
        $mm = new MarcMagic( __DIR__ . '/data/random.mrc' );
        $this->assertInstanceOf( MarcMagic::class, $mm );
    }

    public function testMagic() {
        $mm = new MarcMagic( __DIR__ . '/data/random.mrc' );
        $this->assertEquals( $mm->check(), TRUE);

        // Static call
        $this->assertEquals( MarcMagic::check( __DIR__ . '/data/random.mrc' ), TRUE );

        // Checkin leader
        $this->assertEquals( MarcMagic::checkLeader( '01867cas a2200529 a 4500' ), TRUE );
    }

    public function testInvalidLeader() {
        $this->assertEquals( MarcMagic::checkLeader( '01867 as a2200529 a 4500' ), FALSE );
        $this->assertEquals( MarcMagic::checkLeader( '01867nas a2200529 a 45' ), FALSE );
        $this->assertEquals( MarcMagic::checkLeader( '01867nas a2200529 a 1234' ), FALSE );
        $this->assertEquals( MarcMagic::checkLeader( '' ), FALSE );
    }

}
