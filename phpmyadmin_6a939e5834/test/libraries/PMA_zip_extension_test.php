<?php
/**
 * Tests zip extension usage.
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */

require_once 'libraries/zip_extension.lib.php';
require_once 'libraries/php-gettext/gettext.inc';

/**
 * Tests zip extension usage.
 *
 * @package PhpMyAdmin-test
 */
class PMA_ZipExtension_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test zip file content
     *
     * @param string $file           zip file
     * @param string $specific_entry regular expression to match a file
     * @param mixed  $output         expected output
     *
     * @dataProvider providerForTestGetZipContents
     * @return void
     */
    public function testGetZipContents($file, $specific_entry, $output)
    {
        $this->assertEquals(
            PMA_getZipContents($file, $specific_entry),
            $output
        );
    }

    /**
     * Provider for testGetZipContents
     *
     * @return array
     */
    public function providerForTestGetZipContents()
    {
        return [
            [
                './test/test_data/test.zip',
                null,
                [
                    'error' => '',
                    'data'  => 'TEST FILE' . "\n",
                ],
            ],
            [
                './test/test_data/test.zip',
                'test',
                [
                    'error' => 'Error in ZIP archive: Could not find "test"',
                    'data'  => '',
                ],
            ],
        ];
    }

    /**
     * Test Find file in Zip Archive
     *
     * @param string $file_regexp regular expression for the file name to match
     * @param string $file        zip archive
     * @param mixed  $output      expected output
     *
     * @dataProvider providerForTestFindFileFromZipArchive
     * @return void
     */
    public function testFindFileFromZipArchive($file_regexp, $file, $output)
    {
        $this->assertEquals(
            PMA_findFileFromZipArchive($file_regexp, $file),
            $output
        );
    }

    /**
     * Provider for testFindFileFromZipArchive
     *
     * @return array Test data
     */
    public function providerForTestFindFileFromZipArchive()
    {
        return [
            [
                '/test/',
                './test/test_data/test.zip',
                'test.file',
            ],
        ];
    }

    /**
     * Test for PMA_getNoOfFilesInZip
     *
     * @return void
     */
    public function testGetNoOfFilesInZip()
    {
        $this->assertEquals(
            PMA_getNoOfFilesInZip('./test/test_data/test.zip'),
            1
        );
    }

    /**
     * Test for PMA_zipExtract
     *
     * @return void
     */
    public function testZipExtract()
    {
        $this->assertEquals(
            false,
            PMA_zipExtract(
                './test/test_data/test.zip', 'wrongName'
            )
        );
        $this->assertEquals(
            "TEST FILE\n",
            PMA_zipExtract(
                './test/test_data/test.zip', 'test.file'
            )
        );
    }

    /**
     * Test for PMA_getZipError
     *
     * @param int   $code   error code
     * @param mixed $output expected output
     *
     * @dataProvider providerForTestGetZipError
     * @return void
     */
    public function testGetZipError($code, $output)
    {
        $this->assertEquals(
            PMA_getZipError($code),
            $output
        );
    }

    /**
     * Provider for testGetZipError
     *
     * @return array
     */
    public function providerForTestGetZipError()
    {
        return [
            [
                1,
                'Multi-disk zip archives not supported',
            ],
            [
                5,
                'Read error',
            ],
            [
                7,
                'CRC error',
            ],
            [
                19,
                'Not a zip archive',
            ],
            [
                21,
                'Zip archive inconsistent',
            ],
            [
                404,
                404,
            ],
        ];
    }
}

?>
