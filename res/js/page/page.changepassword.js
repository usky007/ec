/**
 * 
 */
Page.onPageLoad.add(function(){
	$('.button.label-indent').click(function(){
		var password = $('#User_Password').val();
		var repass = $('#User_ConfirmPassword').val();
		var olepass = $('#User_OldPassword').val();
		if(olepass == '')
		{
			$('#User_OldPassword_em_').css('display','block');
			$('#User_OldPassword_em_').text('旧密码不能为空！');
			return false;
		}
		if(password == '')
		{
			$('#User_Password_em_').css('display','block');
			$('#User_Password_em_').text('密码不为空！');
			return false;
		}
		if(password.length < 6)
		{
			$('#User_Password_em_').css('display','block');
			$('#User_Password_em_').text('密码不能太短！');
			return false;
		}
		if(password != repass)
		{
			$('#User_ConfirmPassword_em_').css('display','block');
			$('#User_ConfirmPassword_em_').text('两次密码输入的不一致!');
			return false;
		}
	})
	
	
})