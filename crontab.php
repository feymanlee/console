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
// 计划任务
//----------------------------------
require './Common.php';


//取计划任务列表
function GetCrontab(){
	checkBackup();
	$data = M('crontab')->order("addtime desc")->select();
	
	for($i=0;$i<count($data);$i++){
		
		switch($data[$i]['type']){
			case 'day':
				$data[$i]['type'] = "每天";
				$data[$i]['cycle'] = '每天, '.$data[$i]['where_hour'].'点'.$data[$i]['where_minute'].'分 执行';
				break;
			case 'day-n':
				$data[$i]['type'] = "每".$data[$i]['where1'].'天';
				$data[$i]['cycle'] = '每隔'.$data[$i]['where1'].'天 '.$data[$i]['where_hour'].'点'.$data[$i]['where_minute'].'分 执行';
				break;
			case 'hour':
				$data[$i]['type'] = "每小时";
				$data[$i]['cycle'] = '每小时, 第'.$data[$i]['where_minute'].'分钟 执行';
				break;
			case 'hour-n':
				$data[$i]['type'] = "每".$data[$i]['where1'].'小时';
				$data[$i]['cycle'] = '每隔'.$data[$i]['where1'].'小时 第'.$data[$i]['where_minute'].'分钟 执行';
				break;
			case 'minute-n':
				$data[$i]['type'] = "每".$data[$i]['where1'].'分钟';
				$data[$i]['cycle'] = '每隔'.$data[$i]['where1'].'分钟执行';
				break;
			case 'week':
				$data[$i]['type'] = "每周";
				$data[$i]['cycle'] = '每周'.toWeek($data[$i]['where1']).', '.$data[$i]['where_hour'].'点'.$data[$i]['where_minute'].'分执行';
				break;
			case 'month':
				$data[$i]['type'] = "每月";
				$data[$i]['cycle'] = '每月, '.$data[$i]['where1'].'日 '.$data[$i]['where_hour'].'点'.$data[$i]['where_minute'].'分执行';
				break;
		}
	}
	
	return $data;
}

//检查环境
function checkBackup(){
	
	//检查表是否存在
	$count = SqlQuery("select COUNT(TABLE_NAME) from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='bt_default' and TABLE_NAME='bt_crontab'");
	if(intval($count['COUNT(TABLE_NAME)']) == 0){
		$sql = "CREATE TABLE bt_default.bt_crontab( 
					`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
					`name` varchar(64) DEFAULT '' COMMENT '任务名称',
					`type` varchar(24) DEFAULT '' COMMENT '任务类型',
					`where1` varchar(24) DEFAULT '' COMMENT '条件1',
					`where_hour` int(4) DEFAULT 0 COMMENT '小时',
					`where_minute` int(4) DEFAULT 0 COMMENT '分钟',
					`echo` varchar(32) DEFAULT '' COMMENT '输出标记', 
					`addtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间', 
					PRIMARY KEY (`id`),INDEX(`name`) 
				) 
				ENGINE=MyISAM DEFAULT CHARSET=utf8";
		SqlExec($sql);
	}
	
	//检查备份脚本是否存在
	if(!file_exists('/www/server/cloud/backup')){
		$file = '/www/server/cloud/backup';
		$backup = file_get_contents('http://www.bt.cn/linux/backup.sh');
		file_put_contents('/tmp/read.tmp',$backup);
		SendSocket("FileAdmin|SaveFile|$file");
		SetAccess($file);
	}
	
	//检查日志切割脚本是否存在
	if(!file_exists('/www/server/cloud/logsBackup')){
		$file = '/www/server/cloud/logsBackup';
		$backup = file_get_contents('http://www.bt.cn/linux/logsBackup.py');
		file_put_contents('/tmp/read.tmp',$backup);
		SendSocket("FileAdmin|SaveFile|$file");
		SetAccess($file);
	}
	
	//检查计划任务服务状态
	SendSocket("ExecShell|service crond status");
	$result = trim(file_get_contents('/tmp/shell_temp.pl'));
	if(strpos($result,'running') === false) SendSocket("ExecShell|service crond start");
	
}

//转换大写星期
function toWeek($num){
	switch($num){
		case '0':
			return '日';
		case '1':
			return '一';
		case '2':
			return '二';
		case '3':
			return '三';
		case '4':
			return '四';
		case '5':
			return '五';
		case '6':
			return '六';
	}
}

//添加计划任务
function AddCrontab(){
	if(empty($_POST['name'])) returnMsg(false, '任务名称不能为空!');
	$type = I('type');
	switch($type){
		case 'day':
			$cuonConfig = GetDay();
			$name = "每天";
			break;
		case 'day-n':
			$cuonConfig = GetDay_N();
			$name = "每".I('where1').'天';
			break;
		case 'hour':
			$cuonConfig = GetHour();
			$name = "每小时";
			break;
		case 'hour-n':
			$cuonConfig = GetHour_N();
			$name = "每小时";
			break;
		case 'minute-n':
			$cuonConfig = Minute_N();
			break;
		case 'week':
			$_POST['where1'] = I('week');
			$cuonConfig = Week();
			break;
		case 'month':
			$cuonConfig = Month();
			break;
	}
	
	$cronPath = '/www/server/cron';
	$cronName = GetShell();
	$cuonConfig .= $cronPath.'/'.$cronName.' >> '.$cronPath.'/'.$cronName.'.log 2>&1';
	WriteShell($cuonConfig);
	CrondReload();
	$data = array(
		'name'	=>	I('name'),
		'type'	=>	$type,
		'where1' =>	I('where1'),
		'where_hour' =>	I('hour','','intval'),
		'where_minute' =>	I('minute','','intval'),
		'echo'	=>	$cronName
	);
	
	if(M('crontab')->add($data)){
		WriteLogs('计划任务', '添加计划任务['.$data['name'].']成功!');
		returnMsg(true, '添加成功!');
	}
	
	returnMsg(false, '添加失败!');
}

//取数据列表
function GetDataList(){
	$type = I('type');
	$data = M($type)->field('name,ps')->select();
	ajax_return($data);
}

//写消息
function returnMsg($status,$msg){
	$_SESSION['crontab']['status'] = $status?'true':'false';
	$_SESSION['crontab']['msg'] = $msg;
	header("Location: crontab.php");
	exit;
}

//取任务日志
function GetLogs(){
	$id = I('id','','intval');
	$echo = M('crontab')->where("id=$id")->getField('echo');
	$logFile = '/www/server/cron/'.$echo.'.log';
	if(!file_exists($logFile)) returnJson(false, '当前任务日志为空!');
	$log = file_get_contents($logFile);
	$where = "Warning: Using a password on the command line interface can be insecure.\n";
	if(strpos($log, $where) !== false){
		$log = str_replace($where, '', $log);
		file_put_contents('/tmp/read.tmp',$log);
		SendSocket("FileAdmin|SaveFile|". $logFile);
	}
	
	returnJson(true, $log);
}

//清理任务日志
function DelLogs(){
	$id = I('id','','intval');
	$echo = M('crontab')->where("id=$id")->getField('echo');
	$logFile = '/www/server/cron/'.$echo.'.log';
	SendSocket("FileAdmin|DelFile|".$logFile);
	returnJson(true, '日志已清空!');
}

//删除计划任务
function DelCrontab(){
	$id = I('id');
	$find = M('crontab')->where("id=$id")->find();
	$file = '/var/spool/cron/root';
	SendSocket("FileAdmin|ReadFile|" . $file);
	$conf = file_get_contents('/tmp/read.tmp');
	$rep = "/\n.+".$find['echo'].".+\n/";
	$conf = preg_replace($rep, "\n", $conf);
	file_put_contents('/tmp/read.tmp',$conf);
	$cronPath = '/www/server/cron';
	$result = SendSocket("FileAdmin|SaveFile|". $file);
	if($result['status']){
		SendSocket("FileAdmin|ChmodFile|" . $file . '|600');
		SendSocket("FileAdmin|DelFile|".$cronPath.'/'.$find['echo']);
		SendSocket("FileAdmin|DelFile|".$cronPath.'/'.$find['echo'].'.log');
		CrondReload();
		M('crontab')->where("id=$id")->delete();
		WriteLogs('计划任务', '删除计划任务['.$find['name'].']成功!');
		returnMsg(true, '删除成功!');
	}
	returnMsg(false, '写入配置到计划任务失败!');
}

//重载配置
function CrondReload(){
	SendSocket("ExecShell|/etc/rc.d/init.d/crond reload");
}

//取任务构造Day
function GetDay(){
	$hour = I('hour');
	$minute = I('minute');
	$cuonConfig = "$minute $hour * * * ";
	return $cuonConfig;
}

//取任务构造Day-n
function GetDay_N(){
	$hour = I('hour');
	$minute = I('minute');
	$day = I('where1');
	$cuonConfig = "$minute $hour */$day * * ";
	return $cuonConfig;
}

//取任务构造Hour
function GetHour(){
	$minute = I('minute');
	$cuonConfig = "$minute * * * * ";
	return $cuonConfig;
}

//取任务构造Hour-N
function GetHour_N(){
	$minute = I('minute');
	$hour = I('where1');
	$cuonConfig = "$minute */$hour * * * ";
	return $cuonConfig;
}

//取任务构造Minute-N
function Minute_N(){
	$minute = I('where1');
	$cuonConfig = "*/$minute * * * * ";
	return $cuonConfig;
}

//取任务构造week
function Week(){
	$week = I('week');
	$minute = I('minute');
	$hour = I('hour');
	$cuonConfig = "$minute $hour * * $week ";
	return $cuonConfig;
}

//取任务构造Month
function Month(){
	$where1 = I('where1');
	$minute = I('minute');
	$hour = I('hour');
	$cuonConfig = "$minute $hour $where1 * * ";
	return $cuonConfig;
}

//取执行脚本
function GetShell(){
	$type = I('sType');
	if($type == 'toFile'){
		$sfile = I('sFile');
		if($_FILES["sFile"]["error"] > 0)	returnMsg(false, $_FILES["sFile"]["error"]);
		$shell = file_get_contents($_FILES['sFile']['tmp_name']);
		SendSocket("FileAdmin|DelFile|". $_FILES['sFile']['tmp_name']);
	}else{
		$head = "#!/bin/bash\nPATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin\nexport PATH\n";
		
		switch($type){
			case 'site':
				$shell = $head."/www/server/cloud/backup site ".I('sName')." ".I('save');
				break;
			case 'database':
				$shell = $head."/www/server/cloud/backup database ".I('sName')." ".I('save');
				break;
			case 'logs':
				$shell = $head."/www/server/cloud/logsBackup ".I('sName').($_SESSION['server_type']=='nginx'?'.log':'-access_log')." ".I('save');
				break;
			default:
				$shell = $head.$_POST['sBody'];
		}
		
	}
	
	$cronPath = '/www/server/cron';
	if(!file_exists($cronPath)) SendSocket("FileAdmin|AddDir|" . $cronPath);
	file_put_contents('/tmp/read.tmp',$shell);
	$cronName = md5(md5(time().'_bt'.rand(10000, 1000000)));
	$file = $cronPath.'/'.$cronName;
	$result = SendSocket("FileAdmin|SaveFile|". $file);
	if($result['status']){
		SetAccess($file);
		return $cronName;
	}
	returnMsg(false, '写入脚本失败!');
}

//设置指定文件的权限
function SetAccess($sfile){
	SendSocket("FileAdmin|ChownFile|" . $sfile . '|root');
	SendSocket("FileAdmin|ChmodFile|" . $sfile . '|755');
}

//将Shell脚本写到文件
function WriteShell($config){
	$file = '/var/spool/cron/root';
	SendSocket("FileAdmin|AddFile|" . $file);
	SendSocket("FileAdmin|ReadFile|" . $file);
	$conf = file_get_contents('/tmp/read.tmp');
	$conf .= $config."\n";
	if(file_put_contents('/tmp/read.tmp',$conf)){
		SendSocket("FileAdmin|SaveFile|". $file);
		SendSocket("FileAdmin|ChmodFile|" . $file . '|600');
		SendSocket("FileAdmin|ChownFile|" . $file . '|root');
		return true;
	}
	returnMsg(false, '写入配置到计划任务失败!');
}

//接口动作
if(isset($_GET['action'])){
	$_GET['action']();
	exit;
}


$cronData = GetCrontab();


//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/crontab.html';
require './public/footer.html';
?>