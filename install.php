<?php


//----------------------------------
// 安装与设置
//----------------------------------
include_once './Common.php';

//检查是否初始化
function checkInstall()
{
    $status = M('config')->where("id=1")->getField('status');
    if ($status == '1') {
        $_SESSION['status'] = 1;
        header("Location: login.php");
        exit;
    }
}

//安装/初始化
function Install()
{
    //是否允许提交
    checkInstall();
    if ($_SESSION['status'] == 1 && isset($_SESSION['system']) == false) return;

    //设置MySQL管理密码及默认路径
    $root        = I('root');
    $backup_path = I('backup_path');
    $sites_path  = I('sites_path');
    $sql         = M('config');
    $where       = "id=1";
    $data        = [
        'mysql_root'  => $root,
        'backup_path' => $backup_path,
        'sites_path'  => $sites_path,
    ];

    $sql->where($where)->save($data);

    //设置管理用户及密码
    $username  = I('bt_username');
    $password1 = I('bt_password1');
    $password2 = I('bt_password2');
    $email     = $_POST['bt_email'];

    //$username = preg_replace("/\W+/", "", $username);

    if ($username == '' || $password1 == '' || $email == '') {
        header("Content-type: text/html; charset=utf-8");
        exit('邮箱或用户名或密码不能为空!');
    }

    if ($password1 != $password2) {
        header("Content-type: text/html; charset=utf-8");
        exit('两次输入的密码不一致，请重新输入!');
    }

    $data = [
        'username' => $username,
        'password' => md5($password1),
        'email'    => $email,
    ];

    M('user')->where($where)->save($data);

    //设置状态
    $sql->where($where)->setField('status', 1);
    $_SESSION['status'] = 1;
    unset($_SESSION['system']);
    $itype = '';
    if (file_exists('/www/server/type.pl')) $itype = '[RPM]';
    file_get_contents('http://www.bt.cn/Api/SetupCount?type=Linux' . $itype);
}

checkInstall();


//函数调用
if (isset($_GET['action'])) {
    $_GET['action']();
}

//检查是否显示设置界面
if ($_SESSION['status'] != 1 || isset($_SESSION['system']) == true) {
    $config             = M('config')->where('id=1')->find();
    $user               = M('user')->where('id=1')->find();
    $_SESSION['status'] = 0;

    if ($config['mysql_root'] == '') {
        $config['mysql_root'] = trim(file_get_contents('/www/server/mysql/default.pl'));
    }
}


?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title>初始化宝塔软件</title>
		<link href="public/css/install.css" rel="stylesheet">
		<script type="text/javascript" src="public/js/jquery.js"></script>
	</head>
	<body>
		<div class="main">
		<?php if ($_SESSION['status'] != 1) { ?>
          <div class="warp">
			<div class="title">设置宝塔Linux面板</div>
			<form class="form" action="/install.php?action=Install" method="post">
				<fieldset>
					<legend>管理员设置</legend>
					<p><span class="tit">管理员邮箱</span><input type="email" name="bt_email"
                                                            value="<?php echo $user['email']; ?>"/> *用于找回密码</p>
					<p><span class="tit">用户名</span><input type="text" name="bt_username"
                                                          value="<?php echo $user['username']; ?>"/> *请设置管理员名称</p>
					<p><span class="tit">管理密码</span><input type="password" name="bt_password1" value=""/> *请设置管理员密码</p>
					<p><span class="tit">重复密码 </span><input type="password" name="bt_password2"
                                                            value=""/> *再输一次管理员密码</p>
				</fieldset>
				<fieldset>
					<legend>路径设置</legend>
					<p><span class="tit">备份文件路径</span><input type="text" name="backup_path"
                                                             value="<?php echo $config['backup_path']; ?>"/> *站点及数据库打包备份文件的存放路径</p>
					<p><span class="tit">站点创建路径</span><input type="text" name="sites_path"
                                                             value="<?php echo $config['sites_path']; ?>"/> *站点及FTP创建时的默认路径</p>
				</fieldset>
				<fieldset style="display: none;">
					<legend>MySQL</legend>
					<p><span class="tit">root密码</span><input class='text' name='root'
                                                             value='<?php echo $config['mysql_root']; ?>'> MySQL数据库管理密码</p>
				</fieldset>
				<input class="submit-btn" type="submit" value="保存"/>
			</form>
		</div>
        <?php } else { ?>
          <div class="success">
		<p>宝塔Linux面板初始化成功</p>
		<a href="login.php">登陆页面</a>
		</div>
        <?php } ?>
		</div>
		<div class="copyright">Copyright © 2014-2016 <a href="http://www.bt.cn" target="_blank">宝塔</a>|让你更简单的使用服务器(<a
                  href="http://www.bt.cn" target="_blank">www.bt.cn</a>) All Rights Reserved</div>
		<script>
			var main = $(".main");
            $(window).resize(function () {
              var wh = $(window).height();
              main.height(wh);
            }).resize();
		</script>
	</body>
</html>