<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for export.lib.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */


require_once 'libraries/export.lib.php';

/**
 * class PMA_DisplayExport_Test
 *
 * this class is for testing export.lib.php functions
 *
 * @package PhpMyAdmin-test
 * @group   large
 */
class PMA_Export_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for setUp
     *
     * @return void
     */
    public function setUp()
    {

    }

    /**
     * Test for PMA_mergeAliases
     *
     * @return void
     */
    public function testPMAMergeAliases()
    {
        $aliases1 = [
            'test_db' => [
                'alias'  => 'aliastest',
                'tables' => [
                    'foo' => [
                        'alias'   => 'foobar',
                        'columns' => [
                            'bar' => 'foo',
                            'baz' => 'barbaz',
                        ],
                    ],
                    'bar' => [
                        'alias'   => 'foobaz',
                        'columns' => [
                            'a' => 'a_alias',
                            'b' => 'b',
                        ],
                    ],
                ],
            ],
        ];
        $aliases2 = [
            'test_db' => [
                'alias'  => 'test',
                'tables' => [
                    'foo' => [
                        'columns' => [
                            'bar' => 'foobar',
                        ],
                    ],
                    'baz' => [
                        'columns' => [
                            'a' => 'x',
                        ],
                    ],
                ],
            ],
        ];
        $expected = [
            'test_db' => [
                'alias'  => 'test',
                'tables' => [
                    'foo' => [
                        'alias'   => 'foobar',
                        'columns' => [
                            'bar' => 'foobar',
                            'baz' => 'barbaz',
                        ],
                    ],
                    'bar' => [
                        'alias'   => 'foobaz',
                        'columns' => [
                            'a' => 'a_alias',
                            'b' => 'b',
                        ],
                    ],
                    'baz' => [
                        'columns' => [
                            'a' => 'x',
                        ],
                    ],
                ],
            ],
        ];
        $actual   = PMA_mergeAliases($aliases1, $aliases2);
        $this->assertEquals($expected, $actual);
    }
}
