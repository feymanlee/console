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
// 系统设置
//----------------------------------
require './Common.php';

//服务管理
function ServiceAdmin(){
	$name = I('name');
	$type = I('type');
	if($name == 'apache' || $name == 'httpd'){
		$name = 'httpd';
		SendSocket("ExecShell|/www/server/apache/bin/apachectl -t");
		$result = trim(file_get_contents('/tmp/shell_temp.pl'));
		if(strpos($result,'Syntax OK') === false){
			WriteLogs("环境设置", $exec."执行失败: ".$result);
			returnJson(false,'Apache配置规则错误: <br><a style="color:red;">'.str_replace("\n",'<br>',$result).'</a>');
		}
	} 
	if($name == 'nginx'){
		SendSocket("ExecShell|nginx -t -c /www/server/nginx/conf/nginx.conf");
		$result = file_get_contents('/tmp/shell_temp.pl');
		if(!strpos($result,'successful')){
			WriteLogs("环境设置", $exec."执行失败: ".$result);
			returnJson(false,'Nginx配置规则错误: <br><a style="color:red;">'.str_replace("\n",'<br>',$result).'</a>');
		}
	}
	
	
	if($type == 'test') returnJson(true, '配置检测通过!');
	$exec = "service $name $type";
	if($exec == 'service pure-ftpd reload') $exec = '/www/server/pure-ftpd/bin/pure-pw mkdb /www/server/pure-ftpd/etc/pureftpd.pdb';
	$result = SendSocket("ExecShell|$exec");
	WriteLogs("环境设置", $exec."执行成功!");
	ajax_return($result);
}

//取面板绑定端口和域名
function GetPanelBinding(){
	$data = array();
	$filename = $_SESSION['server_type'] == 'nginx' ? '/www/server/nginx/conf/nginx.conf':'/www/server/apache/conf/extra/httpd-vhosts.conf';
	$conf = file_get_contents($filename);
	$rep = $_SESSION['server_type'] == 'nginx'?"/listen\s+([0-9]{1,6})\s*.*;/":'/Listen\s+([0-9]{1,6})\s/';
	preg_match($rep,$conf,$tmp);
	$data['port'] = $tmp[1];	
	$rep = $_SESSION['server_type'] == 'nginx'?"/\s+server_name\s+([\w\._-]+);/":"/ServerName\s+([\w\._-]+)/";
	preg_match($rep,$conf,$tmp);
	$data['domain'] = $tmp[1];
	
	if($_SESSION['server_type'] == 'nginx'){
		$data['defaultSite'] = file_exists('/www/server/nginx/conf/vhost/default.conf')?true:false;
	}
	
	return $data;
}

//设置面板端口和域名
function SetPanelBinding(){
	$port 	= I('port','','intval');
	$domain = I('domain');
	if(!checkSetPort($port)) returnJson(false, '端口范围不合法!');
	$panel = GetPanelBinding();
	$file = $_SESSION['server_type'] == 'nginx' ? '/www/server/nginx/conf/nginx.conf':'/www/server/apache/conf/extra/httpd-vhosts.conf';
	$conf = file_get_contents($file);
	if($port != $panel['port']){
		$conf = str_replace($panel['port'],$port,$conf);
		//添加防火墙
		SendSocket("Firewall|AddFireWallPort|".$port."|TCP|webPanel");
		//修改防火墙列表
		M('firewall')->where("port='".$panel['port']."'")->setField('port',$port);
	}
	
	if($domain != $panel['domain'] && strlen($domain) > 3){
		$conf = str_replace($panel['domain'],$domain,$conf);
	}
	
	if (file_put_contents('/tmp/read.tmp', $conf)) {
		$result = SendSocket("FileAdmin|SaveFile|" . $file);
		if($result['status']) WriteLogs("环境设置", "将面板端口改为[$port],绑定域名[$domain]!");
		return true;
	}
	return false;
}

//保存配置
function SaveConfig(){
	$result['status']  = false;
	$result['status'] = SetPanelBinding();
	
	$data = array(
		'sites_path'	=>	I('sites_path'),
		'backup_path'	=>	I('backup_path')
	);
	
	if(M('config')->where("id=1")->save($data)){
		$_SESSION['config']['sites_path'] = $data['sites_path'];
		$_SESSION['config']['backup_path'] = $data['backup_path'];
		SendSocket("FileAdmin|AddDir|".$data['sites_path']);
		SendSocket("FileAdmin|AddDir|".$data['backup_path'].'/database');
		SendSocket("FileAdmin|AddDir|".$data['backup_path'].'/site');
		$result['status'] = true;
	}
	
	if(M('user')->where("id=1")->setField('email',I('email'))) $_SESSION['config']['email'] = I('email');
	
	if($_POST['ip'] != $_SESSION['iplist']){
		file_put_contents("./conf/iplist.conf",$_POST['ip']);
		$_SESSION['iplist'] = $_POST['ip'];
		$result['status'] = true;
		WriteLogs("环境设置", "更新服务器IP为[".$_POST['ip']."]!");
	}
	
	$result['msg'] = $result['status']?'配置已保存':'没有做任何改变';
	if($result['status']){
		$result['port'] = I('port');
		$result['domain'] = I('domain');
		$result['script_name'] = $_SERVER['SCRIPT_NAME'];
		if($result['domain'] == 'www.bt.cn') $result['domain'] = $_SESSION['serverip'];
	}
	
	ajax_return($result);
}

//设置pathInfo支持
function SetPathInfo(){
	$version = I('version');
	$type = I('type');
	
	if($_SESSION['server_type'] == 'nginx'){
		$path = '/www/server/nginx/conf/enable-php-'.$version.'.conf';
		$conf = file_get_contents($path);
		if($type == 'on'){
			$conf = str_replace('#include pathinfo.conf;','include pathinfo.conf;',$conf);
		}else{
			$conf = str_replace('include pathinfo.conf;','#include pathinfo.conf;',$conf);
		}
		file_put_contents('/tmp/read.tmp',$conf);
		SendSocket("FileAdmin|SaveFile|" . $path);
	}
	$path = '/www/server/php/'.$version.'/etc/php.ini';
	$conf = file_get_contents($path);
	$rep = "/\n;*\s*cgi\.fix_pathinfo\s*=\s*([0-9]+)\s*\n/";
	$status = $type == 'on' ? '1':'0';
	$conf = preg_replace($rep, "\ncgi.fix_pathinfo = $status\n", $conf);
	file_put_contents('/tmp/read.tmp',$conf);
	SendSocket("FileAdmin|SaveFile|" . $path);
	if($result['status']) WriteLogs("环境设置", "设置PHP-${version} PATH_INFO模块为[{$type}]!");
	SendSocket("ExecShell|service php-fpm-$version reload");
	returnJson(true,'设置成功!');	
}

//设置PHP文件上传大小限制
function SetPHPMaxSize(){
	$version = $_GET['version'];
	$max = intval($_GET['max']);
	
	//设置PHP
	$path = '/www/server/php/'.$version.'/etc/php.ini';
	$conf = file_get_contents($path);
	$rep = "/^upload_max_filesize\s*=\s*[0-9]+M/m";
	$conf = preg_replace($rep,'upload_max_filesize = '.$max.'M',$conf);
	$rep = "/^post_max_size\s*=\s*[0-9]+M/m";
	$conf = preg_replace($rep,'post_max_size = '.$max.'M',$conf);
	if(file_put_contents('/tmp/read.tmp',$conf)){
		$result = SendSocket("FileAdmin|SaveFile|" . $path);
	}
	
	if($_SESSION['server_type'] == 'nginx'){
		//设置Nginx
		$path = '/www/server/nginx/conf/nginx.conf';
		$conf = file_get_contents($path);
		$rep = "/client_max_body_size\s+([0-9]+)m/m";
		preg_match($rep,$conf,$tmp);
		if(intval($tmp[1]) < intval($max)){
			$conf = preg_replace($rep,'client_max_body_size '.$max.'m',$conf);
			if(file_put_contents('/tmp/read.tmp',$conf)){
				$result = SendSocket("FileAdmin|SaveFile|" . $path);
			}
		}
	}
	serviceWebReload();
	SendSocket("ExecShell|service php-fpm-$version reload");
	if($result['status']) WriteLogs("环境设置", "设置PHP最大上传大小为[{$max}MB]!");
	returnJson(true,'设置成功!');	
}

//设置PHP超时时间
function SetPHPMaxTime(){
	$time = I('time','','intval');
	$version = I('version','','intval');
	if($time < 30 || $time > 86400*30) returnJson(false,'请填写30-86400*30间的值!');
	$file = '/www/server/php/'.$version.'/etc/php-fpm.conf';
	$conf = file_get_contents($file);
	$rep = "/request_terminate_timeout\s*=\s*([0-9]+)\n/";
	$conf = preg_replace($rep,"request_terminate_timeout = ".$time."\n",$conf);	
	if(file_put_contents('/tmp/read.tmp',$conf)){
		$result = SendSocket("FileAdmin|SaveFile|" . $file);
	}
	
	if($_SESSION['server_type'] == 'nginx'){
		//设置Nginx
		$path = '/www/server/nginx/conf/nginx.conf';
		$conf = file_get_contents($path);
		$rep = "/fastcgi_connect_timeout\s+([0-9]+);/";
		preg_match($rep, $conf,$tmp);
		if(intval($tmp[1]) < $time){
			$conf = preg_replace($rep,'fastcgi_connect_timeout '.$time.';',$conf);
			$rep = "/fastcgi_send_timeout\s+([0-9]+);/";
			$conf = preg_replace($rep,'fastcgi_send_timeout '.$time.';',$conf);
			$rep = "/fastcgi_read_timeout\s+([0-9]+);/";
			$conf = preg_replace($rep,'fastcgi_read_timeout '.$time.';',$conf);
			 
			if(file_put_contents('/tmp/read.tmp',$conf)){
				$result = SendSocket("FileAdmin|SaveFile|" . $path);
			}
		}
	}
	if($result['status']) WriteLogs("环境设置", "设置PHP最大脚本超时时间为[{$time}秒]!");
	serviceWebReload();
	SendSocket("ExecShell|service php-fpm-$version reload");
	returnJson(true, '保存成功!');
}

//设置防恶意解析
function SetDefaultSite(){
	if($_SESSION['server_type'] != 'nginx') return;
	$file = '/www/server/nginx/conf/vhost/default.conf';
	if(file_exists($file)) {
		SendSocket("FileAdmin|DelFile|".$file);
	}else{
		$conf=<<<EOT
server {
	listen 80 default_server;
	server_name _;
	root /www/server/nginx/html;
}
EOT;

		if (file_put_contents('/tmp/read.tmp', $conf)) {
			SendSocket("FileAdmin|SaveFile|".$file);
		}
	}
	serviceWebReload();
	returnJson(true, '设置成功!');
}

//取FPM设置
function GetFpmConfig(){
	$version = I('version');
	$file = "/www/server/php/$version/etc/php-fpm.conf";
	$conf = file_get_contents($file);
	$rep = "/\s*pm.max_children\s*=\s*([0-9]+)\s*/";
	preg_match($rep, $conf,$tmp);
	$data['max_children'] = $tmp[1];
	
	$rep = "/\s*pm.start_servers\s*=\s*([0-9]+)\s*/";
	preg_match($rep, $conf,$tmp);
	$data['start_servers'] = $tmp[1];
	
	$rep = "/\s*pm.min_spare_servers\s*=\s*([0-9]+)\s*/";
	preg_match($rep, $conf,$tmp);
	$data['min_spare_servers'] = $tmp[1];
	
	$rep = "/\s*pm.max_spare_servers \s*=\s*([0-9]+)\s*/";
	preg_match($rep, $conf,$tmp);
	$data['max_spare_servers'] = $tmp[1];
	
	ajax_return($data);
}

//设置
function SetFpmConfig(){
	$version = I('version');
	$max_children = I('max_children');
	$start_servers = I('start_servers');
	$min_spare_servers = I('min_spare_servers');
	$max_spare_servers = I('max_spare_servers');
	
	$file = "/www/server/php/$version/etc/php-fpm.conf";
	$conf = file_get_contents($file);
	
	$rep = "/\s*pm.max_children\s*=\s*([0-9]+)\s*/";
	$conf = preg_replace($rep, "\npm.max_children = ".$max_children, $conf);
	
	$rep = "/\s*pm.start_servers\s*=\s*([0-9]+)\s*/";
	$conf = preg_replace($rep, "\npm.start_servers = ".$start_servers, $conf);
	
	$rep = "/\s*pm.min_spare_servers\s*=\s*([0-9]+)\s*/";
	$conf = preg_replace($rep, "\npm.min_spare_servers = ".$min_spare_servers, $conf);
	
	$rep = "/\s*pm.max_spare_servers \s*=\s*([0-9]+)\s*/";
	$conf = preg_replace($rep, "\npm.max_spare_servers = ".$max_spare_servers."\n", $conf);
	if(file_put_contents('/tmp/read.tmp',$conf)){
		$result = SendSocket("FileAdmin|SaveFile|" . $file);
	}
	SendSocket("ExecShell|service php-fpm-$version reload");
	returnJson(true, '设置成功');
}

//接口动作
$_SESSION['crontab']['status'] = null;
if(isset($_GET['action'])){
	$_GET['action']();
	exit;
}


$ConfigInfo = GetConfigInfo();
$Panel = GetPanelBinding();

//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/config.html';
require './public/footer.html';
?>