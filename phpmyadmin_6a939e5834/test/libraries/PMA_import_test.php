<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_checkTimeout()
 * from libraries/import.lib.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */

/*
 * we must set $GLOBALS['server'] here
 * since 'check_user_privileges.lib.php' will use it globally
 */
$GLOBALS['server'] = 0;

/*
 * Include to test.
 */
require_once 'libraries/Util.class.php';
require_once 'libraries/Tracker.class.php';
require_once 'libraries/database_interface.inc.php';
require_once 'libraries/import.lib.php';
require_once 'libraries/sqlparser.lib.php';
require_once 'libraries/url_generating.lib.php';

/**
 * Tests for import functions
 *
 * @package PhpMyAdmin-test
 */
class PMA_Import_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Prepares environment for the test.
     *
     * @return void
     */
    public function setUp()
    {
        $GLOBALS['cfg']['ServerDefault'] = '';
    }

    /**
     * Test for PMA_checkTimeout
     *
     * @return void
     */
    function testCheckTimeout()
    {
        global $timestamp, $maximum_time, $timeout_passed;

        //Reinit values.
        $timestamp      = time();
        $maximum_time   = 0;
        $timeout_passed = false;

        $this->assertFalse(PMA_checkTimeout());

        //Reinit values.
        $timestamp      = time();
        $maximum_time   = 0;
        $timeout_passed = true;

        $this->assertFalse(PMA_checkTimeout());

        //Reinit values.
        $timestamp      = time();
        $maximum_time   = 30;
        $timeout_passed = true;

        $this->assertTrue(PMA_checkTimeout());

        //Reinit values.
        $timestamp      = time() - 15;
        $maximum_time   = 30;
        $timeout_passed = false;

        $this->assertFalse(PMA_checkTimeout());

        //Reinit values.
        $timestamp      = time() - 60;
        $maximum_time   = 30;
        $timeout_passed = false;

        $this->assertTrue(PMA_checkTimeout());
    }

    /**
     * Test for PMA_lookForUse
     *
     * @return void
     */
    function testLookForUse()
    {
        $this->assertEquals(
            [null, null],
            PMA_lookForUse(null, null, null)
        );

        $this->assertEquals(
            ['myDb', null],
            PMA_lookForUse(null, 'myDb', null)
        );

        $this->assertEquals(
            ['myDb', true],
            PMA_lookForUse(null, 'myDb', true)
        );

        $this->assertEquals(
            ['myDb', true],
            PMA_lookForUse('select 1 from myTable', 'myDb', true)
        );

        $this->assertEquals(
            ['anotherDb', true],
            PMA_lookForUse('use anotherDb', 'myDb', false)
        );

        $this->assertEquals(
            ['anotherDb', true],
            PMA_lookForUse('use anotherDb', 'myDb', true)
        );

        $this->assertEquals(
            ['anotherDb', true],
            PMA_lookForUse('use `anotherDb`;', 'myDb', true)
        );
    }

    /**
     * Test for PMA_getColumnAlphaName
     *
     * @param string $expected Expected result of the function
     * @param int    $num      The column number
     *
     * @return void
     *
     * @dataProvider provGetColumnAlphaName
     */
    function testGetColumnAlphaName($expected, $num)
    {
        $this->assertEquals($expected, PMA_getColumnAlphaName($num));
    }

    /**
     * Data provider for testGetColumnAlphaName
     *
     * @return array
     */
    function provGetColumnAlphaName()
    {
        return [
            ['A', 1],
            ['Z', 0],
            ['AA', 27],
            ['AZ', 52],
            ['BA', 53],
            ['BB', 54],
        ];
    }

    /**
     * Test for PMA_getColumnNumberFromName
     *
     * @param int         $expected Expected result of the function
     * @param string|null $name     column name(i.e. "A", or "BC", etc.)
     *
     * @return void
     *
     * @dataProvider provGetColumnNumberFromName
     */
    function testGetColumnNumberFromName($expected, $name)
    {
        $this->assertEquals($expected, PMA_getColumnNumberFromName($name));
    }

    /**
     * Data provider for testGetColumnNumberFromName
     *
     * @return array
     */
    function provGetColumnNumberFromName()
    {
        return [
            [1, 'A'],
            [26, 'Z'],
            [27, 'AA'],
            [52, 'AZ'],
            [53, 'BA'],
            [54, 'BB'],
        ];
    }

    /**
     * Test for PMA_getDecimalPrecision
     *
     * @param int         $expected Expected result of the function
     * @param string|null $size     Size of field
     *
     * @return void
     *
     * @dataProvider provGetDecimalPrecision
     */
    function testGetDecimalPrecision($expected, $size)
    {
        $this->assertEquals($expected, PMA_getDecimalPrecision($size));
    }

    /**
     * Data provider for testGetDecimalPrecision
     *
     * @return array
     */
    function provGetDecimalPrecision()
    {
        return [
            [2, '2,1'],
            [6, '6,2'],
            [6, '6,0'],
            [16, '16,2'],
        ];
    }

    /**
     * Test for PMA_getDecimalScale
     *
     * @param int         $expected Expected result of the function
     * @param string|null $size     Size of field
     *
     * @return void
     *
     * @dataProvider provGetDecimalScale
     */
    function testGetDecimalScale($expected, $size)
    {
        $this->assertEquals($expected, PMA_getDecimalScale($size));
    }

    /**
     * Data provider for testGetDecimalScale
     *
     * @return array
     */
    function provGetDecimalScale()
    {
        return [
            [1, '2,1'],
            [2, '6,2'],
            [0, '6,0'],
            [20, '30,20'],
        ];
    }

    /**
     * Test for PMA_getDecimalSize
     *
     * @param array       $expected Expected result of the function
     * @param string|null $cell     Cell content
     *
     * @return void
     *
     * @dataProvider provGetDecimalSize
     */
    function testGetDecimalSize($expected, $cell)
    {
        $this->assertEquals($expected, PMA_getDecimalSize($cell));
    }

    /**
     * Data provider for testGetDecimalSize
     *
     * @return array
     */
    function provGetDecimalSize()
    {
        return [
            [[2, 1, '2,1'], '2.1'],
            [[2, 1, '2,1'], '6.2'],
            [[3, 1, '3,1'], '10.0'],
            [[4, 2, '4,2'], '30.20'],
        ];
    }

    /**
     * Test for PMA_detectType
     *
     * @param int         $expected Expected result of the function
     * @param int|null    $type     Last cumulative column type (VARCHAR or INT or
     *                              BIGINT or DECIMAL or NONE)
     * @param string|null $cell     String representation of the cell for which a
     *                              best-fit type is to be determined
     *
     * @return void
     *
     * @dataProvider provDetectType
     */
    function testDetectType($expected, $type, $cell)
    {
        $this->assertEquals($expected, PMA_detectType($type, $cell));
    }

    /**
     * Data provider for testDetectType
     *
     * @return array
     */
    function provDetectType()
    {
        return [
            [NONE, null, 'NULL'],
            [NONE, NONE, 'NULL'],
            [INT, INT, 'NULL'],
            [VARCHAR, VARCHAR, 'NULL'],
            [VARCHAR, null, null],
            [VARCHAR, INT, null],
            [INT, INT, '10'],
            [DECIMAL, DECIMAL, '10.2'],
            [DECIMAL, INT, '10.2'],
            [BIGINT, BIGINT, '2147483648'],
            [BIGINT, INT, '2147483648'],
            [VARCHAR, VARCHAR, 'test'],
            [VARCHAR, INT, 'test'],
        ];
    }

    /**
     * Test for PMA_getTableReferences
     *
     * @return void
     */
    function testPMAGetTableReferences()
    {
        $sql_query = 'UPDATE `table_1` AS t1, `table_2` t2, `table_3` AS t3 '
            . 'SET `table_1`.`id` = `table_2`.`id` '
            . 'WHERE 1';

        $parsed_sql           = PMA_SQP_parse($sql_query);
        $analyzed_sql         = PMA_SQP_analyze($parsed_sql);
        $analyzed_sql_results = [
            'parsed_sql'   => $parsed_sql,
            'analyzed_sql' => $analyzed_sql,
        ];

        $table_references = PMA_getTableReferences($analyzed_sql_results);

        $this->assertEquals(
            ' `table_1` AS t1 , `table_2` t2 , `table_3` AS t3',
            $table_references
        );
    }

    /**
     * Test for PMA_getMatchedRows.
     *
     * @return void
     */
    function testPMAGetMatchedRows()
    {
        $GLOBALS['db'] = 'PMA';
        //mock DBI
        $dbi = $this->getMockBuilder('PMA_DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $update_query           = 'UPDATE `table_1` '
            . 'SET `id` = 20 '
            . 'WHERE `id` > 10';
        $simulated_update_query = 'SELECT `id` FROM  `table_1` WHERE `id` > 10 ';
        $delete_query           = 'DELETE FROM `table_1` '
            . 'WHERE `id` > 10';
        $simulated_delete_query = 'SELECT *  FROM  `table_1` WHERE `id` > 10 ';

        $dbi->expects($this->any())
            ->method('numRows')
            ->with([])
            ->will($this->returnValue(2));

        $dbi->expects($this->any())
            ->method('selectDb')
            ->with('PMA')
            ->will($this->returnValue(true));

        $dbi->expects($this->at(1))
            ->method('tryQuery')
            ->with($simulated_update_query)
            ->will($this->returnValue([]));

        $dbi->expects($this->at(4))
            ->method('tryQuery')
            ->with($simulated_delete_query)
            ->will($this->returnValue([]));

        $GLOBALS['dbi'] = $dbi;

        $this->simulatedQueryTest($update_query, $simulated_update_query);
        $this->simulatedQueryTest($delete_query, $simulated_delete_query);
    }

    /**
     * Tests simulated UPDATE/DELETE query.
     *
     * @param string $sql_query       SQL query
     * @param string $simulated_query Simulated query
     *
     * @return void
     */
    function simulatedQueryTest($sql_query, $simulated_query)
    {
        $parsed_sql           = PMA_SQP_parse($sql_query);
        $analyzed_sql         = PMA_SQP_analyze($parsed_sql);
        $analyzed_sql_results = [
            'parsed_sql'   => $parsed_sql,
            'analyzed_sql' => $analyzed_sql,
        ];

        $simulated_data = PMA_getMatchedRows($analyzed_sql_results);

        // URL to matched rows.
        $_url_params      = [
            'db'        => 'PMA',
            'sql_query' => $simulated_query,
        ];
        $matched_rows_url = 'sql.php' . PMA_URL_getCommon($_url_params);

        $this->assertEquals(
            [
                'sql_query'        => PMA_Util::formatSql(
                    $analyzed_sql_results['parsed_sql']['raw']
                ),
                'matched_rows'     => 2,
                'matched_rows_url' => $matched_rows_url,
            ],
            $simulated_data
        );
    }

    /**
     * Test for PMA_checkIfRollbackPossible
     *
     * @return void
     */
    function testPMACheckIfRollbackPossible()
    {
        $GLOBALS['db'] = 'PMA';
        //mock DBI
        $dbi = $this->getMockBuilder('PMA_DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        // List of Transactional Engines.
        $transactional_engines = [
            'INNODB',
            'FALCON',
            'NDB',
            'INFINIDB',
            'TOKUDB',
            'XTRADB',
            'SEQUENCE',
            'BDB',
        ];

        $check_query = 'SELECT `ENGINE` FROM `information_schema`.`tables` '
            . 'WHERE `table_name` = "%s" '
            . 'AND `table_schema` = "%s" '
            . 'AND UPPER(`engine`) IN ("'
            . implode('", "', $transactional_engines)
            . '")';

        $check_table_query = 'SELECT * FROM `%s`.`%s` '
            . 'LIMIT 1';

        $dbi->expects($this->at(0))
            ->method('tryQuery')
            ->with(sprintf($check_table_query, 'PMA', 'table_1'))
            ->will($this->returnValue(['table']));

        $dbi->expects($this->at(1))
            ->method('tryQuery')
            ->with(sprintf($check_query, 'table_1', 'PMA'))
            ->will($this->returnValue(true));

        $dbi->expects($this->at(2))
            ->method('numRows')
            ->will($this->returnValue(1));

        $dbi->expects($this->at(3))
            ->method('tryQuery')
            ->with(sprintf($check_table_query, 'PMA', 'table_2'))
            ->will($this->returnValue(['table']));

        $dbi->expects($this->at(4))
            ->method('tryQuery')
            ->with(sprintf($check_query, 'table_2', 'PMA'))
            ->will($this->returnValue(true));

        $dbi->expects($this->at(5))
            ->method('numRows')
            ->will($this->returnValue(1));

        $GLOBALS['dbi'] = $dbi;

        $sql_query = 'UPDATE `table_1` AS t1, `table_2` t2 '
            . 'SET `table_1`.`id` = `table_2`.`id` '
            . 'WHERE 1';

        $this->assertEquals(true, PMA_checkIfRollbackPossible($sql_query));
    }
}
