<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Tests for Specialized String Class (CType) for phpMyAdmin
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
require_once 'libraries/StringCType.class.php';

/**
 * Tests for Specialized String Class (CType) for phpMyAdmin
 *
 * @package PhpMyAdmin-test
 */
class PMA_StringCType_Test extends PHPUnit_Framework_TestCase
{
    private $_object;

    /**
     * Setup function for test cases
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        if (!@extension_loaded('ctype')) {
            $this->markTestSkipped(
                "ctype extension not present."
            );
        }
        $this->_object = new PMA_StringCType();
    }

    /**
     * Test for isAlnum()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isAlnumData
     */
    public function testIsAlnum($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isAlnum($str)
        );
    }

    /**
     * Data provider for testIsAlnum
     *
     * @return array Test data
     */
    public function isAlnumData()
    {
        return [
            [true, "AbCd1zyZ9"],
            [false, "foo!#bar"],
        ];
    }

    /**
     * Test for isAlpha()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isAlphaData
     */
    public function testIsAlpha($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isAlpha($str)
        );
    }

    /**
     * Data provider for testIsAlpha
     *
     * @return array Test data
     */
    public function isAlphaData()
    {
        return [
            [true, "kJW"],
            [false, "k12"],
        ];
    }

    /**
     * Test for isDigit()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isDigitData
     */
    public function testIsDigit($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isDigit($str)
        );
    }

    /**
     * Data provider for testIsDigit
     *
     * @return array Test data
     */
    public function isDigitData()
    {
        return [
            [false, "kJW"],
            [false, "?.foo!#21"],
            [true, "12"],
        ];
    }

    /**
     * Test for isUpper()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isUpperData
     */
    public function testIsUpper($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isUpper($str)
        );
    }

    /**
     * Data provider for testIsUpper
     *
     * @return array Test data
     */
    public function isUpperData()
    {
        return [
            [true, "ABCD"],
            [false, "AbCD"],
            [false, "ABCD12!3"],
        ];
    }

    /**
     * Test for isLower()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isLowerData
     */
    public function testIsLower($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isLower($str)
        );
    }

    /**
     * Data provider for testIsLower
     *
     * @return array Test data
     */
    public function isLowerData()
    {
        return [
            [true, "abcd"],
            [false, "aBcd"],
            [false, "abcd12!3"],
        ];
    }

    /**
     * Test for isSpace()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isSpaceData
     */
    public function testIsSpace($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isSpace($str)
        );
    }

    /**
     * Data provider for testIsSpace
     *
     * @return array Test data
     */
    public function isSpaceData()
    {
        return [
            [true, " "],
            [false, '\n\r\t'],
            [true, "\n\r\t"],
            [false, "\ntest"],
        ];
    }

    /**
     * Test for isHexDigit()
     *
     * @param integer $expected Expected output
     * @param string  $str      String to check
     *
     * @return void
     * @test
     * @dataProvider isHexDigitData
     */
    public function testIsHexDigit($expected, $str)
    {
        $this->assertEquals(
            $expected,
            $this->_object->isHexDigit($str)
        );
    }

    /**
     * Data provider for testIsHexDigit
     *
     * @return array Test data
     */
    public function isHexDigitData()
    {
        return [
            [true, "AB10BC99"],
            [false, "AR1012"],
            [true, "ab12bc99"],
        ];
    }

}

?>
