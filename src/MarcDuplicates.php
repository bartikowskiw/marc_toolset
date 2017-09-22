<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks for duplicate records
 **/
class MarcDuplicates extends MarcFileToolBase {

    private $mask;
    private $checker;

    private $db;
    private $fp;

    private $result;

    public function __construct( string $marc_file ) {

        parent::__construct();

        // Save the DB inside the memory
        $this->db = new \SQLite3( ':memory:' );

        $this->marc_file = $marc_file;
        $this->marc = $this->initMarc( $marc_file );

        ( new MarcMapWriter( $marc_file, $this->db ) )->map();

        $this->fp = fopen( $this->marc_file, 'r' );
    }

    public function __destruct() {
        fclose( $this->fp );
    }

    public function findDuplicates() : self {

        $stmt = $this->db->prepare(
            'SELECT r1.key, r1.fpos FROM record AS r1
                WHERE (
                    SELECT 1 FROM record AS r2
                    WHERE r1.key = r2.key LIMIT 1, 1
                ) = 1;
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

