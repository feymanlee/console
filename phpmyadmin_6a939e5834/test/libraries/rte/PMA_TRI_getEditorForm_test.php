<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for generating trigger editor
 *
 * @package PhpMyAdmin-test
 */

require_once 'libraries/php-gettext/gettext.inc';
require_once 'libraries/url_generating.lib.php';
require_once 'libraries/Util.class.php';
require_once 'libraries/database_interface.inc.php';
require_once 'libraries/Tracker.class.php';
/*
 * Include to test.
 */
require_once 'libraries/rte/rte_triggers.lib.php';

/**
 * Test for generating trigger editor
 *
 * @package PhpMyAdmin-test
 */
class PMA_TRI_GetEditorForm_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Set up
     *
     * @return void
     */
    public function setUp()
    {
        $GLOBALS['cfg']['ServerDefault'] = '';
        $GLOBALS['db']                   = 'pma_test';
    }


    /**
     * Test for PMA_TRI_getEditorForm
     *
     * @param array $data    Data for trigger
     * @param array $matcher Matcher
     *
     * @return void
     *
     * @dataProvider providerAdd
     * @group        medium
     */
    public function testGetEditorFormAdd($data, $matcher)
    {
        $GLOBALS['is_ajax_request'] = false;
        PMA_TRI_setGlobals();
        $this->assertContains(
            $matcher,
            PMA_TRI_getEditorForm('add', $data)
        );
    }

    /**
     * Provider for testGetEditorFormAdd
     *
     * @return array
     */
    public function providerAdd()
    {
        $data = [
            'item_name'               => '',
            'item_table'              => 'table1',
            'item_original_name'      => '',
            'item_action_timing'      => '',
            'item_event_manipulation' => '',
            'item_definition'         => '',
            'item_definer'            => '',
        ];

        return [
            [
                $data,
                "name='add_item'",
            ],
            [
                $data,
                "name='item_name'",
            ],
            [
                $data,
                "name='item_table'",
            ],
            [
                $data,
                "name='item_timing'",
            ],
            [
                $data,
                "name='item_event'",
            ],
            [
                $data,
                "name='item_definition'",
            ],
            [
                $data,
                "name='item_definer'",
            ],
            [
                $data,
                "name='editor_process_add'",
            ],
        ];
    }

    /**
     * Test for PMA_TRI_getEditorForm
     *
     * @param array $data    Data for trigger
     * @param array $matcher Matcher
     *
     * @return void
     *
     * @dataProvider providerEdit
     * @group        medium
     */
    public function testGetEditorFormEdit($data, $matcher)
    {
        $GLOBALS['is_ajax_request'] = false;
        PMA_TRI_setGlobals();
        $this->assertContains(
            $matcher,
            PMA_TRI_getEditorForm('edit', $data)
        );
    }

    /**
     * Provider for testGetEditorFormEdit
     *
     * @return array
     */
    public function providerEdit()
    {
        $data = [
            'item_name'               => 'foo',
            'item_table'              => 'table1',
            'item_original_name'      => 'bar',
            'item_action_timing'      => 'BEFORE',
            'item_event_manipulation' => 'INSERT',
            'item_definition'         => 'SET @A=1;',
            'item_definer'            => '',
        ];

        return [
            [
                $data,
                "name='edit_item'",
            ],
            [
                $data,
                "name='item_name'",
            ],
            [
                $data,
                "name='item_table'",
            ],
            [
                $data,
                "name='item_timing'",
            ],
            [
                $data,
                "name='item_event'",
            ],
            [
                $data,
                "name='item_definition'",
            ],
            [
                $data,
                "name='item_definer'",
            ],
            [
                $data,
                "name='editor_process_edit'",
            ],
        ];
    }

    /**
     * Test for PMA_TRI_getEditorForm
     *
     * @param array $data    Data for trigger
     * @param array $matcher Matcher
     *
     * @return void
     *
     * @dataProvider providerAjax
     */
    public function testGetEditorFormAjax($data, $matcher)
    {
        $GLOBALS['is_ajax_request'] = true;
        PMA_TRI_setGlobals();
        $this->assertContains(
            $matcher,
            PMA_TRI_getEditorForm('edit', $data)
        );
    }

    /**
     * Provider for testGetEditorFormAjax
     *
     * @return array
     */
    public function providerAjax()
    {
        $data = [
            'item_name'               => 'foo',
            'item_table'              => 'table1',
            'item_original_name'      => 'bar',
            'item_action_timing'      => 'BEFORE',
            'item_event_manipulation' => 'INSERT',
            'item_definition'         => 'SET @A=1;',
            'item_definer'            => '',
        ];

        return [
            [
                $data,
                "name='editor_process_edit'",
            ],
            [
                $data,
                "name='ajax_request'",
            ],
        ];
    }
}

?>
