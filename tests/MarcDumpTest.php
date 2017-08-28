<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcDump;

/**
 * @covers Umlts\MarcToolset\MarcDump
 */
final class MarcDumpTest extends TestCase {

    public function testCanBeCreated() {
        $md = new MarcDump();
        $this->assertInstanceOf( MarcDump::class, $md );
    }

    public function testDumping() {
        $md = new MarcDump( __DIR__ . '/data/random.mrc' );

        ob_start();
        $md->dump();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, '16th International Conference and Exhibition on Electricity Distribution' ) );
    }

    public function testStaticCall() {
        ob_start();
        MarcDump::dump( __DIR__ . '/data/random.mrc' );
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, '16th International Conference and Exhibition on Electricity Distribution' ) );
    }

}
