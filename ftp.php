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
// FTP管理
//----------------------------------
require './Common.php';

//添加FTP
function AddUser(){
	if(preg_match("/\W+/",$_POST['ftp_username'])) ajax_return(array('status'=>false,'code'=>501,'msg'=>'用户名不合法,不能带有特殊符号'));
	if(strlen($_POST['ftp_username']) < 3)ajax_return(array('status'=>false,'code'=>501,'msg'=>'用户名不合法,不能少于3个字符'));
	if(checkMainPath($_POST['path'])) ajax_return(array('status'=>false,'code'=>501,'msg'=>'不能以系统关键目录作为FTP目录'));
	$username = str_replace(' ', '', I('post.ftp_username'));
	$password = I('post.ftp_password');
	$path = str_replace(' ', '', I('post.path'));
	$path = str_replace("\\", "/", $path);
	$exec = 'FTP|add|'.$username.'|'.$password.'|'.$path;
	$ret = SendSocket($exec);
	if($ret['status'] == 'true'){
		$SQL = M('ftps');
		$data = array(
			'name' 		=> $username,
			'password' 	=> RCODE($password,'ENCODE'),
			'path' 		=> $path,
			'status' 	=> 1,
			'ps' 		=> ($_POST['ps'] == '')?'填写备注':$_POST['ps']
		);
		$SQL->add($data);
		WriteLogs('FTP管理', '添加FTP用户['.$username.']成功!');
		$_SESSION['server_count']['ftps']++;
		ajax_return(array('status'=>true,'msg'=>'添加成功'));
	}else{
		WriteLogs('FTP管理', '添加FTP用户['.$username.']失败!');
		ajax_return(array('status'=>true,'msg'=>'添加失败'));
	}
}

//删除用户
function DeleteUser(){
	$username = $_GET['username'];
	$id = $_GET['id'];
	$ret = SendSocket('FTP|delete|'.$username.'|否|');
	if($ret['status'] == 'true'){
		M('ftps')->where("id='$id'")->delete();
		WriteLogs('FTP管理', '删除FTP用户['.$username.']成功!');
		$_SESSION['server_count']['ftps']--;
		returnJson(true, "删除用户成功!");
	}else{
		WriteLogs('FTP管理', '删除FTP用户['.$username.']失败!');
		returnJson(false, "接口连接失败!");
	}
}

//修改用户密码
function SetUserPassword(){
	$id = I('post.id');
	$username = I('post.ftp_username');
	$password = I('post.new_password');
	$ret = SendSocket('FTP|password|'.$username.'|'.$password);
	if($ret['status'] == 'true'){
		M('ftps')->where("id='$id'")->setField('password',$password);
		WriteLogs('FTP管理', 'FTP用户['.$username.']，修改密码成功!');
		ajax_return(true);
	}else{
		WriteLogs('FTP管理', 'FTP用户['.$username.']，修改密码失败!');
		ajax_return(false);
	}
}

//设置用户状态
function SetStatus(){
	$id = I('id');
	$username = I('username');
	$status = I('status');
	$result = SendSocket('FTP|status|'.$username.'|'.$status);
	
	if(@$result['status'] == 'true'){
		M('ftps')->where("id='$id'")->setField('status',$status);
		WriteLogs('FTP管理', "停用FTP用户[$username]成功!");
		returnJson(true, '操作成功');
	}
	WriteLogs('FTP管理', "停用FTP用户[$username]失败!");
	returnJson(false, '接口连接失败!');
}

/**
 * 设置FTP端口
 * @param Int $_GET['port'] 端口号 
 * @return bool
 */
function setPort(){
	$port = I('get.port','','intval');
	if($port < 1 || $port > 65535) returnJson(false,'端口格式错误,范围:1-65535');
	$file = '/www/server/pure-ftpd/etc/pure-ftpd.conf';
	$conf = file_get_contents($file);
	$rep = "/^#? *Bind\s+[0-9]+\.[0-9]+\.[0-9]+\.+[0-9]+,([0-9]+)/m";
	preg_match($rep,$conf,$tmp);
	$conf = preg_replace($rep,"Bind		0.0.0.0,".$port,$conf);
	if (file_put_contents('/tmp/read.tmp', $conf)) {
		
		//保存文件
		$result = SendSocket("FileAdmin|SaveFile|" . $file);
		if(!$result['status']){
			returnJson(false, '文件转存失败!');
		} 
		
		WriteLogs('FTP管理', "修改FTP端口为[$port]成功!");
		
		//添加防火墙
		SendSocket("Firewall|AddFireWallPort|".$port."|TCP|ftp");
		//重载服务
		$exec = 'service pure-ftpd restart';
		SendSocket("ExecShell|$exec");
		//修改防火墙列表
		M('firewall')->where("port='".$tmp[1]."'")->setField('port',$port);
		returnJson(true, '修改成功');
	}
	
	returnJson(false, '修改失败!');
}


//接口动作
if(isset($_GET['action'])){
	$_GET['action']();
	exit;
}

$file = '/www/server/pure-ftpd/etc/pure-ftpd.conf';
$conf = file_get_contents($file);
$rep = "/^#? *Bind\s+[0-9]+\.[0-9]+\.[0-9]+\.+[0-9]+,([0-9]+)/m";
preg_match($rep,$conf,$tmp);
$_SESSION['port'] = $tmp[1];

//包含头部与菜单
require './public/head.html'; 
require './public/menu.html';
require './public/ftp.html';
require './public/footer.html';
?>