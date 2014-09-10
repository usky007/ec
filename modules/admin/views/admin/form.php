<style type="text/css">
/* CSS Document */



a {
color: #c75f3e;
}

table {
width: 90%;
padding: 0;
margin: 0;
}

caption {
padding: 0 0 5px 0;
width: 700px;
font: italic 11px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
text-align: right;
}

th {
font: bold 11px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
color: #4f6b72;
border-right: 1px solid #C1DAD7;
border-bottom: 1px solid #C1DAD7;
border-top: 1px solid #C1DAD7;
letter-spacing: 2px;
text-transform: uppercase;
text-align: left;
padding: 6px 6px 6px 12px;
background: #CAE8EA no-repeat;
}
/*power by www.winshell.cn*/
th.nobg {
border-top: 0;
border-left: 0;
border-right: 1px solid #C1DAD7;
background: none;
}

td {
border-right: 1px solid #C1DAD7;
border-bottom: 1px solid #C1DAD7;
background: none repeat scroll 0 0 #E5E5E5;
font-size:11px;
padding: 2px 4px 4px 2px;
color: #4f6b72;
}
/*power by www.winshell.cn*/

td.alt {
background: none repeat scroll 0 0 #D3DCE3;
    color: #000000;
    font-weight: bold;
}

th.spec {
border-left: 1px solid #C1DAD7;
border-top: 0;
background: #fff no-repeat;
font: bold 10px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}

th.specalt {
border-left: 1px solid #C1DAD7;
border-top: 0;
background: #f5fafa no-repeat;
font: bold 10px "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
color: #797268;
}
/*---------for IE 5.x bug*/
html>body td{ font-size:11px;}
body,td,th {
font-family: 宋体, Arial;
font-size: 12px;
}
</style>
<p></p>

<form action="<?php echo $form['action']?>" <?php echo isset($form['enctype'])?'enctype="'.$form['enctype'].'"':""; ?> method="<?php echo $form['method']?>" name="<?php echo $form['name']?>" id="<?php echo $form['id']?>">
<table id="table1" class="data ajax"  >


<tbody>

<?php foreach($dt_fields as $field){?>
	<tr>
		<td class='alt'><?php echo $field['label']?></td>
		<td><?php echo $field['value']?></td>
	</tr>
<?php }?>

</tbody>
</table>

<input type="submit" value="<?php echo $form['submitlabel']?>">

<?php if($form['preview']):?>
<!-- guide preview start -->
<input type="button" id='preview' value="<?php echo $form['preview']?>" />
<style>
#dPreview{
	height: 100%;
	width: 100%;
	background: rgba(0,0,0,0.6);
	position: absolute;
	top: 0;
	left: 0;
	z-index: 100;
	display: none;
}
#ipreview{
	width: 1000px;
	padding: 20px;
	float: left;
	position: relative;
	background: #fff;
	margin: 70px 0 20px 0;
	min-height: 600px;
	border-radius: 2px;
	top: 5%;
	left: 10%;
	font-color:black;
	z-index:1000;
}
#ipreview #iajaxPreview{
	width:1000px;
	height:600px;
	background:#ccc;
}
#ipreview #ioperate{
	width:200px;
	height:20px;
	margin-bottom:15px;
}
</style>
<div id='dPreview'>
	<div id='ipreview'>
		<div id='ioperate'>
			<input type='button' id='iupgrage' value='升级' />
			<input type='button' id='iclose' value='关闭'/>
		</div>
		<iframe id="iajaxPreview" src=""></iframe>
	</div>
</div>
<script>
/*$.ajax({
type : 'POST',
url : "iframe",
data:{guideid : guideid},
success : function(msg){
	console.log(msg);
	$('#dPreview').show();
	$('#iajaxPreview').html(msg);
}	
})*/

//存在预览按钮的时候，重定义回车的事件
document.onkeydown = function(e){
	var ev = document.all ? window.event : e;
	if(ev.keyCode == 13){
		$('#preview').click();
		return false;
	}
}
//预览按钮的点击事件
$('#preview').click(function(){
	var guideid = $('input[name="guideid"]').val();
	if(guideid && !isNaN(guideid)){
		$('#dPreview').show();
		$('#iajaxPreview').attr('src', "/user/guide/"+guideid);
	}else{
		alert('请确定攻略id不为空，并且是一个数字');
	}
});
//预览界面中升级按钮的点击事件
$('#iupgrage').click(function(){
	$('#<?php echo $form['id']?>').submit();
})
//预览界面关闭按钮的点击事件
$('#iclose').click(function(){
	$('#dPreview').hide();
})
</script>
<!-- guide preview end -->
<?php endif;?>

</form>
<?php echo $footHTML?>