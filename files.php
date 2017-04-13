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
// 文件管理
//----------------------------------
require './Common.php';

//上传文件
function UploadFile(){
	set_time_limit(0);
	if($_FILES["zunfile"]["error"] > 0)	returnJson(false, $_FILES["file"]["error"]);
	$dirName = $_GET['path'];
	if(!file_exists($dirName)) SendSocket("FileAdmin|AddDir|".$dirName);
	$fileName = $dirName.$_FILES['zunfile']['name'];
	$result = SendSocket("FileAdmin|MvDirOrFile|".$_FILES['zunfile']['tmp_name'].'|'.$fileName);
	if($result['status']) WriteLogs("文件管理", "上传文件[$fileName]成功!");
	set_time_limit(300);
	returnSocket($result);
}

//创建文件
function CreateFile() {
	$file =  $_POST['file'];
	$result = SendSocket("FileAdmin|AddFile|" . $file);
	if($result['status']) WriteLogs("文件管理", "创建文件[$file]成功!");
	ajax_return($result);
}

//创建目录
function CreateDir() {
	$dir = $_POST['dir'];
	$result = SendSocket("FileAdmin|AddDir|" . $dir);
	if($result['status']) WriteLogs("文件管理", "创建目录[$dir]成功!");
	ajax_return($result);
}

//删除目录
function DeleteDir() {
	$dir = $_POST['dir'];
	if(checkDir($dir))returnJson(false,'请不要花样作死!');
	$result = SendSocket("FileAdmin|DelDir|" . $dir);
	if($result['status']) WriteLogs("文件管理", "删除目录[$dir]成功!");
	ajax_return($result);
}

//删除文件
function DeleteFile() {
	$file = I('file');
	if(strpos($file,'.user.ini')) SendSocket("ExecShell|chattr -i ".$file);
	$result = SendSocket("FileAdmin|DelFile|" . $file);
	if($result['status']) WriteLogs("文件管理", "删除文件[$file]成功!");
	ajax_return($result);
}

//复制文件
function CopyFile() {
	$sfile = I('sfile');
	$dfile = I('dfile');
	$result = SendSocket("ExecShell|\\cp -r -a $sfile $dfile");
	if(strpos($dfile,$_SESSION['config']['sites_path']) !== false){
		SendSocket("FileAdmin|ChownFile|" . $dfile . '|www');
	}
	if($result['status']) WriteLogs("文件管理", "复制文件[$sfile]至[$dfile]成功!");
	ajax_return($result);
}

//移动文件或目录
function MvFile() {
	$sfile = I('sfile');
	$dfile = I('dfile');
	CheckDirMV($dfile);
	$result = SendSocket("FileAdmin|MvDirOrFile|" . $sfile . '|' . $dfile);
	if($result['status']) WriteLogs("文件管理", "移动文件[$sfile]至[$dfile]成功!");
	ajax_return($result);
}

//检测可移动目录
function CheckDirMV($dfile){
	if(file_exists($dfile)){
		if(is_dir($dfile)){
			$mdir = dir($dfile);
			$n=0;
			while($mfile = $mdir->read()){
				$n++;
			}
			if($n <= 2){
				SendSocket("FileAdmin|DelDir|". $dfile);
			}else{
				returnJson(false, '目标目录已存在!');
			}
		}
	}
}

//获取文件内容
function GetFileBody() {
	$file = I('file');
    $result['status'] = false;
	$body = file_get_contents($file);
    if($body !== false){
         $result['status'] = true;
    }
    $result['data'] = $body;
    $tmp =  json_decode(json_encode($result),1);
	if($tmp['data'] == null){
		$tmp['data'] = iconv('GB2312','UTF-8',$body); 
	} 
       ajax_return($tmp);
}

//保存文件
function SaveFileBody() {
	$file = I('file');
	if($_POST['data'] == '')$_POST['data'] = ' ';
	$data = str_replace('(__bt@cn__)','+',urldecode($_POST['data']));
	if(version_compare(PHP_VERSION,'5.4.0','<')) $data = str_replace("\\'","'",str_replace("\\\\","\\", str_replace("\\\"","\"",$data)));
	if (file_put_contents('/tmp/read.tmp', $data)) {
		$isConf = strpos($file,($_SESSION['server_type'] == 'nginx'?'nginx':'apache').'/conf');
		SendSocket('ExecShell|\\cp -a '.$file.' /tmp/backup.conf');
		$result = SendSocket("FileAdmin|SaveFile|" . $file);
		if($isConf) {
			
			if($_SESSION['server_type'] == 'nginx'){
				$isError = checkNginxConf();
			}else{
				$isError = checkHttpdConf();
			}
			
			if($isError !== true){
				SendSocket('ExecShell|\\cp -a /tmp/backup.conf '.$file);
				SendSocket("FileAdmin|ChmodFile|" . $file . '|root');
				returnJson(false,'配置文件错误: <br><a style="color:red;">'.str_replace("\n",'<br>',$isError).'</a>');
			}
		}
		
		if($result['status']) WriteLogs("文件管理", "编辑文件[$file]保存成功!");
		ajax_return($result);
	}
	ajax_return(array('status' => false, 'msg' => '转存文件失败!'));
}
//文件压缩
function Zip() {
	$sfile = I('sfile');
	$dfile = I('dfile');
	$type = trim(I('type'));
	if(checkDir($sfile)) returnJson(false, '不能压缩系统关键目录!');
	if(!isset($type)) returnJson(false, '参数不正确!');
	$result = SendSocket("FileAdmin|ZipFile|" . $sfile . '|' . $dfile . '|' . $type);
	if($result['status']) WriteLogs("文件管理", "压缩目录[$sfile]至[$dfile]成功!");
	ajax_return($result);
}

//文件解压
function UnZip() {
	$sfile = I('sfile');
	$dfile = I('dfile');
	$type = trim(I('type'));
	if(!isset($type)) returnJson(false, '参数不正确');
	$result = SendSocket("FileAdmin|UnZipFile|" . $sfile . '|' . $dfile . '|' . $type);
	if($result['status']) WriteLogs("文件管理", "解压文件[$sfile]至[$dfile]成功!");
	ajax_return($result);
}

//获取文件/目录 权限信息
function GetFileAccess(){
	$fileName = I('filename');
	//取所有者
	$own = posix_getpwuid(fileowner($fileName));
	$fileInfo['chown'] = $own['name'];
	
	//取权限
	$perms = fileperms($fileName);
	$fileInfo['chmod'] = '';
	
	$num = 0;
	$num += ($perms & 0x0100)?4:0;
	$num += ($perms & 0x0080)?2:0;
	$num += ($perms & 0x0040)?1:0;
	$fileInfo['chmod'] .= $num;
	$num = 0;
	$num += ($perms & 0x0020)?4:0;
	$num += ($perms & 0x0010)?2:0;
	$num += ($perms & 0x0008)?1:0;
	$fileInfo['chmod'] .= $num;
	$num = 0;
	$num += ($perms & 0x0004)?4:0;
	$num += ($perms & 0x0002)?2:0;
	$num += ($perms & 0x0001)?1:0;
	$fileInfo['chmod'] .= $num;
	if($fileInfo['chmod'] == '000'){
		$fileInfo['chmod'] = '755';
		$fileInfo['chown'] = 'www';
	} 	
	ajax_return($fileInfo);
}

//设置文件权限和所有者
function SetFileAccess(){
	$file = I('filename');
	$user = I('user');
	$access = I('access');
	$result = SendSocket("FileAdmin|ChownFile|" . $file . '|' . $user);
	$result = SendSocket("FileAdmin|ChmodFile|" . $file . '|' . $access);
	WriteLogs("文件管理", "设置文件[".$file."]权限成功!");
	ajax_return($result);
}

//输出文件
function GetFileBytes(){
	$file = I('file');
	if(!file_exists($file)) exit('错误: 指定文件不存在!');
	$fileInfo = pathinfo($file);
	$myFile = fopen($file, 'rb');
	$size = filesize($file);
	$type = isImageFile($fileInfo['extension']);
	
	//输出文件头
	set_time_limit(0);
	header("Content-Disposition: attachment; filename=" . iconv('utf-8','gb2312',$fileInfo['basename']));
	header("Accept-Ranges: bytes");
	header("Content-Length: ".$size);
	if($type != false){
		ob_clean();
		header('Content-type: '.$type);
	}else{
		//是否使用浏览器缓存
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
		  header("HTTP/1.1 304 Not Modified");
		  exit;
		}
		//设置浏览器缓存
		$sTime = 60*60;
		header("Cache-Control: private, max-age=".$sTime.", pre-check=".$sTime);
		header("Pragma: private");
		header('Expires:'. preg_replace('/.{5}$/', 'GMT', gmdate('r', time()+ $sTime)));
		header('Last-Modified: ' . preg_replace('/.{5}$/', 'GMT', gmdate('r', $sTime)));
		//输出文件头
		header('Content-type: application/octet-stream');
	}
	
	$n = 4096;
	while($eopt = fread($myFile, $n)){
		echo $eopt;
		$n += 4096;
	}
	fclose($myFile);
	set_time_limit(300);
	
}

//是否图片
function isImageFile($ext){
	$exts = array('jpg','jpeg','png','bmp','gif','tiff');
	foreach($exts as $value){
		if($value == $ext){
			return 'image/'.$ext;
		}
	}
	return false;
}

//校验关键目录
function checkDir($path){
	$dirs = array('/','/*', '/www', '/root', '/boot', '/bin', '/etc', '/home', '/dev', '/sbin', '/var', '/usr', '/tmp', '/sys',
	 '/proc', '/media', '/mnt', '/opt', '/lib', '/srv', 'selinux','/www/server','/www/wwwroot','/www/backup','/www/wwwroot/default');
	return in_array($path, $dirs);
}

//下载文件
function DownloadFile(){
	$url = urldecode($_POST['url']);
	$fileName = I('path');
	$result = SendSocket("DownloadFile|$url|$fileName");
	ajax_return($result);
}

//执行命令行
function ExecShell(){
	$exec = trim(I('exec'));
	$path = trim(I('path'));
	$zs = array('rm -rf /','rm -rf /*','rm -rf /boot','rm -rf /usr','rm -rf /var','rm -rf /www');
	if(in_array($exec,$zs)) returnJson(false,'请不要花样作死!');
	$result = SendSocket("ExecShell|$exec|$path");
	ajax_return($result);
}

//取SHELL输出
function GetShellEcho(){
	$tmp = '/tmp/shell_temp.pl';
	if(!file_exists($tmp))ajax_return('error');
	$str = file_get_contents($tmp);
	ajax_return($str);
}

//批量操作
function SetBatchData(){
	switch(intval($_POST['type'])){
		case 1:
			$_SESSION['selected'] = $_POST;
			$msg = '标记成功,请在目标目录点击粘贴所有按钮!';
			break;
		case 2:
			$_SESSION['selected'] = $_POST;
			$msg = '标记成功,请在目标目录点击粘贴所有按钮!';
			break;
		case 3:
			$user = $_POST['user'];
			$access = $_POST['access'];
			foreach($_POST['data'] as $value){
				$file = $_POST['path'].'/'.$value;
				SendSocket("FileAdmin|ChownFile|" . $file . '|' . $user);
				SendSocket("FileAdmin|ChmodFile|" . $file . '|' . $access);
			}
			WriteLogs("文件管理", "从[".$_POST['path']."]批量设置权限成功!");
			$msg = '设置成功';
		break;
		case 4:
			foreach($_POST['data'] as $value){
				$file = $_POST['path'].'/'.$value;
				if(is_dir($file)){
					if(checkDir($file)) continue;
					SendSocket("FileAdmin|DelDir|".$file);
				}else{
					SendSocket("FileAdmin|DelFile|".$file);
				}
			}
			WriteLogs("文件管理", "从[".$_POST['path']."]批量删除文件成功!");
			$msg = '删除成功';
		break;
	}
	returnJson(true,$msg);
}

//批量粘贴
function BatchPaste(){
	$i = 0;
	switch(intval($_POST['type'])){
		case 1:
			foreach($_SESSION['selected']['data'] as $value){
				$i++;
				$sfile = $_SESSION['selected']['path'].'/'.$value;
				$dfile = $_POST['path'].'/'.$value;
				SendSocket("ExecShell|\\cp -r -a $sfile $dfile");
				if(strpos($dfile,$_SESSION['config']['sites_path']) !== false){
					SendSocket("FileAdmin|ChownFile|" . $dfile . '|www');
				}
			}
			$errorCount = count($_SESSION['selected']['data']) - $i;
			WriteLogs("文件管理", "从[$sfile]批量复制到[$dfile]成功!");
			break;
		case 2:
			foreach($_SESSION['selected']['data'] as $value){
				$sfile = $_SESSION['selected']['path'].'/'.$value;
				$dfile = $_POST['path'].'/'.$value;
				CheckDirMV($dfile);
                SendSocket("FileAdmin|MvDirOrFile|" . $sfile . '|' . $dfile);
				$i++;
			}
			$errorCount = count($_SESSION['selected']['data']) - $i;
			WriteLogs("文件管理", "从[$sfile]批量移动到[$dfile]成功![".$i.'],失败['.(count($_SESSION['selected']['data']) - $i).']');
            break;
	}
	unset($_SESSION['selected']);
	returnJson(true,'批量操作成功['.$i.'],失败['.$errorCount.']');
}


//接口动作
if (isset($_GET['action'])) {
	$key = array('file', 'dir', 'sfile', 'dfile', 'path', 'user', 'type');
	foreach ($key as $k) {
		if (isset($_POST[$k])) {
			$_POST[$k] = trim(str_replace('//', '/', $_POST[$k]));
		}
	}

	$_GET['action']();
	exit ;
}

//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/files.html';
require './public/footer.html';
?>

