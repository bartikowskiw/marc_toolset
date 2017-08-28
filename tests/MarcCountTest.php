<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcCount;

/**
 * @covers Umlts\MarcToolset\MarcCount
 */
final class MarcCountTest extends TestCase {

    public function testCanBeCreated() {
        $mc = new MarcCount();
        $this->assertInstanceOf( MarcCount::class, $mc );
    }

    public function testCounting() {
        $mc = new MarcCount( __DIR__ . '/data/random.mrc' );
        $count = $mc->count();
        $this->assertEquals( $count, 100 );
    }

    public function testStaticCall() {
        $count = MarcCount::count( __DIR__ . '/data/random.mrc' );
        $this->assertEquals( $count, 100 );
    }

}
