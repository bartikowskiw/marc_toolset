# MARC Toolset

Set of tools to deal with MARC records.

## MarcMapWriter

Creates a SQLite DB with the OCLC (MARC Tag 001) number and the position
of the record inside the file.

This is helpful for big MARC files that consist of thousands or millions
of records.

### Usage

```php
use Umlts\MarcSwissKnife\MarcMapWriter;

$db = new SQLite3( $db_file );
$mm = ( new MarcMapWriter( $marc_file, $db ) )->map();
```

## MarcMapReader

Looks up a MARC record by OCLC (MARC Tag 001) number and reads it.

```php
use Umlts\MarcSwissKnife\MarcMapReader;

$mr = new MarcMapReader( $marc_file, $db );
try {
    $file_marc_record = $mr->get( $oclc_number );
    echo $file_marc_record;
} catch ( MarcRecordNotFoundException $e ) {
    echo "Record not found.";
}
```

## MarcDump

```php
use Umlts\MarcToolset\MarcDump;

// Create object and call method
$marc_file = __DIR__ . '/../data/random.mrc';
( new MarcDump( $marc_file ) )->dump();

// or call statically
MarcDump::dump( $marc_file );
```

## MarcCount

```php
use Umlts\MarcToolset\MarcCount;

$marc_file = __DIR__ . '/../data/random.mrc';

// Create object and call method
echo ( new MarcCount( $marc_file ) )->count();

// or call statically
echo MarcCount::count( $marc_file );
```
