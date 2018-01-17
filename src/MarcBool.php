<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;
use Umlts\MarcToolset\MarcMapKeyCreator;

/**
 * Looks for records
 **/
class MarcBool extends MarcFileToolBase {

    private $mask;
    private $checker;

    private $db;
    private $marc1, $marc2;
    private $marc_file1, $marc_file2;
    private $fp;

    private $result;

    public function __construct( string $marc_file1, string $marc_file2, MarcMapKeyCreator $key_creator = NULL ) {

        parent::__construct();

        // Save the DB inside the memory
        $this->db = new \SQLite3( ':memory:' );

        $this->marc_file1 = $marc_file1;
        $this->marc_file2 = $marc_file2;

        $this->marc1 = $this->initMarc( $marc_file1 );
        $this->marc2 = $this->initMarc( $marc_file2 );

        ( new MarcMapWriter( $marc_file1, $this->db, $key_creator ) )->map();
        $this->db->exec( 'ALTER TABLE `record` RENAME TO `record1`' );

        ( new MarcMapWriter( $marc_file2, $this->db, $key_creator ) )->map();
        $this->db->exec( 'ALTER TABLE `record` RENAME TO `record2`' );

        $this->fp = fopen( $this->marc_file1, 'r' );
    }

    public function __destruct() {
        fclose( $this->fp );
    }

    public function boolAnd() : self {
        $stmt = $this->db->prepare(
            'SELECT r1.key, r1.fpos
                FROM record1 AS r1
                JOIN record2 AS r2
                WHERE ( r1.key = r2.key )
            '
        );
        $this->result = $stmt->execute();
        return $this;
    }

    public function boolNot() {
        $stmt = $this->db->prepare(
            'SELECT r1.key, r1.fpos
                FROM record1 AS r1
                WHERE (
                    SELECT COUNT(*) FROM record2 AS r2 WHERE r2.key = r1.key
                ) = 0
            '
        );
        $this->result = $stmt->execute();
        return $this;
    }

    public function next() {
        if ( empty( $this->result ) ) {
            throw new \RuntimeException( 'No query has been executed. Run an operation first!' );
        }

        $data = $this->result->fetchArray( SQLITE3_ASSOC );
        if ( $data === FALSE ) {
            $this->result = NULL;
            throw new MarcRecordNotFoundException('Record not found.');
        }
        return MarcMapReader::readRecord( $this->fp, $data['fpos'] );
    }


    public function echoDump( bool $ansi = TRUE ) : self {
        $first = TRUE;
        while ( TRUE ) {
            try {
                $record = $this->next();
                if ( !$first ) { echo self::sep; }
                echo MarcDump::dumpRecord( $record, $ansi );
                $first = FALSE;
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        return $this;
    }

    public function echoRaw() : self {
        while ( TRUE ) {
            try {
                $record = $this->next();
                echo $record->toRaw();
            } catch ( MarcRecordNotFoundException $e ) {
                break;
            }
        }
        return $this;
    }

    public function __toString() {
        ob_start();
        $this->echoDump();
        $content = ob_get_clean();
        return $content;
    }

}
