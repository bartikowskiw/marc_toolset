<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;

/**
 * Splits a MARC file.
 **/
class MarcSplit extends MarcFileToolBase {

    const DEFAULT_ENUM_CHARS = '01234567890';

    private $output_dir = './';
    private $enum_length = 2;
    private $enum_chars = self::DEFAULT_ENUM_CHARS;

    /**
     * Sets the character list for the enum() method
     * @param string $chars
     * @return self
     */
    public function setEnumChars( string $chars ) : self {
        $this->enum_chars = $chars;
        return $this;
    }

    /**
     * Sets the count of digits used for numbering the files.
     *
     * @param int $digits
     * @return self
     */
    public function setEnumLength( int $enum_length ) : self {
        $this->enum_length = $enum_length;
        return $this;
    }

    /**
     * Sets the dir the resulting files will be written to.
     *
     * @param string $dir
     * @return self
     */
    public function setOutputDir( string $dir ) : self {
        if ( !is_writable( $dir ) ) {
            throw new \InvalidArgumentException( $dir . ' is not writable.' );
        }
        $this->output_dir = $dir;
        return $this;
    }

    /**
     * Splits a MARC file in multiple parts.
     *
     * @param int $size
     *   How many records per file?
     * @param string $marc_file
     *   Path to file. Optional when called as method. Needed if called
     *   statically.
     * @return int
     *   Returns number of records.
     * @throws RuntimeException
     *   If file is not writeable
     */
    public final function split( int $size=1, string $marc_file = '', string $dir = '' ) {

        $count = 0;
        $file_nr = 0;

        $filename = 'output.mrc';
        if ( empty( $dir ) && isset( $this ) ) {
            $dir = rtrim( $this->output_dir, '/' ) . '/';
        }

        if ( empty( $marc_file ) && isset( $this ) ) { $marc_file = $this->marc_file; }
        if ( is_file( $marc_file ) ) { $filename = basename( $marc_file ); }

        $name = self::splitFilename( $filename );
        $marc = self::getMarc( $marc_file );

        while ( $record = $marc->nextRaw() ) {

            if ( $count == 0 ) {

                $output = self::stitchFilename( $file_nr, $name, $dir );
                $fp = fopen( $output, 'w' );
                if ( $fp === FALSE ) {
                    throw new \RuntimeException( 'Can not write to ' . $output . '.' );
                }
            }

            fwrite( $fp, $record );
            $count++;

            if ( $count >= $size || empty( $output ) ) {
                $count=0; $file_nr++;
                fclose( $fp );
            }
        }

    }

    /**
     * Splits filename in base and extension.
     *
     * @param string $filename
     * @return array
     *   Returns array with two elements: 'base' and 'ext'
     */
    private final function splitFilename( string $filename ) : array {
        $ext = 'mrc';
        $parts = explode( '.', $filename );
        if ( count( $parts ) > 1 ) {
            $ext = array_pop( $parts );
        }
        $base = implode( '.', $parts );

        return [ 'base' => $base, 'ext' => $ext ];
    }

    /**
     * Sets the count of digits used for numbering the files.
     *
     * @param int $digits
     * @return self
     */
    private final function stitchFilename( int $nr, array $name, string $dir ) {
        $number = self::enum( $nr );
        $filename = implode( '.', [ $name['base'], $number, $name['ext'] ] );
        return $dir .  $filename;
    }

    /**
     * Creates string of digits or any alphanumeric characters
     * for enumeration.
     *
     * @param int $nr
     * @return string
     */
    private final function enum( int $nr, string $enum_chars = '' ) : string {
        $string = '';
        if ( empty( $enum_chars ) && isset( $this ) ) { $enum_chars = $this->enum_chars; }
        if ( empty( $enum_chars ) ) { $enum_chars = self::DEFAULT_ENUM_CHARS; }
        $a = str_split( $enum_chars );
        $c = count( $a );

        do {
            $string .= $a[ $nr % $c ];
            $nr = floor( $nr / $c );
        } while ( $nr > 0 );

        $string = strrev( $string );
        $string = str_pad(
            $string,
            isset( $this ) ? $this->enum_length : 1,
            $a[0],
            STR_PAD_LEFT
        );

        return $string;
    }

}
