<?php


//----------------------------------
// 输出验证码
//----------------------------------
include_once './class/Code.class.php';
@session_start();
$codeImage = new Code(30);
$codeImage->getImage();
?>