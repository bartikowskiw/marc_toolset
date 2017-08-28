<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcLint;

/**
 * @covers Umlts\MarcToolset\MarcLint
 */
final class MarcLintTest extends TestCase {

    public function testCanBeCreated() {
        $md = new MarcLint();
        $this->assertInstanceOf( MarcLint::class, $md );
    }

    public function testLinting() {
        $md = new MarcLint( __DIR__ . '/data/random.mrc' );

        ob_start();
        $md->check();
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, 'Must end with . (period).' ) );
    }

    public function testStaticCall() {

        ob_start();
        MarcLint::check( __DIR__ . '/data/random.mrc' );
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, 'Must end with . (period).' ) );
    }

}
