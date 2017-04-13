<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for PMA_langDetails from select_lang.lib.php
 *
 * @package PhpMyAdmin-test
 * @group   select_lang.lib-tests
 */

/*
 * Include to test.
 */
require_once 'libraries/select_lang.lib.php';

/**
 * Test for PMA_langDetails from select_lang.lib.php
 *
 * @package PhpMyAdmin-test
 * @group   select_lang.lib-tests
 */
class PMA_LangDetails_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test for testLangDetails
     *
     * @return array
     */
    function dataProvider()
    {
        return [
            ['af|afrikaans', 'af', '', 'af'],
            [
                'ar|arabic',
                'ar',
                '&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;',
                'ar',
            ],
            ['az|azerbaijani', 'az', 'Az&#601;rbaycanca', 'az'],
            ['bn|bangla', 'bn', 'বাংলা', 'bn'],
            [
                'be|belarusian',
                'be',
                '&#1041;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1072;&#1103;',
                'be',
            ],
            [
                'be[-_]lat|belarusian latin',
                'be-lat',
                'Bie&#0322;aruskaja',
                'be@latin',
            ],
            [
                'bg|bulgarian',
                'bg',
                '&#1041;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;',
                'bg',
            ],
            ['bs|bosnian', 'bs', 'Bosanski', 'bs'],
            ['br|breton', 'br', 'Brezhoneg', 'br'],
            ['ca|catalan', 'ca', 'Catal&agrave;', 'ca'],
            ['cs|czech', 'cs', 'Čeština', 'cs'],
            ['cy|welsh', 'cy', 'Cymraeg', 'cy'],
            ['da|danish', 'da', 'Dansk', 'da'],
            ['de|german', 'de', 'Deutsch', 'de'],
            [
                'el|greek',
                'el',
                '&Epsilon;&lambda;&lambda;&eta;&nu;&iota;&kappa;&#940;',
                'el',
            ],
            ['en|english', 'en', '', 'en'],
            ['en[_-]gb|english (United Kingdom)', 'en-gb', '', 'en_GB'],
            ['es|spanish', 'es', 'Espa&ntilde;ol', 'es'],
            ['et|estonian', 'et', 'Eesti', 'et'],
            ['eu|basque', 'eu', 'Euskara', 'eu',],
            ['fa|persian', 'fa', '&#1601;&#1575;&#1585;&#1587;&#1740;', 'fa'],
            ['fi|finnish', 'fi', 'Suomi', 'fi'],
            ['fr|french', 'fr', 'Fran&ccedil;ais', 'fr'],
            ['gl|galician', 'gl', 'Galego', 'gl'],
            ['he|hebrew', 'he', '&#1506;&#1489;&#1512;&#1497;&#1514;', 'he'],
            [
                'hi|hindi',
                'hi',
                '&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;',
                'hi',
            ],
            ['hr|croatian', 'hr', 'Hrvatski', 'hr'],
            ['hu|hungarian', 'hu', 'Magyar', 'hu'],
            ['id|indonesian', 'id', 'Bahasa Indonesia', 'id'],
            ['it|italian', 'it', 'Italiano', 'it'],
            ['ja|japanese', 'ja', '&#26085;&#26412;&#35486;', 'ja'],
            ['ko|korean', 'ko', '&#54620;&#44397;&#50612;', 'ko'],
            [
                'ka|georgian',
                'ka',
                '&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312;',
                'ka',
            ],
            ['lt|lithuanian', 'lt', 'Lietuvi&#371;', 'lt'],
            ['lv|latvian', 'lv', 'Latvie&scaron;u', 'lv'],
            ['mk|macedonian', 'mk', 'Macedonian', 'mk'],
            [
                'mn|mongolian',
                'mn',
                '&#1052;&#1086;&#1085;&#1075;&#1086;&#1083;',
                'mn',
            ],
            ['ms|malay', 'ms', 'Bahasa Melayu', 'ms'],
            ['nl|dutch', 'nl', 'Nederlands', 'nl'],
            ['nb|norwegian', 'nb', 'Norsk', 'nb'],
            ['pl|polish', 'pl', 'Polski', 'pl'],
            [
                'pt[-_]br|brazilian portuguese',
                'pt-BR',
                'Portugu&ecirc;s',
                'pt_BR',
            ],
            ['pt|portuguese', 'pt', 'Portugu&ecirc;s', 'pt'],
            ['ro|romanian', 'ro', 'Rom&acirc;n&#259;', 'ro'],
            [
                'ru|russian',
                'ru',
                '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;',
                'ru',
            ],
            ['si|sinhala', 'si', '&#3523;&#3538;&#3458;&#3524;&#3517;', 'si'],
            ['sk|slovak', 'sk', 'Sloven&#269;ina', 'sk'],
            ['sl|slovenian', 'sl', 'Sloven&scaron;&#269;ina', 'sl'],
            ['sq|albanian', 'sq', 'Shqip', 'sq'],
            ['sr[-_]lat|serbian latin', 'sr-lat', 'Srpski', 'sr@latin'],
            [
                'sr|serbian',
                'sr',
                '&#1057;&#1088;&#1087;&#1089;&#1082;&#1080;',
                'sr',
            ],
            ['sv|swedish', 'sv', 'Svenska', 'sv'],
            ['ta|tamil', 'ta', 'தமிழ்', 'ta'],
            ['te|telugu', 'te', 'తెలుగు', 'te'],
            [
                'th|thai',
                'th',
                '&#3616;&#3634;&#3625;&#3634;&#3652;&#3607;&#3618;',
                'th',
            ],
            ['tr|turkish', 'tr', 'T&uuml;rk&ccedil;e', 'tr'],
            ['tt|tatarish', 'tt', 'Tatar&ccedil;a', 'tt'],
            ['ug|uyghur', 'ug', 'ئۇيغۇرچە', 'ug'],
            [
                'uk|ukrainian',
                'uk',
                '&#1059;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;',
                'uk',
            ],
            ['ur|urdu', 'ur', 'اُردوُ', 'ur'],
            ['uz[-_]lat|uzbek-latin', 'uz-lat', 'O&lsquo;zbekcha', 'uz@latin'],
            [
                'uz[-_]cyr|uzbek-cyrillic',
                'uz-cyr',
                '&#1038;&#1079;&#1073;&#1077;&#1082;&#1095;&#1072;',
                'uz',
            ],
            [
                'zh[-_](tw|hk)|chinese traditional',
                'zh-TW',
                '&#20013;&#25991;',
                'zh_TW',
            ],
            [
                'zh(?![-_](tw|hk))([-_][[:alpha:]]{2,3})?|chinese simplified',
                'zh',
                '&#20013;&#25991;',
                'zh_CN',
            ],
            ['test_lang|test_lang', 'test_lang', 'test_lang', 'test_lang'],
        ];
    }

    /**
     * Test for PMA_langDetails
     *
     * @param string $a Language
     * @param string $b Language code
     * @param string $c Language native name in html entities
     * @param string $d Language
     *
     * @return void
     *
     * @dataProvider dataProvider
     */
    function testLangDetails($a, $b, $c, $d)
    {
        $this->assertEquals([$a, $b, $c], PMA_langDetails($d));
    }
}
