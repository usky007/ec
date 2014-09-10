 
	<form method="POST" action="/admin/login/dologin">
	<?php echo isset($data['alertstr'])?$data['alertstr']:"" ;?><br>
 	username：<input type="text" name="username" value="admin" size="60"/><br>
 	password：<input type="password" name="password" size="60"/><br>
	<input type="submit" value="登录">
	</form>
 