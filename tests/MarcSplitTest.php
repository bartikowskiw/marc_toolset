<?php

declare( strict_types = 1 );

use PHPUnit\Framework\TestCase;
use Umlts\MarcToolset\MarcSplit;
use Umlts\MarcToolset\MarcCount;

/**
 * @covers Umlts\MarcToolset\MarcSplit
 */
final class MarcSplitTest extends TestCase {

    public $tmp_dir;
    public $split_size = 11;

    public function setUp() {
        $this->tmp_dir = sys_get_temp_dir() . '/MarcSplitTest_' . md5( random_bytes( 100 ) );
        mkdir( $this->tmp_dir );
    }

    public function tearDown() {
        rmdir( $this->tmp_dir );
    }

    public function testCanBeCreated() {
        $ms = new MarcSplit();
        $this->assertInstanceOf( MarcSplit::class, $ms );
    }

    public function testSplitting() {

        $ms = ( new MarcSplit( __DIR__ . '/data/random.mrc' ) )
            ->setOutputDir( $this->tmp_dir )
            ->split( $this->split_size );

        $total = MarcCount::count( __DIR__ . '/data/random.mrc' );

        $files = glob( $this->tmp_dir . '/*.mrc' );

        $this->assertEquals( count( $files ), (int) ceil( $total / $this->split_size ) );

        for ( $i=0; $i<count( $files ) - 1; $i++ ) {
            $this->assertEquals( MarcCount::count( $files[$i] ), $this->split_size );
        }

        $this->assertEquals(
            MarcCount::count( $files[ count( $files ) - 1 ] ),
            $total % $this->split_size
        );

        array_map( 'unlink', $files );
    }

}
