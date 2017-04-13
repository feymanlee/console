<?php


//----------------------------------
// 防火墙管理
//----------------------------------
require './Common.php';

//设置防火墙状态
function SetFirewallStatus()
{
    $status = I('status', '', 'intval');
    $result = SendSocket("Firewall|status|" . $status);
    ajax_return($result);
}

//添加放行端口
function AddAcceptPort()
{
    $port = I('port', '', 'intval');
    $ps   = I('ps');
    $sql  = M('firewall');
    if ($sql->where("port='$port'")->getCount() > 0) {
        ajax_return(['status' => false, 'msg' => '您要放行的端口已存在，无需重复放行!']);
    }

    $result = SendSocket("Firewall|AddFireWallPort|" . $port . "|TCP|" . $ps);
    if ($result['status'] == 'true') {
        WriteLogs("防火墙管理", '放行端口[' . $port . ']成功!');
        $data = ['port' => $port, 'ps' => $ps, 'addtime' => date('Y-m-d H:i:s')];
        $sql->add($data);
    }
    ajax_return($result);
}

//删除放行端口
function DelAcceptPort()
{
    $port = I('port', '', 'intval');
    $id   = I('id', '', 'intval');

    $result = SendSocket("Firewall|DelFireWallPort|" . $port . '||');
    if ($result['status']) {
        WriteLogs("防火墙管理", '屏蔽端口[' . $port . ']成功!');
        M('firewall')->where("id='$id'")->delete();
    }
    ajax_return($result);
}

//设置远程端口状态
function SetSshStatus()
{
    $version = file_get_contents('/etc/redhat-release');
    if (intval($_GET['status']) == 1) {
        $msg = 'SSH服务已停用';
        $act = 'stop';
    } else {
        $msg = 'SSH服务已启动';
        $act = 'start';
    }

    if (strpos($version, '7.')) {
        $ret = SendSocket("ExecShell|systemctl {$act} sshd.service");
    } else {
        $ret = SendSocket("ExecShell|service sshd " . $act);
    }

    if ($ret['status']) {

        WriteLogs("防火墙管理", $msg);
        ajax_return(['status' => true, 'msg' => $msg]);
    } else {
        ajax_return(['status' => false, 'msg' => '操作失败,未知错误']);
    }
}

//设置ping
function SetPing()
{
    if ($_GET['st'] != 1) {
        $ret     = SendSocket('Firewall|OffPing');
        $in_ping = 'false';
    } else {
        $ret     = SendSocket('Firewall|NoPing');
        $in_ping = 'true';
    }
    if ($ret['status'] == 'true') {
        M('config')->where("id=1")->setField('ping', $in_ping);
        $_SESSION['config']['ping'] = $in_ping;
        WriteLogs("防火墙管理", (($_GET['st'] != 1) ? '设置' : '解除') . "禁Ping成功!");
        ajax_return(true);
    } else {
        WriteLogs("防火墙管理", (($_GET['st'] != 1) ? '设置' : '解除') . "禁Ping失败!");
        ajax_return(false);
    }
}

//改远程端口
function SetSshPort()
{
    $port = trim(I('port'));
    if ($port < 22 || $port > 65535) ajax_return(false);

    $file = '/etc/ssh/sshd_config';
    SendSocket("FileAdmin|ReadFile|" . $file);
    $conf = file_get_contents('/tmp/read.tmp');

    $rep  = "/#*Port\s+([0-9]+)\s*\n/";
    $conf = preg_replace($rep, "Port $port\n", $conf);
    file_put_contents('/tmp/read.tmp', $conf);
    $ret = SendSocket("FileAdmin|SaveFile|" . $file);

    if ($ret['status']) {
        SendSocket("FileAdmin|ChmodFile|" . $file . '|600');
        SendSocket("FileAdmin|ChownFile|" . $file . '|root');
        SendSocket("Firewall|AddFireWallPort|$port|TCP|ssh");
        SendSocket("ExecShell|service sshd restart");
        M('firewall')->where("ps='SSH远程管理服务'")->setField('port', $port);
        WriteLogs("防火墙管理", "改SSH端口为[$port]成功!");
        ajax_return(true);
    } else {
        WriteLogs("防火墙管理", "改SSH端口为[$port]失败!");
        ajax_return(false);
    }
}

//接口动作
if (isset($_GET['action'])) {
    $_GET['action']();
    exit;
}
$ssh = SendSocket('Testing|mstsc');

//包含头部与菜单
require './public/head.html';
require './public/menu.html';
require './public/firewall.html';
require './public/footer.html';
?>