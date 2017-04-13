<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for format number and byte
 *
 * @package PhpMyAdmin-test
 * @group   common.lib-tests
 */

/*
 * Include to test.
 */
require_once 'libraries/Util.class.php';
require_once 'libraries/php-gettext/gettext.inc';

/**
 * Test for format number and byte
 *
 * @package PhpMyAdmin-test
 * @group   common.lib-tests
 */
class PMA_FormatNumberByteDown_Test extends PHPUnit_Framework_TestCase
{
    /**
     * temporary variable for globals array
     */
    protected $tmpGlobals;

    /**
     * temporary variable for session array
     */
    protected $tmpSession;

    /**
     * storing globals and session
     *
     * @return void
     */
    public function setUp()
    {
        $this->tmpGlobals = $GLOBALS;
        $this->tmpSession = $_SESSION;

    }

    /**
     * recovering globals and session
     *
     * @return void
     */
    public function tearDown()
    {
        $GLOBALS  = $this->tmpGlobals;
        $_SESSION = $this->tmpSession;

    }

    /**
     * format number data provider
     *
     * @return array
     */
    public function formatNumberDataProvider()
    {
        return [
            [10, 2, 2, '10  '],
            [100, 2, 0, '100  '],
            [100, 2, 2, '100  '],
            [-1000.454, 4, 2, '-1,000.45  '],
            [0.00003, 3, 2, '30 &micro;'],
            [0.003, 3, 3, '3 m'],
            [-0.003, 6, 0, '-3,000 &micro;'],
            [100.98, 0, 2, '100.98'],
        ];
    }

    /**
     * format number test, globals are defined
     *
     * @param float $a Value to format
     * @param int   $b Sensitiveness
     * @param int   $c Number of decimals to retain
     * @param array $d Expected value
     *
     * @return void
     *
     * @dataProvider formatNumberDataProvider
     */
    public function testFormatNumber($a, $b, $c, $d)
    {
        $this->assertEquals(
            $d,
            (string)PMA_Util::formatNumber(
                $a, $b, $c, false
            )
        );
    }

    /**
     * format byte down data provider
     *
     * @return array
     */
    public function formatByteDownDataProvider()
    {
        return [
            [10, 2, 2, ['10', __('B')]],
            [100, 2, 0, ['0', __('KiB')]],
            [100, 3, 0, ['100', __('B')]],
            [100, 2, 2, ['0.10', __('KiB')]],
            [1034, 3, 2, ['1.01', __('KiB')]],
            [100233, 3, 3, ['97.884', __('KiB')]],
            [2206451, 1, 2, ['2.10', __('MiB')]],
            [21474836480, 4, 0, ['20', __('GiB')]],
        ];
    }

    /**
     * format byte test, globals are defined
     *
     * @param float $a Value to format
     * @param int   $b Sensitiveness
     * @param int   $c Number of decimals to retain
     * @param array $e Expected value
     *
     * @return void
     *
     * @dataProvider formatByteDownDataProvider
     */
    public function testFormatByteDown($a, $b, $c, $e)
    {
        $result    = PMA_Util::formatByteDown($a, $b, $c);
        $result[0] = trim($result[0]);
        $this->assertEquals($e, $result);
    }
}

?>
