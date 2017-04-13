<?php


//----------------------------------
// 数据库管理
//----------------------------------
require './Common.php';

//添加数据库
function AddDatabase()
{
    $data_name = trim(I('post.name'));
    if ($data_name == 'root' || $data_name == 'mysql' || $data_name == 'test' || strlen($data_name) < 3) {
        ajax_return(['status' => false, 'msg' => '数据库名称不合法,或少于3个字符!']);
    }

    if (strlen($data_name) > 16) returnJson(false, '数据库名不能大于16位');

    $reg = "/^[a-zA-Z]{1}\w+$/";
    if (!preg_match($reg, $data_name)) {
        ajax_return(['status' => false, 'msg' => '数据库名不能带有特殊符号，且首位必需为字母']);
    }
    $data_pwd = I('post.password');
    if (!$_POST['password']) {
        $data_pwd = substr(md5(time()), 0, 8);
    }
    $sql = M('databases');
    if ($sql->where("name='$data_name'")->getCount()) returnJson(false, '数据库已存在!');
    $address  = trim($_POST['address']);
    $user     = '是';
    $username = $data_name;
    $password = RCODE($data_pwd, 'ENCODE');

    $result = SqlExec("create database $data_name");
    IsSqlError($result);
    SqlExec("drop user '$username'@'localhost'");
    SqlExec("drop user '$username'@'$address'");
    SqlExec("grant all privileges on " . $data_name . ".* to '$username'@'localhost' identified by '$data_pwd'");
    SqlExec("grant all privileges on " . $data_name . ".* to '$username'@'$address' identified by '$data_pwd'");
    SqlExec("flush privileges");

    $data = [
        'name'     => $data_name,
        'username' => $username,
        'password' => $password,
        'accept'   => $address,
        'ps'       => ($_POST['bak'] == '') ? '填写备注' : $_POST['bak']];
    $sql->add($data);
    WriteLogs("数据库管理", "添加数据库[$data_name]成功!");
    ajax_return(['status' => true, 'msg' => '添加成功']);
}

//检测数据库执行错误
function IsSqlError($str)
{
    if (strstr($str, "using password:")) returnJson(false, '数据库管理密码错误!');
    if (strstr($str, "Connection refused")) returnJson(false, '数据库连接失败,请检查数据库服务是否启动!');
    if (strstr($str, "1133")) returnJson(false, '数据库用户不存在!');
}

//删除数据库
function DeleteDatabase()
{
    $id   = $_GET['id'];
    $name = I('name');
    if ($name == 'bt_default') returnJson(false, '不能删除宝塔默认数据库!');
    $accept = M('databases')->where("id='$id'")->getField('accept');
    $result = SqlExec("drop database $name");
    IsSqlError($result);
    SqlExec("drop user '$name'@'localhost'");
    SqlExec("drop user '$name'@'$accept'");
    SqlExec("flush privileges");
    M('databases')->where("id='$id'")->delete();
    WriteLogs("数据库管理", "删除数据库[$name]成功!");
    returnJson(true, '删除数据库成功!');
}

//设置ROOT密码
function SetupPassword()
{
    $password = trim(I('password'));
    $rep      = "/^[\w#@%]+$/";
    if (!preg_match($rep, $password)) returnJson(false, '密码中请不要带有特殊字符!');
    $sql        = M('config');
    $mysql_root = $sql->where("id=1")->getField('mysql_root');
    $sql->where("id=1")->setField('mysql_root', $password);
    $result = SqlQuery("show databases");
    $msg    = '设置成功!';
    if (!is_array($result)) {
        $sql->where("id=1")->setField('mysql_root', $mysql_root);

        if (strpos(@file_get_contents('/www/server/mysql/version.pl'), '5.7') !== false) {
            $result = SqlExec("update mysql.user set authentication_string=password('$password') where User='root'");
        } else {
            $result = SqlExec("update mysql.user set Password=password('$password') where User='root'");
        }
        if (strpos($result, 'using password')) {
            WriteLogs("数据库管理", "设置root密码失败,密码校验失败!");
            ajax_return(['status' => false, 'msg' => 'root密码不正确，请重新输入!']);
        }
        SqlExec("flush privileges");
        $msg = 'ROOT密码修改成功!';
    }
    M('config')->where("id=1")->setField('mysql_root', $password);
    WriteLogs("数据库管理", "设置root密码成功!");
    session('mysql_root', $password);
    ajax_return(['status' => true, 'msg' => $msg]);
}

//修改用户密码
function ResDatabasePassword()
{
    $newpassword = I('post.password');
    $username    = I('post.username');
    if ($username == 'bt_default') returnJson(false, '不能修改宝塔数据库的密码!');
    $id = I('post.id');
    if (strpos(@file_get_contents('/www/server/mysql/version.pl'), '5.7') !== false) {
        $result = SqlExec("update mysql.user set authentication_string=password('$newpassword') where User='$username'");
    } else {
        $result = SqlExec("update mysql.user set Password=password('$newpassword') where User='$username'");
    }

    IsSqlError($result);
    if (!$result) returnJson(false, '修改失败,数据库用户不存在!');
    if (intval($id) > 0) {
        M('databases')->where("id='$id'")->setField('password', RCODE($newpassword, 'ENCODE'));
    } else {
        M('config')->where("id=1")->setField('mysql_root', $newpassword);
        session('mysql_root', $newpassword);
    }
    SqlExec("flush privileges");
    WriteLogs("数据库管理", "修改数据库用户[$username]密码成功!");
    returnJson(true, '修改数据库[' . $username . ']密码成功!');
}

function setMyCnf($action = true)
{
    $myFile    = '/etc/my.cnf';
    $mycnf     = file_get_contents($myFile);
    $root      = $_SESSION['mysql_root'];
    $pwdConfig = "\n[mysqldump]\nuser=root\npassword=$root";
    $rep       = "/\n\[mysqldump\]\nuser=root\npassword=.+/";
    if ($action) {
        if (strpos($mycnf, $pwdConfig) !== false) return;
        if (strpos($mycnf, "\n[mysqldump]\nuser=root\n") !== false) {
            $mycnf = preg_replace($rep, $pwdConfig, $mycnf);
        } else {
            $mycnf .= "\n[mysqldump]\nuser=root\npassword=$root";
        }
    } else {
        $mycnf = preg_replace($rep, '', $mycnf);
    }

    file_put_contents('/tmp/read.tmp', $mycnf);
    SendSocket("FileAdmin|SaveFile|" . $myFile);
    SendSocket("FileAdmin|ChownFile|$myFile|root");
    SendSocket("FileAdmin|ChmodFile|$myFile|644");
}

//备份
function ToBackup()
{
    $id    = I('id');
    $where = "id=$id";
    $name  = M('databases')->where($where)->getField('name');
    $root  = $_SESSION['mysql_root'];
    setMyCnf(true);
    $fileName   = $name . '_' . date('Ymd_His') . '.sql';
    $backupName = $_SESSION['config']['backup_path'] . '/database/' . $fileName;
    if (!file_exists($_SESSION['config']['backup_path'] . '/database')) SendSocket("FileAdmin|AddDir|" . $data['backup_path'] . '/database');
    $exec    = "MySQL|backup|$name|$backupName|$root";
    $result  = SendSocket($exec);
    $tarName = str_replace('.sql', '.tar.gz', $fileName);
    SendSocket("ExecShell|tar -zcvf $tarName $fileName && rm -f $fileName|" . $_SESSION['config']['backup_path'] . '/database');
    $backupName = str_replace('.sql', '.tar.gz', $backupName);

    setMyCnf(false);
    if ($result['status']) {
        $sql  = M('backup');
        $data = [
            'type'     => 1,
            'name'     => $tarName,
            'pid'      => $id,
            'filename' => $backupName,
            'size'     => 0,
            'addtime'  => date('Y-m-d H:i:s')];
        $sql->add($data);
        WriteLogs("数据库管理", "备份数据库[$name]成功!");
        returnJson(true, '备份成功!');
    }
    WriteLogs("数据库管理", "备份数据库[$name]失败!");
    returnJson(false, '备份失败!');
}

//删除备份文件
function DelBackup()
{
    $id       = I('id');
    $where    = "id=$id";
    $filename = M('backup')->where($where)->getField('filename');
    $result   = SendSocket("FileAdmin|DelFile|$filename");
    if ($result['status'] == true || empty($result['msg']) == true) {
        M('backup')->where($where)->delete();
        WriteLogs("数据库管理", "删除备份文件[$filename]成功!");
    }
    $result['msg'] = isset($result['msg']) ? $result['msg'] : '文件不存在!';
    returnSocket($result);
}

//导入
function InputSql()
{
    $name = I('name');
    $file = I('file');
    $root = $_SESSION['mysql_root'];
    $tmp  = explode('.', $file);
    $exts = ['sql', 'gz', 'zip'];
    $ext  = $tmp[count($tmp) - 1];
    if (!in_array($ext, $exts)) returnJson(false, '请选择sql、gz、zip文件格式!');

    if ($ext != 'sql') {
        $tmp        = explode('/', $file);
        $tmpFile    = $tmp[count($tmp) - 1];
        $tmpFile    = str_replace('.sql.' . $ext, '.sql', $tmpFile);
        $tmpFile    = str_replace('.' . $ext, '.sql', $tmpFile);
        $tmpFile    = str_replace('tar.', '', $tmpFile);
        $backupPath = $_SESSION['config']['backup_path'] . '/database';

        if ($ext == 'zip') {
            SendSocket("ExecShell|unzip $file|" . $backupPath);
        } else {
            SendSocket("ExecShell|tar zxf $file|" . $backupPath);
        }

        if (!file_exists($backupPath . '/' . $tmpFile) || $tmpFile == '') returnJson(false, '文件[' . $tmpFile . ']不存在!');
        $exec   = "ExecShell|/www/server/mysql/bin/mysql -uroot -p$root $name < $tmpFile && rm -f $tmpFile|" . $backupPath;
        $result = SendSocket($exec);
    } else {
        $result = SendSocket("ExecShell|/www/server/mysql/bin/mysql -uroot -p$root $name < $file");
    }

    if ($result['status']) WriteLogs("数据库管理", "导入数据库[$name]成功!");
    returnSocket($result);
}

//同步数据库到服务器
function SyncToDatabases()
{
    $type = intval($_GET['type']);
    $n    = 0;
    $sql  = M('databases');
    if ($type == 0) {
        $data = $sql->field('id,name,username,password,accept')->select();
        foreach ($data as $value) {
            $result = ToDataBase($value);
            if ($result == 1) $n++;
        }
    } else {
        $data = $_POST;
        foreach ($data as $value) {
            $find   = $sql->where("id=$value")->field('id,name,username,password,accept')->find();
            $result = ToDataBase($find);
            if ($result == 1) $n++;
        }
    }

    returnJson(true, "本次共同步了{$n}数据库");
}

//添加到服务器
function ToDataBase($find)
{
    if ($find['username'] == 'bt_default') return 0;
    if (strlen($find['password']) < 3) {
        $find['username'] = $find['name'];
        $find['password'] = substr(md5(time() . $find['name']), 0, 10);
        M('databases')->where("id=" . $find['id'])->save(['password' => $find['password'], 'username' => $find['username']]);
    }
    $result = SqlExec("create database " . $find['name']);
    if (substr($result, "using password:")) return -1;
    if (substr($result, "Connection refused")) return -1;
    SqlExec("drop user '" . $find['username'] . "'@'localhost'");
    SqlExec("drop user '" . $find['username'] . "'@'" . $find['accept'] . "'");
    $password = $find['password'];
    if (isset($find['password']) && strlen($find['password']) > 20) {
        $password = RCode($find['password'], 'DECODE');
    }
    SqlExec("grant all privileges on " . $find['name'] . ".* to '" . $find['username'] . "'@'localhost' identified by '$password'");
    SqlExec("grant all privileges on " . $find['name'] . ".* to '" . $find['username'] . "'@'" . $find['accept'] . "' identified by '$password'");
    SqlExec("flush privileges");

    return 1;
}

//从服务器获取数据库
function SyncGetDatabases()
{
    $data    = SqlQuery("show databases");
    $users   = SqlQuery("select User,Host from mysql.user where User!='root' AND Host!='localhost' AND Host!=''");
    $sql     = M('databases');
    $nameArr = ['information_schema', 'performance_schema', 'mysql'];
    $n       = 0;
    foreach ($data as $value) {
        if (in_array($value['Database'], $nameArr)) continue;
        if ($sql->where("name='" . $value['Database'] . "'")->getCount()) continue;
        $host = '127.0.0.1';
        foreach ($users as $user) {
            if ($value == $user['User']) {
                $host = $user['Host'];
                break;
            }
        }
        switch ($value['Database']) {
            case 'bt_default':
                $ps = '管理助手Linux版数据库';
                break;
            case 'test':
                $ps = '测试数据库';
                break;
            default:
                $ps = '填写备注';
                break;
        }
        $data = [
            'name'     => $value['Database'],
            'username' => $value['Database'],
            'password' => '',
            'accept'   => $host,
            'ps'       => $ps];
        if ($sql->add($data)) $n++;
    }

    returnJson(true, '本次共从服务器获取' . $n . '个数据库!');
}

//获取数据库权限
function GetDatabaseAccess()
{
    $name  = I('name');
    $users = SqlQuery("select User,Host from mysql.user where User='$name' AND Host!='localhost'");
    ajax_return($users[0]);
}

//设置数据库权限
function SetDatabaseAccess()
{
    $name   = I('name');
    $access = I('access');
    if ($access != '%' && filter_var($access, FILTER_VALIDATE_IP) == false) returnJson(false, '权限格式不合法');
    $password = M('databases')->where("name='$name'")->getField('password');
    SqlExec("delete from mysql.user where User='$name' AND Host!='localhost'");
    SqlExec("grant all privileges on " . $name . ".* to '$name'@'$access' identified by '$password'");
    SqlExec("flush privileges");
    returnJson(true, '设置成功!');
}

//接口动作
if (isset($_GET['action'])) {
    $_GET['action']();
    exit;
}

//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/database.html';
require './public/footer.html';
?>

