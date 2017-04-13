<?php


//----------------------------------
// 操作日志
//----------------------------------
require './Common.php';

//接口动作
if (isset($_GET['action'])) {
    $_GET['action']();
    exit;
}

//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/logs.html';
require './public/footer.html';
?>
