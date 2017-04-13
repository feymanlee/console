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
// 服务器状态
//----------------------------------
include_once './Common.php';
/**
 * 设置新密码
 * @param array $_POST 密码数组
 * @return json
 */
function setPassword(){
	//判断数据是否完整
	if($_POST['password1'] == ''){
		returnJson(false, '错误，新密码不能为空!');
	}
	//判断密码是否一致
	if($_POST['password1'] != $_POST['password2']){
		returnJson(false, '错误，两次输入的密码不一致!');
	}
	//提交数据库
	$SQL = M('user');
	if($SQL->where("id='1'")->setField('password',md5(I('post.password1'))))
	{
		returnJson(true, '修改成功!');
	}
	returnJson(false, '与原密码相同!');
}

/**
 * 设置新用户名
 * @param array $_POST
 * @return json
 */
function setUserName(){
	//判断数据是否完整
	if($_POST['username1'] == ''){
		returnJson(false, '错误，新用户名不能为空!');
	}
	//判断密码是否一致
	if($_POST['username1'] != $_POST['username2']){
		returnJson(false, '错误，两次输入的用户名不一致!');
	}
	//提交数据库
	$SQL = M('user');
	if($SQL->where("id='1'")->setField('username',I('post.username1')))
	{
		$_SESSION['username'] = $_POST['username1'];
		returnJson(true, '修改成功!');
	}
	returnJson(false, '与原用户相同!');
}

//检查与更新版本
function update(){
	//取回远程版本信息
	if(isset($_SESSION['updateInfo'])){
		$updateInfo = $_SESSION['updateInfo'];
	}else{
		$updateInfo = json_decode(httpGet('http://www.bt.cn/Api/updateLinuxClient'),1);
		if(empty($updateInfo)) returnJson(false,"连接云端服务器失败!");
		session('updateInfo', $updateInfo);
	}
	
	//检查是否需要升级
	if($updateInfo['version'] == $_SESSION['info-n']){
		returnJson(false,"当前已经是最新版本!");
	}
	
	//是否执行升级程序
	if($updateInfo['force'] == true || $_GET['toUpdate'] == 'yes'){
		$result = SendSocket("UpdateWeb|".$updateInfo['downUrl']);
		if($result['status'] == true){
			session('info-n', $updateInfo['version']);
			returnJson(true,'成功升级到'.$updateInfo['version']);
		}
		returnJson(false,'文件下载失败!');
	}
	
	//输出新版本信息
	$data = array(
		'status' => true,
		'version'=> $updateInfo['version'],
		'updateMsg'=> $updateInfo['updateMsg']
	);
	ajax_return($data);
}

//检查MySQL目录权限
function checkMySQLPath(){
	$data = '/www/server/data';
	$own = posix_getpwuid(fileowner($data));
	if($own['name'] != 'mysql'){
		SendSocket("FileAdmin|ChownFile|" . $data . '|mysql');
	}
	$own = posix_getpwuid(fileowner('/etc/my.cnf'));
	if($own['name'] != 'root'){
		SendSocket("FileAdmin|ChownFile|/etc/my.cnf|root");
		SendSocket("FileAdmin|ChmodFile|/etc/my.cnf|644");
	}
	
	
}

//设置面板PHP版本
function SetPanelPHPVersion(){
	$version = I('version');
	if($_SESSION['server_type'] == 'nginx'){
	$file = "/www/server/nginx/conf/enable-php.conf";
	$conf=<<<EOT
location ~ [^/]\.php(/|$)
{
	try_files \$uri =404;
	fastcgi_pass  unix:/tmp/php-cgi-$version.sock;
	fastcgi_index index.php;
	include fastcgi.conf;
	#include pathinfo.conf;
}
EOT;
	}else{
		$file = "/www/server/apache/conf/extra/httpd-vhosts.conf";
		$conf = file_get_contents($file);
		$rep = "/php-cgi-([0-9]+).sock/";
		$conf = preg_replace($rep, "php-cgi-$version.sock", $conf,1);
	}
	
	file_put_contents('/tmp/read.tmp',$conf);
	$result = SendSocket("FileAdmin|SaveFile|" . $file);
	serviceWebReload();
	returnJson(true, '面板使用版本已切换');
}

//重启服务器
function ReBoot(){
	WriteLogs('服务管理', '重启服务器!');
	SendSocket("ExecShell|service mysqld stop");
	SendSocket("ExecShell|init 6");
	returnJson(true, '正在重启服务器，请稍等几分钟再刷新页面!');
}

//接口动作
if(isset($_GET['action'])){
	$_GET['action']();
	exit;
}
//取磁盘
$Disk = SendSocket("System|disk");
if(!is_array($Disk)) $Disk = array();
if(empty($Disk[0])){
	for($i=1;$i<count($Disk)+1;$i++){
		$Disk[$i]['使用率'] = ceil(($Disk[$i]['总容量'] - $Disk[$i]['可用空间']) / ($Disk[$i]['总容量'] / 100)); 
	}
}

if(file_exists('/tmp/exec_shell.pl')) SendSocket("FileAdmin|DelFile|/tmp/exec_shell.pl");



//取统计
$count['sites'] = intval(getCount('sites'));
$count['ftps'] = intval(getCount('ftps'));
$count['databases'] = intval(getCount('databases'));
session('server_count',$count);
checkMySQLPath();

//取环境套件信息
$ConfigInfo = GetConfigInfo();

//包含头部HTML
include_once './public/head.html';
include_once './public/menu.html';
include_once './public/index.html';
include_once './public/footer.html';
?>