<?php
// +----------------------------------------------------------------------
// | 宝塔服务器助手[Linux面板]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 宝塔软件(http://bt.cn) All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 黄文良 <2879625666@qq.com>
// +----------------------------------------------------------------------

//----------------------------------
// 管理员登陆
//----------------------------------
include_once './Common.php';
@session_start();
$_SESSION['error_count'] = isset($_SESSION['error_count'])? $_SESSION['error_count']:0;
if(isset($_GET['dologin'])) dologin();
if(isset($_POST['username'])) login();
//登陆
function login(){
	$tmp = @file_get_contents('./conf/breakIp.conf');
	$breakIp = explode('|',$tmp);
	$ip = get_client_ip();
	if($ip == $breakIp[0] && (time() - $breakIp[1]) < 600){
		header("Content-type: text/html; charset=utf-8"); 
		exit('您多次登陆失败，禁止登陆10分钟!');
	}
	
	if($_SESSION['error_count'] > 6){
		file_put_contents('./conf/breakIp.conf', $ip.'|'.time());
		$_SESSION['error_count'] = 4;
	}
	
	//是否验证图形验证码
	if($_SESSION['error_count'] > 3){
		include_once './class/Code.class.php';
		if(Code::check($_POST['code']) == false){
			$_SESSION['error'] = "请输入正确的验证码!";
			$_SESSION['error_count']++;
			unset($_POST['username']);
			return;
		}
	}
	
	//是否提交登陆
	if(isset($_POST['username']) && isset($_POST['password']))
	{
		$sql = M('user');
		$ip = get_client_ip();
		//表单过滤
		$username = str_replace(' ', '', I('username'));
		$password = md5(trim(I('password')));
		
		//取出用户信息
		$isLogin = $sql->where("id='1'")->find();
		
		//二次验证
		if($password != $isLogin['password'] || $username != $isLogin['username']) $isLogin = false;
		if($isLogin){
			$sql->where('id=1')->save(array('login_ip'=>$ip,'login_time'=>date('Y-m-d H:i:s')));
			$_SESSION['username'] = $_POST['username'];
			WriteLogs("登陆", "登陆成功;登陆IP:$ip");
			unset($_SESSION['error_count']);
			unset($_SESSION['error']);
			header("Location: /index.php");
			exit;
		}else{
			WriteLogs("登陆", "登陆失败;帐号:$username,密码:".$_POST['password'].",登陆IP:$ip");
			$_SESSION['error'] = "帐号或密码不正确!";
			$_SESSION['error_count']++;
		}
	}
}

//退出登陆
function dologin(){
	$_SESSION = array();
	if (isset ( $_COOKIE [session_name ()] )) {
		setcookie ( session_name (), '', time () - 1, '/' );
	}
	session_destroy ();
	header("Location: /login.php");
	exit;
}
//包含头部与菜单
require './public/login.html';
?>