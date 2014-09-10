
<!-- session alert -->
<script>
<?php
	$session = Session::instance();
	$success_msg = $session->get_once('backend_success_msg',null);
	$error_msg = $session->get_once('backend_error_msg',null);

	if(!is_null($success_msg))
	{
		echo "alert('".$success_msg."');";
	}

	if(!is_null($error_msg))
	{
		echo "alert('".$error_msg."');";
	}
?>
	</script>
<div class="main">
<?php echo isset($content) ? $content : '';?>
<?php echo isset($pagenation)?$pagenation:"";?>
</div>