<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_safeUnserialize
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
require_once 'libraries/core.lib.php';

class PMA_safeUnserialize_test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for unserializing
     *
     * @param string $data     Serialized data
     * @param mixed  $expected Expected result
     *
     * @return void
     *
     * @dataProvider provideMySQLHosts
     */
    function testSanitizeMySQLHost($data, $expected)
    {
        $this->assertEquals(
            $expected,
            PMA_safeUnserialize($data)
        );
    }

    /**
     * Test data provider
     *
     * @return array
     */
    function provideMySQLHosts()
    {
        return [
            ['s:6:"foobar";', 'foobar'],
            ['foobar', null],
            ['b:0;', false],
            ['O:1:"a":1:{s:5:"value";s:3:"100";}', null],
            ['O:8:"stdClass":1:{s:5:"field";O:8:"stdClass":0:{}}', null],
            [serialize([1, 2, 3]), [1, 2, 3]],
            [serialize('string""'), 'string""'],
            [serialize(['foo' => 'bar']), ['foo' => 'bar']],
            [serialize(['1', new stdClass(), '2']), null],
        ];
    }

}

