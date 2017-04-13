<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Tests for MySQL Charsets
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */
require_once 'libraries/mysql_charsets.inc.php';
require_once 'libraries/php-gettext/gettext.inc';

/**
 * Tests for MySQL Charsets
 *
 * @package PhpMyAdmin-test
 */
class PMA_MySQL_Charsets_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for PMA_generateCharsetQueryPart
     *
     * @param bool   $drizzle   Value for PMA_DRIZZLE
     * @param string $collation Collation
     * @param string $expected  Expected Charset Query
     *
     * @return void
     * @test
     * @dataProvider charsetQueryData
     */
    public function testGenerateCharsetQueryPart(
        $drizzle, $collation, $expected
    ) {
        if (!PMA_HAS_RUNKIT) {
            $this->markTestSkipped(
                'Cannot redefine constant - missing runkit extension'
            );
        }

        $restoreDrizzle = '';

        if (defined('PMA_DRIZZLE')) {
            $restoreDrizzle = PMA_DRIZZLE;
            runkit_constant_redefine('PMA_DRIZZLE', $drizzle);
        } else {
            $restoreDrizzle = 'PMA_TEST_CONSTANT_REMOVE';
            define('PMA_DRIZZLE', $drizzle);
        }

        $this->assertEquals(
            $expected,
            PMA_generateCharsetQueryPart($collation)
        );

        if ($restoreDrizzle === 'PMA_TEST_CONSTANT_REMOVE') {
            runkit_constant_remove('PMA_DRIZZLE');
        } else {
            runkit_constant_redefine('PMA_DRIZZLE', $restoreDrizzle);
        }
    }

    /**
     * Data Provider for testPMA_generateCharsetQueryPart
     *
     * @return array test data
     */
    public function charsetQueryData()
    {
        return [
            [false, "a_b_c_d", " CHARACTER SET a COLLATE a_b_c_d"],
            [false, "a_", " CHARACTER SET a COLLATE a_"],
            [false, "a", " CHARACTER SET a"],
            [true, "a_b_c_d", " COLLATE a_b_c_d"],
        ];
    }


    /**
     * Test for PMA_getDbCollation
     *
     * @return void
     * @test
     */
    public function testGetDbCollation()
    {
        if (!PMA_HAS_RUNKIT) {
            $this->markTestSkipped(
                'Cannot redefine constant - missing runkit extension'
            );
        } else {
            $GLOBALS['server'] = 1;
            // test case for system schema
            $this->assertEquals(
                'utf8_general_ci',
                PMA_getDbCollation("information_schema")
            );

            $restoreDrizzle = '';

            // test case with no pma drizzle
            if (defined('PMA_DRIZZLE')) {
                $restoreDrizzle = PMA_DRIZZLE;
                runkit_constant_redefine('PMA_DRIZZLE', false);
            } else {
                $restoreDrizzle = 'PMA_TEST_CONSTANT_REMOVE';
                define('PMA_DRIZZLE', false);
            }

            $GLOBALS['cfg']['Server']['DisableIS'] = false;
            $GLOBALS['cfg']['DBG']['sql']          = false;

            $this->assertEquals(
                'utf8_general_ci',
                PMA_getDbCollation('pma_test')
            );

            // test case with pma drizzle as true
            runkit_constant_redefine('PMA_DRIZZLE', true);
            $this->assertEquals(
                'utf8_general_ci_pma_drizzle',
                PMA_getDbCollation('pma_test')
            );

            $GLOBALS['cfg']['Server']['DisableIS'] = true;
            $GLOBALS['db']                         = 'pma_test2';
            $this->assertEquals(
                'bar',
                PMA_getDbCollation('pma_test')
            );
            $this->assertNotEquals(
                'pma_test',
                $GLOBALS['dummy_db']
            );

            if ($restoreDrizzle === 'PMA_TEST_CONSTANT_REMOVE') {
                runkit_constant_remove('PMA_DRIZZLE');
            } else {
                runkit_constant_redefine('PMA_DRIZZLE', $restoreDrizzle);
            }
        }
    }

    /**
     * Test case for PMA_getCollationDescr()
     *
     * @param string $collation Collation for which description is reqd
     * @param string $desc      Expected Description
     *
     * @return void
     * @test
     * @dataProvider collationDescr
     */
    public function testGetCollationDescr($collation, $desc)
    {
        $this->assertEquals(
            $desc,
            PMA_getCollationDescr($collation)
        );
    }

    /**
     * Data Provider for testPMA_getCollationDescr()
     *
     * @return array Test data for testPMA_getCollationDescr()
     */
    public function collationDescr()
    {
        return [
            ['binary', 'Binary'],
            ['foo_bulgarian_bar', 'Bulgarian'],
            ['gb2312_chinese', 'Simplified Chinese'],
            ['gbk_chinese', 'Simplified Chinese'],
            ['big5_chinese', 'Traditional Chinese'],
            ['foo_ci_bar', 'unknown, case-insensitive'],
            ['foo_cs_bar', 'unknown, case-sensitive'],
            ['foo_croatian_bar', 'Croatian'],
            ['foo_czech_bar', 'Czech'],
            ['foo_danish_bar', 'Danish'],
            ['foo_english_bar', 'English'],
            ['foo_esperanto_bar', 'Esperanto'],
            ['foo_estonian_bar', 'Estonian'],
            ['foo_german1_bar', 'German (dictionary)'],
            ['foo_german2_bar', 'German (phone book)'],
            ['foo_hungarian_bar', 'Hungarian'],
            ['foo_icelandic_bar', 'Icelandic'],
            ['foo_japanese_bar', 'Japanese'],
            ['foo_latvian_bar', 'Latvian'],
            ['foo_lithuanian_bar', 'Lithuanian'],
            ['foo_korean_bar', 'Korean'],
            ['foo_persian_bar', 'Persian'],
            ['foo_polish_bar', 'Polish'],
            ['foo_roman_bar', 'West European'],
            ['foo_romanian_bar', 'Romanian'],
            ['foo_slovak_bar', 'Slovak'],
            ['foo_slovenian_bar', 'Slovenian'],
            ['foo_spanish_bar', 'Spanish'],
            ['foo_spanish2_bar', 'Traditional Spanish'],
            ['foo_swedish_bar', 'Swedish'],
            ['foo_thai_bar', 'Thai'],
            ['foo_turkish_bar', 'Turkish'],
            ['foo_ukrainian_bar', 'Ukrainian'],
            ['foo_unicode_bar', 'Unicode (multilingual)'],
            ['ucs2', 'Unicode (multilingual)'],
            ['utf8', 'Unicode (multilingual)'],
            ['ascii', 'West European (multilingual)'],
            ['cp850', 'West European (multilingual)'],
            ['dec8', 'West European (multilingual)'],
            ['hp8', 'West European (multilingual)'],
            ['latin1', 'West European (multilingual)'],
            ['cp1250', 'Central European (multilingual)'],
            ['cp852', 'Central European (multilingual)'],
            ['latin2', 'Central European (multilingual)'],
            ['macce', 'Central European (multilingual)'],
            ['cp866', 'Russian'],
            ['koi8r', 'Russian'],
            ['gb2312', 'Simplified Chinese'],
            ['gbk', 'Simplified Chinese'],
            ['sjis', 'Japanese'],
            ['ujis', 'Japanese'],
            ['cp932', 'Japanese'],
            ['eucjpms', 'Japanese'],
            ['cp1257', 'Baltic (multilingual)'],
            ['latin7', 'Baltic (multilingual)'],
            ['armscii8', 'Armenian'],
            ['armscii', 'Armenian'],
            ['big5', 'Traditional Chinese'],
            ['cp1251', 'Cyrillic (multilingual)'],
            ['cp1256', 'Arabic'],
            ['euckr', 'Korean'],
            ['hebrew', 'Hebrew'],
            ['geostd8', 'Georgian'],
            ['greek', 'Greek'],
            ['keybcs2', 'Czech-Slovak'],
            ['koi8u', 'Ukrainian'],
            ['latin5', 'Turkish'],
            ['swe7', 'Swedish'],
            ['tis620', 'Thai'],
            ['foobar', 'unknown'],
            ['foo_test_bar', 'unknown'],
            ['foo_bin_bar', 'unknown, Binary'],
        ];
    }

    /**
     * Test for PMA_generateCharsetDropdownBox
     *
     * @return void
     * @test
     */
    public function testGenerateCharsetDropdownBox()
    {
        $GLOBALS['mysql_charsets']              = ['latin1', 'latin2', 'latin3'];
        $GLOBALS['mysql_charsets_available']    = [
            'latin1' => true,
            'latin2' => false,
            'latin3' => true,
        ];
        $GLOBALS['mysql_charsets_descriptions'] = [
            'latin1' => 'abc',
            'latin2' => 'def',
        ];
        $GLOBALS['mysql_collations']            = [
            'latin1' => [
                'latin1_german1_ci',
                'latin1_swedish1_ci',
            ],
            'latin2' => ['latin1_general_ci'],
            'latin3' => [],
        ];
        $GLOBALS['mysql_collations_available']  = [
            'latin1_german1_ci'  => true,
            'latin1_swedish1_ci' => false,
            'latin2_general_ci'  => true,
        ];
        $result                                 = PMA_generateCharsetDropdownBox();

        $this->assertContains('name="collation"', $result);
        $this->assertNotContains('id="', $result);
        $this->assertNotContains('class="autosubmit"', $result);
        $this->assertContains('<option value="">Collation', $result);
        $this->assertContains('<option value=""></option>', $result);
        $this->assertContains('<optgroup label="latin1', $result);
        $this->assertNotContains('<optgroup label="latin2', $result);
        $this->assertContains('title="latin3', $result);
        $this->assertContains('title="abc', $result);
        $this->assertNotContains('value="latin1_swedish1_ci"', $result);
        $this->assertContains('value="latin1_german1_ci"', $result);
        $this->assertNotContains('value="latin2_general1_ci"', $result);
        $this->assertContains('title="German', $result);

        $result = PMA_generateCharsetDropdownBox(
            2, null, "test_id", "latin1", false, true
        );
        $this->assertContains('name="character_set"', $result);
        $this->assertNotContains('Charset</option>', $result);
        $this->assertContains('class="autosubmit"', $result);
        $this->assertContains('id="test_id"', $result);
        $this->assertContains('selected="selected">latin1', $result);
    }

    /**
     * Test for PMA_getServerCollation
     *
     * @return void
     * @test
     */
    public function testGetServerCollation()
    {
        $GLOBALS['server']            = 1;
        $GLOBALS['cfg']['DBG']['sql'] = false;
        $this->assertEquals('utf8_general_ci', PMA_getServerCollation());
    }
}

?>
