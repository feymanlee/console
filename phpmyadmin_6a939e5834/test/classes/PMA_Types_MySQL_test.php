<?php
/**
 * Tests for Types.class.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */

require_once 'libraries/Types.class.php';
require_once 'libraries/php-gettext/gettext.inc';

/**
 * Testcase for MySQL types handling.
 *
 * @package PhpMyAdmin-test
 */
class PMA_Types_MySQL_Test extends PHPUnit_Framework_TestCase
{
    /**
     * @var PMA_Types
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new PMA_Types_MySQL();
    }

    /**
     * Test for getTypeDescription
     *
     * @param string $type The data type to get a description.
     *
     * @return void
     *
     * @dataProvider providerForTestGetTypeDescription
     */
    public function testGetTypeDescription($type)
    {
        $this->assertNotEquals(
            '',
            $this->object->getTypeDescription($type)
        );
    }

    /**
     * Test for getTypeDescription with unknown value
     *
     * @return void
     */
    public function testGetUnknownTypeDescription()
    {
        $this->assertEquals(
            '',
            $this->object->getTypeDescription('UNKNOWN')
        );
    }

    /**
     * Provider for testGetTypeDescription
     *
     * @return array
     */
    public function providerForTestGetTypeDescription()
    {
        return [
            ['TINYINT'],
            ['SMALLINT'],
            ['MEDIUMINT'],
            ['INT'],
            ['BIGINT'],
            ['DECIMAL'],
            ['FLOAT'],
            ['DOUBLE'],
            ['REAL'],
            ['BIT'],
            ['BOOLEAN'],
            ['SERIAL'],
            ['DATE'],
            ['DATETIME'],
            ['TIMESTAMP'],
            ['TIME'],
            ['YEAR'],
            ['CHAR'],
            ['VARCHAR'],
            ['TINYTEXT'],
            ['TEXT'],
            ['MEDIUMTEXT'],
            ['LONGTEXT'],
            ['BINARY'],
            ['VARBINARY'],
            ['TINYBLOB'],
            ['MEDIUMBLOB'],
            ['BLOB'],
            ['LONGBLOB'],
            ['ENUM'],
            ['SET'],
            ['GEOMETRY'],
            ['POINT'],
            ['LINESTRING'],
            ['POLYGON'],
            ['MULTIPOINT'],
            ['MULTILINESTRING'],
            ['MULTIPOLYGON'],
            ['GEOMETRYCOLLECTION'],
        ];
    }

    /**
     * Test for getTypeClass
     *
     * @param string $type   Type to check
     * @param string $output Expected result
     *
     * @return void
     *
     * @dataProvider providerFortTestGetTypeClass
     */
    public function testGetTypeClass($type, $output)
    {
        $this->assertEquals(
            $output,
            $this->object->getTypeClass($type)
        );
    }

    /**
     * Data provider for type testing
     *
     * @return array for testing type detection
     */
    public function providerFortTestGetTypeClass()
    {
        return [
            [
                'SERIAL',
                'NUMBER',
            ],
            [
                'YEAR',
                'DATE',
            ],
            [
                'GEOMETRYCOLLECTION',
                'SPATIAL',
            ],
            [
                'SET',
                'CHAR',
            ],
            [
                'UNKNOWN',
                '',
            ],
        ];
    }

    /**
     * Test for getFunctionsClass
     *
     * @param string $class  The class to get function list.
     * @param array  $output Expected function list
     *
     * @return void
     *
     * @dataProvider providerFortTestGetFunctionsClass
     */
    public function testGetFunctionsClass($class, $output)
    {
        $this->assertEquals(
            $output,
            $this->object->getFunctionsClass($class)
        );
    }

    /**
     * Data provider for testing function lists
     *
     * @return array with test data
     */
    public function providerFortTestGetFunctionsClass()
    {
        return [
            [
                'CHAR',
                [
                    'AES_DECRYPT',
                    'AES_ENCRYPT',
                    'BIN',
                    'CHAR',
                    'COMPRESS',
                    'CURRENT_USER',
                    'DATABASE',
                    'DAYNAME',
                    'DES_DECRYPT',
                    'DES_ENCRYPT',
                    'ENCRYPT',
                    'HEX',
                    'INET_NTOA',
                    'LOAD_FILE',
                    'LOWER',
                    'LTRIM',
                    'MD5',
                    'MONTHNAME',
                    'OLD_PASSWORD',
                    'PASSWORD',
                    'QUOTE',
                    'REVERSE',
                    'RTRIM',
                    'SHA1',
                    'SOUNDEX',
                    'SPACE',
                    'TRIM',
                    'UNCOMPRESS',
                    'UNHEX',
                    'UPPER',
                    'USER',
                    'UUID',
                    'VERSION',
                ],
            ],
            [
                'DATE',
                [
                    'CURRENT_DATE',
                    'CURRENT_TIME',
                    'DATE',
                    'FROM_DAYS',
                    'FROM_UNIXTIME',
                    'LAST_DAY',
                    'NOW',
                    'SEC_TO_TIME',
                    'SYSDATE',
                    'TIME',
                    'TIMESTAMP',
                    'UTC_DATE',
                    'UTC_TIME',
                    'UTC_TIMESTAMP',
                    'YEAR',
                ],
            ],
            [
                'SPATIAL',
                [
                    'GeomFromText',
                    'GeomFromWKB',

                    'GeomCollFromText',
                    'LineFromText',
                    'MLineFromText',
                    'PointFromText',
                    'MPointFromText',
                    'PolyFromText',
                    'MPolyFromText',

                    'GeomCollFromWKB',
                    'LineFromWKB',
                    'MLineFromWKB',
                    'PointFromWKB',
                    'MPointFromWKB',
                    'PolyFromWKB',
                    'MPolyFromWKB',
                ],
            ],
            [
                'NUMBER',
                [
                    '0'  => 'ABS',
                    '1'  => 'ACOS',
                    '2'  => 'ASCII',
                    '3'  => 'ASIN',
                    '4'  => 'ATAN',
                    '5'  => 'BIT_LENGTH',
                    '6'  => 'BIT_COUNT',
                    '7'  => 'CEILING',
                    '8'  => 'CHAR_LENGTH',
                    '9'  => 'CONNECTION_ID',
                    '10' => 'COS',
                    '11' => 'COT',
                    '12' => 'CRC32',
                    '13' => 'DAYOFMONTH',
                    '14' => 'DAYOFWEEK',
                    '15' => 'DAYOFYEAR',
                    '16' => 'DEGREES',
                    '17' => 'EXP',
                    '18' => 'FLOOR',
                    '19' => 'HOUR',
                    '20' => 'INET_ATON',
                    '21' => 'LENGTH',
                    '22' => 'LN',
                    '23' => 'LOG',
                    '24' => 'LOG2',
                    '25' => 'LOG10',
                    '26' => 'MICROSECOND',
                    '27' => 'MINUTE',
                    '28' => 'MONTH',
                    '29' => 'OCT',
                    '30' => 'ORD',
                    '31' => 'PI',
                    '32' => 'QUARTER',
                    '33' => 'RADIANS',
                    '34' => 'RAND',
                    '35' => 'ROUND',
                    '36' => 'SECOND',
                    '37' => 'SIGN',
                    '38' => 'SIN',
                    '39' => 'SQRT',
                    '40' => 'TAN',
                    '41' => 'TO_DAYS',
                    '42' => 'TO_SECONDS',
                    '43' => 'TIME_TO_SEC',
                    '44' => 'UNCOMPRESSED_LENGTH',
                    '45' => 'UNIX_TIMESTAMP',
                    '46' => 'UUID_SHORT',
                    '47' => 'WEEK',
                    '48' => 'WEEKDAY',
                    '49' => 'WEEKOFYEAR',
                    '50' => 'YEARWEEK',
                ],
            ],
            [
                'UNKNOWN',
                [],
            ],
        ];
    }

    /**
     * Test for getAttributes
     *
     * @return void
     */
    public function testGetAttributes()
    {
        $this->assertEquals(
            [
                '',
                'BINARY',
                'UNSIGNED',
                'UNSIGNED ZEROFILL',
                'on update CURRENT_TIMESTAMP',
            ],
            $this->object->getAttributes()
        );
    }

    /**
     * Test for getColumns
     *
     * @return void
     */
    public function testGetColumns()
    {
        $this->assertEquals(
            [
                0               => 'INT',
                1               => 'VARCHAR',
                2               => 'TEXT',
                3               => 'DATE',
                'Numeric'       => [
                    'TINYINT',
                    'SMALLINT',
                    'MEDIUMINT',
                    'INT',
                    'BIGINT',
                    '-',
                    'DECIMAL',
                    'FLOAT',
                    'DOUBLE',
                    'REAL',
                    '-',
                    'BIT',
                    'BOOLEAN',
                    'SERIAL',
                ],
                'Date and time' => [
                    'DATE',
                    'DATETIME',
                    'TIMESTAMP',
                    'TIME',
                    'YEAR',
                ],
                'String'        => [
                    'CHAR',
                    'VARCHAR',
                    '-',
                    'TINYTEXT',
                    'TEXT',
                    'MEDIUMTEXT',
                    'LONGTEXT',
                    '-',
                    'BINARY',
                    'VARBINARY',
                    '-',
                    'TINYBLOB',
                    'MEDIUMBLOB',
                    'BLOB',
                    'LONGBLOB',
                    '-',
                    'ENUM',
                    'SET',
                ],
                'Spatial'       => [
                    'GEOMETRY',
                    'POINT',
                    'LINESTRING',
                    'POLYGON',
                    'MULTIPOINT',
                    'MULTILINESTRING',
                    'MULTIPOLYGON',
                    'GEOMETRYCOLLECTION',
                ],
            ],
            $this->object->getColumns()
        );
    }
}
