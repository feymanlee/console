<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for build_html_for_db.lib.php
 *
 * @package PhpMyAdmin-test
 */

/*
 * Include to test.
 */

$GLOBALS['server'] = 0;
require_once 'libraries/Util.class.php';
require_once 'libraries/php-gettext/gettext.inc';
require_once 'libraries/build_html_for_db.lib.php';
require_once 'libraries/js_escape.lib.php';
require_once 'libraries/Theme.class.php';
require_once 'libraries/database_interface.inc.php';
require_once 'libraries/Tracker.class.php';
require_once 'libraries/Types.class.php';
require_once 'libraries/mysql_charsets.inc.php';

/**
 * tests for build_html_for_db.lib.php
 *
 * @package PhpMyAdmin-test
 */
class PMA_BuildHtmlForDb_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Prepares environment for the test.
     *
     * @return void
     */
    public function setUp()
    {
        global $cfg;

        $cfg['ShowFunctionFields'] = false;
        $GLOBALS['server']         = 0;
        $cfg['ServerDefault']      = 1;

        $GLOBALS['PMA_Types']              = new PMA_Types_MySQL();
        $_SESSION['PMA_Theme']             = new PMA_Theme();
        $GLOBALS['cfg']['ActionLinksMode'] = 'icons';

        $GLOBALS['pmaThemePath']  = $_SESSION['PMA_Theme']->getPath();
        $GLOBALS['pmaThemeImage'] = 'theme/';

        $GLOBALS['cfg']['DefaultTabDatabase'] = 'db_structure.php';
    }

    /**
     * Test for PMA_getColumnOrder
     *
     * @return void
     */
    public function testGetColumnOrder()
    {
        $this->assertEquals(
            [
                'DEFAULT_COLLATION_NAME' => [
                    'disp_name'            => __('Collation'),
                    'description_function' => 'PMA_getCollationDescr',
                    'format'               => 'string',
                    'footer'               => 'utf8_general_ci',
                ],
                'SCHEMA_TABLES'          => [
                    'disp_name' => __('Tables'),
                    'format'    => 'number',
                    'footer'    => 0,
                ],
                'SCHEMA_TABLE_ROWS'      => [
                    'disp_name' => __('Rows'),
                    'format'    => 'number',
                    'footer'    => 0,
                ],
                'SCHEMA_DATA_LENGTH'     => [
                    'disp_name' => __('Data'),
                    'format'    => 'byte',
                    'footer'    => 0,
                ],
                'SCHEMA_INDEX_LENGTH'    => [
                    'disp_name' => __('Indexes'),
                    'format'    => 'byte',
                    'footer'    => 0,
                ],
                'SCHEMA_LENGTH'          => [
                    'disp_name' => __('Total'),
                    'format'    => 'byte',
                    'footer'    => 0,
                ],
            ],
            PMA_getColumnOrder()
        );
    }

    /**
     * Test for PMA_buildHtmlForDb
     *
     * @param array   $current           Current
     * @param boolean $is_superuser      Is superuser
     * @param string  $url_query         URL query
     * @param array   $column_order      Column order
     * @param array   $replication_types Replication types
     * @param array   $replication_info  Replication info
     * @param array   $tags              Tags
     *
     * @return void
     * @dataProvider providerForTestBuildHtmlForDb
     *
     * @group        medium
     */
    public function testBuildHtmlForDb($current, $is_superuser,
        $url_query, $column_order, $replication_types,
        $replication_info, $html_segments
    ) {
        $result = PMA_buildHtmlForDb(
            $current, $is_superuser, $url_query,
            $column_order, $replication_types, $replication_info
        );
        $this->assertEquals(
            $column_order,
            $result[0]
        );
        foreach ($html_segments as $html_segment) {
            $this->assertContains(
                $html_segment,
                $result[1]
            );
        }
    }

    /**
     * Data for testBuildHtmlForDb
     *
     * @return array data for testBuildHtmlForDb test case
     */
    public function providerForTestBuildHtmlForDb()
    {
        return [
            [
                ['SCHEMA_NAME' => 'pma'],
                true,
                'target=main.php',
                PMA_getColumnOrder(),
                [
                    'SCHEMA_NAME' => 'pma',
                ],
                [
                    'pma' => [
                        'status'    => 'true',
                        'Ignore_DB' => [
                            'pma' => 'pma',
                        ],
                    ],
                ],
                [
                    '<td class="tool">',
                    '<input type="checkbox" name="selected_dbs[]" class="checkall" title="pma" value="pma"',
                ],
            ],
            [
                ['SCHEMA_NAME' => 'INFORMATION_SCHEMA'],
                true,
                'target=main.php',
                PMA_getColumnOrder(),
                [
                    'SCHEMA_NAME' => 'INFORMATION_SCHEMA',
                ],
                [
                    'INFORMATION_SCHEMA' => [
                        'status'    => 'false',
                        'Ignore_DB' => [
                            'INFORMATION_SCHEMA' => 'INFORMATION_SCHEMA',
                        ],
                    ],
                ],
                [
                    '<input type="checkbox" name="selected_dbs[]" class="checkall" title="INFORMATION_SCHEMA" value="INFORMATION_SCHEMA" disabled="disabled"',
                ],
            ],
        ];
    }
}
