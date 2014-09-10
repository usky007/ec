<?php if(isset($errorinfo)) {?>
<style>
<!--
div.main {float: none;}
-->
</style>
<div class="tip_gray" style="width: 59%; margin: 50px auto;padding-top:3px;">
	<h2 style="font-size:14px;">信息提示</h2>
	<p style="font-size:14px;padding:2em 1em;margin:0px" id="logininfo">
		<?php echo $errorinfo,'，返回<a href="'.$url.'">信息同步</a>界面'?>
	</p>
	<p class="op" style="text-align:right;font-size:12px;padding:2em 1em;margin:0px">
		<a href="<?php echo url::site('main')?>">返回首页</a>
	</p>
</div>
<?php } else {?>
<div class="page_label_min">绑定您的迷世界账号 <a href="<?php echo url::site("login")?>">以后再绑定</a>
</div>
<hr>
<div class="form_box">
	<form method="post" action="<?php echo url::site("ajax/social/bind/$provider")?>" id="bindForm">
	<table class="form">
	<tr>
		<td class="label gray" style="width: auto; line-height: 25px; height: 60px; vertical-align: top;" colspan="2">
			<span class="blue"><?php echo isset($ms['name']) ? $ms['name'].'，' : ''?></span>欢迎来到迷世界<br>
<?php if(isset($email) && !empty($email)) {
	echo "您已经用这个邮箱注册过迷世界了。现在只要输入对应的密码，就可以完成账号绑定了！";
} else {
	echo "您已经注册过迷世界？请输入账号和密码进行绑定。";
} ?>
		</td>
	</tr>
	<tr>
        <td class="label">账号:</td>
        <td><input value="<?php echo isset($email) && !empty($email) ? $email : '' ?>" class="text" type="text" name="email" autocomplete="off"></td>
    </tr>
    <tr>
        <td class="label">密码:</td>
        <td><input value="" class="text" type="password" name="password"> <a href="#">忘记密码</a></td>
    </tr>
    <tr>
		<td class="label"></td>
		<td>
            <input class="btn_blue_80" value="绑定账号" type="submit">
            <input type="hidden" name="invite_uid" value="<?php echo $invite_uid;?>"/>
            <input type="hidden" name="app" value="<?php echo $app;?>"/>
            <input type="hidden" name="code" value="<?php echo $code;?>"/>
		</td>
	</tr>
	<tr>
		<td class="label"></td>
		<td><div id="bindinfo"></div></td>
	</tr>
	</table>
	</form>
</div>
<?php } ?>