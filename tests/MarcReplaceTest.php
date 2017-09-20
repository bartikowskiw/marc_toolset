<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcReplace;
use Umlts\MarcToolset\MarcMask;

/**
 * @covers Umlts\MarcToolset\MarcReplace
 */
final class MarcReplaceTest extends TestCase {

    public function testCanBeCreated() {
        $mr = new MarcReplace(
            __DIR__ . '/data/random.mrc',
            new MarcMask(),
            ''
        );
        $this->assertInstanceOf( MarcReplace::class, $mr );
    }

    public function testReplace() {
        $mr = new MarcReplace(
            __DIR__ . '/data/random.mrc',
            new MarcMask( '65.', '.', '0', '.', 'beef' ),
            'pork'
        );

        $record = $mr->next();

        $content = (string) $record;

        $this->assertEquals( true, !empty( $content ) );
        $this->assertNotEquals( false, strpos( $content, 'pork' ) );
    }

    public function testLeaderReplace() {
        $mr = new MarcReplace(
            __DIR__ . '/data/random.mrc',
            new MarcMask( 'leader', '.', '.', '.', '(..834c2)(m)' ),
            '\1@'
        );

        $record = $mr->next();
        $this->assertEquals( 7, strpos( $record->getLeader(), '@' ) );
    }

}
