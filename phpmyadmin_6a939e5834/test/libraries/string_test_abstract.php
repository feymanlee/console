<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Abstract tests for string library with default set of tests
 *
 * @package PhpMyAdmin-test
 */

/**
 * tests for string library
 *
 * @package PhpMyAdmin-test
 */
abstract class PMA_StringTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for mb_strlen
     *
     * @param integer $length Length of the string
     * @param string  $str    String to check for
     *
     * @return void
     * @test
     * @dataProvider providerStrlen
     */
    public function testStrlen($length, $str)
    {
        $this->assertEquals(
            $length,
            mb_strlen($str)
        );
    }

    /**
     * Data provider for testStrlen
     *
     * @return array Test data
     */
    public function providerStrlen()
    {
        return [
            [2, "ab"],
            [9, "test data"],
            [0, ""],
        ];
    }

    /**
     * Test for PMA_StringNative::substr
     *
     * @param string $str      Expected substring
     * @param string $haystack String to check in
     * @param int    $start    Starting position of substring
     * @param int    $length   Length of substring
     *
     * @return void
     * @test
     * @dataProvider providerSubstr
     */
    public function testSubStr($str, $haystack, $start, $length)
    {
        $this->assertEquals(
            $str,
            mb_substr($haystack, $start, $length)
        );
    }

    /**
     * Data provider for testSubStr
     *
     * @return array Test data
     */
    public function providerSubstr()
    {
        return [
            ["b", "ab", 1, 1],
            ["data", "testdata", 4, 4],
        ];
    }

    /**
     * Test for PMA_StringNative::substrCount
     *
     * @param int    $expected number of occurrences
     * @param string $haystack string to check
     * @param string $needle   string to count
     *
     * @return void
     * @test
     * @dataProvider providerSubstrCount
     */
    public function testSubstrCount($expected, $haystack, $needle)
    {
        $this->assertEquals(
            $expected,
            mb_substr_count($haystack, $needle)
        );
    }

    /**
     * Data provider for testSubstrCount
     *
     * @return array Test data
     */
    public function providerSubstrCount()
    {
        return [
            [1, "ab", "b"],
            [1, "testdata", "data"],
            [2, "testdata", "a"],
            [0, "testdata", "b"],
        ];
    }

    /**
     * Test for PMA_StringNative::substrCount
     *
     * @param string $haystack string to check
     * @param string $needle   string to count
     *
     * @return void
     * @test
     * @dataProvider providerSubstrCountException
     *
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSubstrCountException($haystack, $needle)
    {
        //No test. We're waiting for an exception.
        mb_substr_count($haystack, $needle);
    }

    /**
     * Data provider for testSubstrCountException
     *
     * @return array Test data
     */
    public function providerSubstrCountException()
    {
        return [
            ["testdata", ""],
            ["testdata", null],
            ["testdata", false],
        ];
    }

    /**
     * Test for PMA_StringNative::strpos
     *
     * @param int    $pos      Expected position
     * @param string $haystack String to search in
     * @param string $needle   String to search for
     * @param int    $offset   Search offset
     *
     * @return void
     * @test
     * @dataProvider providerStrpos
     */
    public function testStrpos($pos, $haystack, $needle, $offset = 0)
    {
        $this->assertEquals(
            $pos,
            mb_strpos($haystack, $needle, $offset)
        );
    }

    /**
     * Data provider for testStrpos
     *
     * @return array Test data
     */
    public function providerStrpos()
    {
        return [
            [1, "ab", "b", 0],
            [4, "test data", " ", 0],
        ];
    }

    /**
     * Test for PMA_StringNative::strrchr
     *
     * @param string $expected Expected substring
     * @param string $haystack String to cut
     * @param string $needle   Searched string
     *
     * @return void
     * @test
     * @dataProvider providerStrrchr
     */
    public function testStrrchr($expected, $haystack, $needle)
    {
        $this->assertEquals(
            $expected,
            mb_strrchr($haystack, $needle)
        );
    }

    /**
     * Data provider for testStrrchr
     *
     * @return array Test data
     */
    public function providerStrrchr()
    {
        return [
            ['abcdef', 'abcdefabcdef', 'a'],
            [false, 'abcdefabcdef', 'A'],
            ['f', 'abcdefabcdef', 'f'],
            [false, 'abcdefabcdef', 'z'],
            [false, 'abcdefabcdef', ''],
            [false, 'abcdefabcdef', false],
            [false, 'abcdefabcdef', true],
            ['123', '789456123', true],
            [false, '7894560123', false],
            [false, 'abcdefabcdef', null],
            [false, null, null],
            [false, null, 'a'],
            [false, null, '0'],
            [false, false, null],
            [false, false, 'a'],
            [false, false, '0'],
            [false, true, null],
            [false, true, 'a'],
            [false, true, '0'],
        ];
    }

    /**
     * Test for PMA_StringNative::strtolower
     *
     * @param string $expected Expected lowercased string
     * @param string $string   String to convert to lowercase
     *
     * @return void
     * @test
     * @dataProvider providerStrtolower
     */
    public function testStrtolower($expected, $string)
    {
        $this->assertEquals(
            $expected,
            mb_strtolower($string)
        );
    }

    /**
     * Data provider for testStrtolower
     *
     * @return array Test data
     */
    public function providerStrtolower()
    {
        return [
            ["mary had a", "Mary Had A"],
            ["test string", "TEST STRING"],
        ];
    }
}
