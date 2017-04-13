<?php


//----------------------------------
// 修改FTP密码
//----------------------------------
include_once './model/Public.model.php';

//提交修改
function SetFtpPassword()
{
    $username  = I('username');
    $password  = I('password');
    $password1 = I('password1');
    $password2 = I('password2');
    if ($username == '') returnJson(false, "FTP帐户不能为空!");
    if ($password1 != $password2) returnJson(false, "两次输入的密码不一致，请重新输入!");
    session_start();
    if ($_SESSION['vcode'] != md5(I('code'))) returnJson(false, "图形验证码不正确，请重新输入!");

    $sql         = M('ftps');
    $where       = "name='$username'";
    $oldPassword = $sql->where($where)->getField('password');
    if (!$oldPassword) returnJson(false, "FTP帐衣不存在，请重新输入!");
    $mPassword = RCode($password, 'ENCODE');
    if ($oldPassword != $password && $oldPassword != $mPassword) returnJson(false, "旧密码不正确，请重新输入!");
    $ret = SendSocket('FTP|password|' . $username . '|' . $password1);
    if ($ret['status'] == 'true') {
        $sql->where($where)->setField('password', RCode($password1, 'ENCODE'));
        returnJson(true, '修改成功!');
    }
    returnJson(true, '修改失败!');
}


//接口动作
if (isset($_GET['action'])) {
    $_GET['action']();
    exit;
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>宝塔服务器管理助手-自助修改密码</title>
<link href="public/css/reg.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="public/js/jquery.js"></script>
<script type="text/javascript" src="public/js/Validform_v5.3.2_min.js"></script>
<script type="text/javascript" src="public/layer/layer.js"></script>
<style>
.main .reg {
  height: 352px;
}
</style>
</head>

<body>
<div class="main">
	<form class="registerform" id="PwdForm" method="post" onsubmit="return false;">
	<div class="reg">
		<div class="title">修改FTP密码</div>
		<div class="line"><span>FTP账号</span><input class="inputtxt" type="text" name="username" value=""
                                                   datatype="*1-24" nullmsg="请填写FTP帐号" errormsg="格式不对"
                                                   placeholder="FTP账号"><div class="Validform_checktip"></div></div>
		<div class="line"><span>旧密码</span><input class="inputtxt" type="password" name="password" value=""
                                                 datatype="*6-16" nullmsg="请填写旧密码" errormsg="3~16位之间" placeholder="旧密码"><div
                  class="Validform_checktip"></div></div>
		<div class="line"><span>新密码</span><input class="inputtxt" type="password" name="password1" value=""
                                                 datatype="*6-16" nullmsg="请设置密码" errormsg="6~16位之间" placeholder="新密码"><div
                  class="Validform_checktip"></div></div>
		<div class="line"><span>重复新密码</span><input class="inputtxt" type="password" name="password2" value=""
                                                   datatype="*6-16" nullmsg="请设置密码" errormsg="6~16位之间"
                                                   placeholder="重复新密码"><div class="Validform_checktip"></div></div>
		<div class="line yzm"><span>图形验证码</span><input type="text" maxlength="5" value="" name="code" id="piccode"
                                                       class="inputtxt code" datatype="*" nullmsg="请输入图形验证码"
                                                       errormsg="请输入图形验证码" placeholder="图形验证码"><div
                  class="picgetcode"><img id="codeImgs" style="width:100%;height:100%" src="GetCodeImage.php?"
                                          onclick="this.src=this.src.split('?')[0]+'?'+Math.random();"></div><div
                  class="Validform_checktip" id="piccodetips"></div></div>
		<div class="reg_btn"><input id="SetPassword" type="submit" value="确认修改"></div>
	</div>
	</form>
</div>
<div class="copyright">Copyright © 2014-2016 <a href="http://www.bt.cn" target="_blank">宝塔</a>|让你更简单的使用服务器(<a
          href="http://www.bt.cn" target="_blank">www.bt.cn</a>) All Rights Reserved</div>
<script>
$(function () {
  $(".registerform").Validform({
    tiptype: 3
  });
  var main = $(".main");
  $(window).resize(function () {
    var wh = $(window).height();
    main.height(wh);
  }).resize();
});

$("#PwdForm").submit(function () {
  var data = $("#PwdForm").serialize();
  $.post('reset.php?action=SetFtpPassword', data, function (rdata) {
    layer.msg(rdata.msg, {icon: rdata.status ? 1 : 5});
    if (rdata.status) $("#PwdForm")[0].reset();
    $("#codeImgs").click();
  });
});
</script>
</body>
</html>