<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 ** Test for PMA_Util::containsNonPrintableAscii from common.lib
 *
 * @package PhpMyAdmin-test
 * @group   common.lib-tests
 */

/*
 * Include to test.
 */
require_once 'libraries/Util.class.php';

/**
 ** Test for PMA_Util::containsNonPrintableAscii from common.lib
 *
 * @package PhpMyAdmin-test
 * @group   common.lib-tests
 */
class PMA_ContainsNonPrintableAsciiTest extends PHPUnit_Framework_TestCase
{
    /**
     * data provider for testContainsNonPrintableAscii
     *
     * @return array
     */
    public function dataProvider()
    {
        return [
            ["normal string", 0],
            ["new\nline", 1],
            ["tab\tspace", 1],
            ["escape" . chr(27) . "char", 1],
            ["chars%$\r\n", 1],
        ];
    }

    /**
     * Test for containsNonPrintableAscii
     *
     * @param string $str Value
     * @param bool   $res Expected value
     *
     * @return void
     *
     * @dataProvider dataProvider
     */
    public function testContainsNonPrintableAscii($str, $res)
    {
        $this->assertEquals(
            $res, PMA_Util::containsNonPrintableAscii($str)
        );
    }

}