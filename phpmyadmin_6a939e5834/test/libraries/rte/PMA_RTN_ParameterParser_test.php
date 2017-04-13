<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for parsing of Routine parameters
 *
 * @package PhpMyAdmin-test
 */

/*
 * Needed for PMA_Util::unQuote() and PMA_SQP_parse()
 */
require_once 'libraries/Util.class.php';
require_once 'libraries/sqlparser.lib.php';

/*
 * Include to test.
 */
require_once 'libraries/rte/rte_routines.lib.php';

/**
 * Test for parsing of Routine parameters
 *
 * @package PhpMyAdmin-test
 */
class PMA_RTN_ParameterParser_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for PMA_RTN_parseRoutineDefiner
     *
     * @param string $source Source
     * @param array  $target Expected output
     *
     * @return void
     *
     * @dataProvider definer_provider
     */
    public function test_parseDefiner($source, $target)
    {
        PMA_RTN_setGlobals();
        $this->assertEquals(
            $target,
            PMA_RTN_parseRoutineDefiner(PMA_SQP_parse($source))
        );
    }

    /**
     * Data provider for test_parseDefiner
     *
     * @return array
     */
    public function definer_provider()
    {
        return [
            ['CREATE PROCEDURE FOO() SELECT NULL', ''],
            [
                'CREATE DEFINER=`root`@`localhost` PROCEDURE FOO() SELECT NULL',
                'root@localhost',
            ],
            [
                'CREATE DEFINER=`root\\`@`localhost` PROCEDURE FOO() SELECT NULL',
                'root\\@localhost',
            ],
        ];
    }

    /**
     * Test for PMA_RTN_parseOneParameter
     *
     * @param string $source Source
     * @param array  $target Expected output
     *
     * @return void
     *
     * @dataProvider param_provider
     */
    public function test_parseOneParameter($source, $target)
    {
        PMA_RTN_setGlobals();
        $this->assertEquals($target, PMA_RTN_parseOneParameter($source));
    }

    /**
     * Data provider for test_parseOneParameter
     *
     * @return array
     */
    public function param_provider()
    {
        return [
            ['`foo` TEXT', ['', 'foo', 'TEXT', '', '']],
            ['`foo` INT(20)', ['', 'foo', 'INT', '20', '']],
            ['DECIMAL(5,5)', ['', '', 'DECIMAL', '5,5', '']],
            [
                'IN `fo``fo` INT UNSIGNED',
                ['IN', 'fo`fo', 'INT', '', 'UNSIGNED'],
            ],
            [
                'OUT bar VARCHAR(1) CHARSET utf8',
                ['OUT', 'bar', 'VARCHAR', '1', 'utf8'],
            ],
            [
                '`"baz\'\'` ENUM(\'a\', \'b\') CHARSET latin1',
                ['', '"baz\'\'', 'ENUM', '\'a\',\'b\'', 'latin1'],
            ],
            [
                'INOUT `foo` DECIMAL(5,2) UNSIGNED ZEROFILL',
                ['INOUT', 'foo', 'DECIMAL', '5,2', 'UNSIGNED ZEROFILL'],
            ],
            [
                '`foo``s func` SET(\'test\'\'esc"\',   \'more\\\'esc\')',
                [
                    '', 'foo`s func', 'SET', '\'test\'\'esc"\',\'more\\\'esc\'', '',
                ],
            ],
        ];
    }

    /**
     * Test for PMA_RTN_parseAllParameters
     *
     * @param string $query  Query
     * @param string $type   Type
     * @param array  $target Expected output
     *
     * @return void
     *
     * @depends      test_parseOneParameter
     * @dataProvider query_provider
     */
    public function test_parseAllParameters($query, $type, $target)
    {
        PMA_RTN_setGlobals();
        $this->assertEquals(
            $target,
            PMA_RTN_parseAllParameters(PMA_SQP_parse($query), $type)
        );
    }

    /**
     * Data provider for test_parseAllParameters
     *
     * @return array
     */
    public function query_provider()
    {
        return [
            [
                'CREATE PROCEDURE `foo`() SET @A=0',
                'PROCEDURE',
                [
                    'num'    => 0,
                    'dir'    => [],
                    'name'   => [],
                    'type'   => [],
                    'length' => [],
                    'opts'   => [],
                ],
            ],
            [
                'CREATE DEFINER=`user\\`@`somehost``(` FUNCTION `foo```(`baz` INT) BEGIN SELECT NULL; END',
                'FUNCTION',
                [
                    'num'    => 1,
                    'dir'    => [
                        0 => '',
                    ],
                    'name'   => [
                        0 => 'baz',
                    ],
                    'type'   => [
                        0 => 'INT',
                    ],
                    'length' => [
                        0 => '',
                    ],
                    'opts'   => [
                        0 => '',
                    ],
                ],
            ],
            [
                'CREATE PROCEDURE `foo`(IN `baz\\)` INT(25) zerofill unsigned) BEGIN SELECT NULL; END',
                'PROCEDURE',
                [
                    'num'    => 1,
                    'dir'    => [
                        0 => 'IN',
                    ],
                    'name'   => [
                        0 => 'baz\\)',
                    ],
                    'type'   => [
                        0 => 'INT',
                    ],
                    'length' => [
                        0 => '25',
                    ],
                    'opts'   => [
                        0 => 'UNSIGNED ZEROFILL',
                    ],
                ],
            ],
            [
                'CREATE PROCEDURE `foo`(IN `baz\\` INT(001) zerofill, out bazz varchar(15) charset UTF8) BEGIN SELECT NULL; END',
                'PROCEDURE',
                [
                    'num'    => 2,
                    'dir'    => [
                        0 => 'IN',
                        1 => 'OUT',
                    ],
                    'name'   => [
                        0 => 'baz\\',
                        1 => 'bazz',
                    ],
                    'type'   => [
                        0 => 'INT',
                        1 => 'VARCHAR',
                    ],
                    'length' => [
                        0 => '1',
                        1 => '15',
                    ],
                    'opts'   => [
                        0 => 'ZEROFILL',
                        1 => 'utf8',
                    ],
                ],
            ],
        ];
    }
}

?>
