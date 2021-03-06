<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for multibytes string library
 *
 * @package PhpMyAdmin-test
 */

require_once 'test/libraries/string_test_abstract.php';

/**
 * tests for multibytes string library
 *
 * @package PhpMyAdmin-test
 */
class PMA_StringMbTest extends PMA_StringTest
{
    /**
     * Setup function for test cases
     *
     * @access protected
     * @return void
     */
    protected function setUp()
    {
        require_once 'libraries/string.lib.php';
        if (MULTIBYTES_STATUS === MULTIBYTES_OFF) {
            $this->markTestSkipped(
                "Multibyte functions don't exist, skipping test."
            );
        }
    }

    /**
     * Data provider for testStrlen
     *
     * @return array Test data
     */
    public function providerStrlen()
    {
        return array_merge(
            parent::providerStrlen(),
            [[13, "chaîne testée"]]
        );
    }

    /**
     * Data provider for testSubStr
     *
     * @return array Test data
     */
    public function providerSubstr()
    {
        return array_merge(
            parent::providerSubstr(),
            [
                ["rçon", "garçon", 2, 4],
                ["de ", "garçon de café", 7, 3],
            ]
        );
    }

    /**
     * Data provider for testSubstrCount
     *
     * @return array Test data
     */
    public function providerSubstrCount()
    {
        return array_merge(
            parent::providerSubstrCount(),
            [
                [2, "garçon de café", "a"],
                [1, "garçon de café attristé", "ç"],
                [2, "garçon de café attristé", "é"],
                [1, "garçon de café attristé", "fé"],
            ]
        );
    }

    //providerSubstrCountException

    /**
     * Data provider for testStrpos
     *
     * @return array Test data
     */
    public function providerStrpos()
    {
        return array_merge(
            parent::providerStrpos(),
            [
                [16, "garçon de café attristé", "t"],
                [13, "garçon de café attristé", "é"],
                [22, "garçon de café attristé", "é", 15],
            ]
        );
    }

    //providerStrpos
    //providerStrrchr
    //providerStrtolower
}
