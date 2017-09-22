<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcBool;
use Umlts\MarcToolset\MarcRecordNotFoundException;


/**
 * @covers Umlts\MarcToolset\MarcBool
 */
final class MarcBoolTest extends TestCase {

    public function testCanBeCreated() {
        $mb = new MarcBool( __DIR__ . '/data/random.mrc', __DIR__ . '/data/random2.mrc' );
        $this->assertInstanceOf( MarcBool::class, $mb );
    }

    public function testBoolAnd() {
        $mb = new MarcBool( __DIR__ . '/data/random.mrc', __DIR__ . '/data/random2.mrc' );
        $mb->boolAnd();
        $count = 0;
        while ( TRUE ) {
            try {
                $mb->next();
                $count++;
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        $this->assertEquals( 37, $count );
    }

    public function testBoolNot() {
        $mb = new MarcBool( __DIR__ . '/data/random.mrc', __DIR__ . '/data/random2.mrc' );
        $mb->boolNot();
        $count = 0;
        while ( TRUE ) {
            try {
                $mb->next();
                $count++;
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        $this->assertEquals( 63, $count );
    }

}
