<?php

declare( strict_types = 1 );

namespace Umlts\MarcToolset;

use File\MARC;
use Umlts\MarcToolset\AnsiCodes;
use Umlts\MarcToolset\MarcFileToolBase;
use Umlts\MarcToolset\MarcDump;
use Umlts\MarcToolset\MarcRecordNotFoundException;

/**
 * Looks for records
 **/
class MarcBool extends MarcFileToolBase {

    private $mask;
    private $checker;

    private $db;
    private $marc1, $marc2;
    private $marc_file1, $marc_file2;

    public function __construct( string $marc_file1, string $marc_file2 ) {

        parent::__construct();

        $this->db = new \SQLite3( ':memory:' );
        //$this->db = new \SQLite3( 'test.db' );

        $this->marc_file1 = $marc_file1;
        $this->marc_file2 = $marc_file2;

        $this->marc1 = $this->initMarc( $marc_file1 );
        $this->marc2 = $this->initMarc( $marc_file2 );

        ( new MarcMapWriter( $marc_file1, $this->db ) )->map();
        $this->db->exec( 'ALTER TABLE `record` RENAME TO `record1`' );

        ( new MarcMapWriter( $marc_file2, $this->db ) )->map();
        $this->db->exec( 'ALTER TABLE `record` RENAME TO `record2`' );
    }

    public function boolAnd() {
        $stmt = $this->db->prepare(
            'SELECT r1.key, r1.fpos
                FROM record1 AS r1
                JOIN record2 AS r2
                WHERE ( r1.key = r2.key )
            '
        );
        $result = $stmt->execute();

        $fp = fopen( $this->marc_file1, 'r' );
        while( $data = $result->fetchArray( SQLITE3_ASSOC ) ) {
            $records[] = MarcMapReader::readRecord( $fp, $data['fpos'] );
        }

        return $records;
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
        $result = $stmt->execute();

        $fp = fopen( $this->marc_file1, 'r' );
        while( $data = $result->fetchArray( SQLITE3_ASSOC ) ) {
            $records[] = MarcMapReader::readRecord( $fp, $data['fpos'] );
        }

        return $records;
    }

}


/*

--- JOIN ALL

SELECT key FROM record1
UNION
SELECT key FROM record2

--- INTERSECTING:

SELECT
	r1.key
FROM record1 AS r1
JOIN record2 AS r2 USING (key)

--- NOT INTERSECTING:

SELECT key FROM record1
UNION
SELECT key FROM record2

EXCEPT

SELECT key FROM record1
INNER JOIN record2 USING (key)

--- A WITHOUT B

SELECT r1.key FROM record1 AS r1
EXCEPT SELECT r2.key FROM record2 AS r2


*/
