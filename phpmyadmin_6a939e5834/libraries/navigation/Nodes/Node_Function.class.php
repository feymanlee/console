<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Functionality for the navigation tree
 *
 * @package PhpMyAdmin-Navigation
 */
if (!defined('PHPMYADMIN')) {
    exit;
}

require_once 'libraries/navigation/Nodes/Node_DatabaseChild.class.php';

/**
 * Represents a function node in the navigation tree
 *
 * @package PhpMyAdmin-Navigation
 */
class Node_Function extends Node_DatabaseChild
{
    /**
     * Initialises the class
     *
     * @param string $name     An identifier for the new node
     * @param int    $type     Type of node, may be one of CONTAINER or OBJECT
     * @param bool   $is_group Whether this object has been created
     *                         while grouping nodes
     *
     * @return Node_Function
     */
    public function __construct($name, $type = Node::OBJECT, $is_group = false)
    {
        parent::__construct($name, $type, $is_group);
        $this->icon    = PMA_Util::getImage('b_routines.png', __('Function'));
        $this->links   = [
            'text' => 'db_routines.php?server=' . $GLOBALS['server']
                . '&amp;db=%2$s&amp;item_name=%1$s&amp;item_type=FUNCTION'
                . '&amp;execute_dialog=1&amp;token=' . $_SESSION[' PMA_token '],
            'icon' => 'db_routines.php?server=' . $GLOBALS['server']
                . '&amp;db=%2$s&amp;item_name=%1$s&amp;item_type=FUNCTION'
                . '&amp;edit_item=1&amp;token=' . $_SESSION[' PMA_token '],
        ];
        $this->classes = 'function';
    }

    /**
     * Returns the type of the item represented by the node.
     *
     * @return string type of the item
     */
    protected function getItemType()
    {
        return 'function';
    }
}

?>
