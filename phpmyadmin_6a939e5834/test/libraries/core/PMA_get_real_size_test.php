<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_getRealSize()  from libraries/core.lib.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
require_once 'libraries/core.lib.php';

/**
 * Test for PMA_getRealSize()  from libraries/core.lib.php
 *
 * @package PhpMyAdmin-test
 */
class PMA_GetRealSize_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for
     *
     * @param string $size     Size
     * @param int    $expected Expected value
     *
     * @return void
     *
     * @dataProvider provider
     */
    public function testNull($size, $expected)
    {
        $this->assertEquals($expected, PMA_getRealSize($size));
    }

    /**
     * Data provider for testExtractValueFromFormattedSize
     *
     * @return array
     */
    public function provider()
    {
        return [
            ['0', 0],
            ['1kb', 1024],
            ['1024k', 1024 * 1024],
            ['8m', 8 * 1024 * 1024],
            ['12gb', 12 * 1024 * 1024 * 1024],
            ['1024', 1024],
        ];
    }

}

?>
