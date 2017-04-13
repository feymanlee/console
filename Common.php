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
// 公共入口
//----------------------------------
include_once './model/Public.model.php';
error_reporting(E_ALL^E_NOTICE^E_WARNING);
@session_start();
if(empty($_SESSION['brand'])){
	session('brand', '宝塔');
	session('product','Linux面板');
	session('version','Linux版');
	session('info-n',"2.7.7");
}
if(empty($_SESSION['config']['status'])){
	$status = M('config')->where("id=1")->getField('status');
	$_SESSION['config']['status'] = $status;
}

if(empty($_SESSION['iplist']) == true  || $_SESSION['iplist'] != $_SESSION['serverip']){
	$_SESSION['iplist'] = @file_get_contents('./conf/iplist.conf');
	if(empty($_SESSION['iplist'])){
		$_SESSION['iplist'] = $_SESSION['serverip'];
	}
	$tmp = explode(',',$_SESSION['iplist']);
	$_SESSION['serverip'] = $tmp[0];
}

//是否初始化
if(empty($_SESSION['config']['status'])){
	if(intval($status) != 1 && $_SERVER['SCRIPT_NAME'] != '/install.php'){
		header("Location: install.php");
		exit;
	}
}else{
	//验证是否登陆
	if(empty($_SESSION["username"]) && $_SERVER['SCRIPT_NAME'] != '/login.php')
	{
		header("Location: login.php");
		exit;
	}
	GetServerInfo();
}
//取服务器信息
function GetServerInfo(){
	if(empty($_SESSION['system'])){
		$where = "id='1'";
		$find = M('config')->where($where)->find();
		$result = SendSocket('Info');
		if(!$result){
			header("Content-type: text/html; charset=utf-8"); 
			exit('抱歉，连接接口失败，请尝试在SSH命令行输入 service yunclient start 启动接口!');
		}
		$ip = explode(',',$result['address']);
		session('config',$find);
		session('system','Linux');					//操作系统类型
		session('mysql_root',$find['mysql_root']);	//MySQL管理员密码
		session('info',$result['info']);			//接口版本
		session('server_type',$find['webserver']);	//HTTP服务器类型
		session('serverip',GetMainAddress($ip));	//服务器IP
		session('phpmyadminDirName', trim(file_get_contents('/www/server/cloud/phpmyadminDirName.pl')));
		session('phpmodel',trim(file_get_contents('/www/server/php/version.pl')));
	}
}

//取主要IP地址
function GetMainAddress($ip){
	foreach($ip as $address){
		if(strlen($address) < 8) continue;
		$tmp = explode('.',$address);
		if($tmp[0] == '10') continue;
		if($tmp[0] == '192' && $tmp[1] == '168') continue;
		if($tmp[0] == '172' && $tmp[1] > 15 and $tmp[1] < 32) continue;
		return $address;
	}
	if(strlen($ip[0]) < 8) return $ip[1];
	return $ip[0];
}

//SESSION赋值
function session($key,$value){
	@session_start();
	
	if($value){
		$_SESSION[$key] = $value;
		return true;
	}
	return $_SESSION[$key];
}

//取配置信息
function GetConfigInfo(){
	if(!isset($_SESSION['config'])){
		$_SESSION['config'] = M('config')->where("id=1")->find();
	}
	if(!isset($_SESSION['config']['email'])){
		$_SESSION['config']['email'] = M('user')->where("id=1")->getField('email');
	}
	$data = $_SESSION['config'];
	
	$phpVersions = array('52','53','54','55','56','70','71');
	
	foreach($phpVersions as $val){
		$data['php'][$val]['setup'] = file_exists('/www/server/php/'.$val.'/bin/php');
		if($data['php'][$val]['setup']){
			$phpConfig = GetPHPConfig($val);
			$data['php'][$val]['max'] = $phpConfig['max'];
			$data['php'][$val]['maxTime'] = $phpConfig['maxTime'];
			$data['php'][$val]['pathinfo'] = $phpConfig['pathinfo'];
		}
		$data['php'][$val]['status'] = file_exists('/tmp/php-cgi-'.$val.'.sock');
	}
	
	$data['web']['type'] = $data['webserver'];
	$data['web']['version'] = file_get_contents('/www/server/'.$data['webserver'].'/version.pl');
	$data['mysql']['version'] = file_get_contents('/www/server/mysql/version.pl');
	$data['mysql']['status'] = file_exists('/tmp/mysql.sock');
	$data['pure-ftpd']['version'] = file_get_contents('/www/server/pure-ftpd/version.pl');
	$data['pure-ftpd']['status'] = file_exists('/var/run/pure-ftpd.pid');
	return $data;
}

//取PHP配置信息
function GetPHPConfig($version){
	$file = "/www/server/php/{$version}/etc/php.ini";
	$phpini = file_get_contents($file);
	$file = "/www/server/php/{$version}/etc/php-fpm.conf";
	$phpfpm = file_get_contents($file);
	
	$rep = "/^upload_max_filesize\s*=\s*([0-9]+)M/m";
	preg_match($rep,$phpini,$tmp);
	$data['max'] = $tmp[1];
	
	$rep = "/request_terminate_timeout\s*=\s*([0-9]+)\n/";
	preg_match($rep,$phpfpm,$tmp);
	$data['maxTime'] = $tmp[1];
	
	$rep = "/\n;*\s*cgi\.fix_pathinfo\s*=\s*([0-9]+)\s*\n/";
	preg_match($rep,$phpini,$tmp);
	
	$data['pathinfo'] = $tmp[1] == '0'?true:false;
	
	return $data;
}

/**
 * 取行数
 * @param String $table 表名
 * @param void $where 查询条件
 * @return Int
 */
 function getCount($table){
 	$SQL = M($table);
	return $SQL->getCount();
 }
 
//检测端口合法性
function checkSetPort($port){
	if(intval($port) > 65535 || intval($port) < 21 ) return false;
	$ports = array('1','21','25326','20','22');
	if(in_array($port, $ports)) return false;
	return true;
}

//检查Apache配置文件
function checkHttpdConf(){
	SendSocket("ExecShell|/www/server/apache/bin/apachectl -t");
	$result = trim(file_get_contents('/tmp/shell_temp.pl'));
	if(strpos($result,'Syntax OK') === false){
		WriteLogs("环境设置", "配置文件错误: ".$result);
		return $result;
	}
	return true;
}
//检查Nginx配置文件
function checkNginxConf(){
	SendSocket("ExecShell|nginx -t -c /www/server/nginx/conf/nginx.conf");
	$reConf = file_get_contents('/tmp/shell_temp.pl');
	if(!strpos($reConf,'successful')){
		WriteLogs("修改配置", "配置文件错误: ".$reConf);
		return $reConf;
	}
	
	return true;
}

//检查目录限制
function checkMainPath($path){
	if($path == '/') return true;
	if(substr($path,strlen($path)-1,1) == '/'){
		$path = substr($path,0,strlen($path)-1);
	}
	$limit = array('/','/root','/usr','/var','bin','/sbin','/www','/www/server','/www/server/data','/www/wwwroot/default');
	if(in_array($path,$limit)) return true;
	return false;
}

/**
 * 取字符串中间
 * @param String $content 源文本
 * @param String $start 开始文本
 * @param String $end	结束文本
 * @return String
 */
function GetBetween($content,$start,$end){
	$r = explode($start, $content);
	if (isset($r[1])){
		$r = explode($end, $r[1]);
		return $r[0];
	}
	return '';
}

?>
