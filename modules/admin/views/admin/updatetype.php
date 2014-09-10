<form id="form1" name="form1" method="get" action="/admin/category/updatetype">
	<input type="text" name="tid" id="textfield3" placeholder="请输入type_id"/>
	<input name="" type="submit" />
</form>


<table width="597" border="1">
  <tr>
    <td>type_id</td>
    <td>name</td>
    <td>操作</td>
  </tr>
  <?php foreach ($cates as $c){ ?>
  <tr>
    <td>
       <input type="text" name="<?php echo $c->id; ?>" class='typeId' value="<?php echo $c->type_id; ?>" style="border: 0px; background:none; width: 100%;color: #FFF;" />
 
    </td>
    <td>
    	<input type="text" name="<?php echo $c->id; ?>s" class='typename' value="<?php echo $c->name; ?>" style="border: 0px; background:none; width: 100%;color: #FFF;"/>
    </td>
    <td>
    	<a style="cursor: pointer;" name="<?php echo $c->id; ?>" class="typedel"  >删除</a>
    </td>
  </tr>
  <?php }?>
</table>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script>
	$(function(){
		$('.typeId').blur(function(){
			var typeId = $(this).val();
			var cid = $(this).attr('name');
			$.ajax({
				   type: "POST",
				   url: "/admin/category/execType",
				   data: "type=typeId&tid="+typeId+"&cid="+cid
			});
			
		})
	})
	
	$(function(){
		$('.typename').blur(function(){
			var name = $(this).val();
			var cid = $(this).attr('name');
			$.ajax({
				   type: "POST",
				   url: "/admin/category/execType",
				   data: "type=name&name="+name+"&cid="+cid
			});
		})
	})
	$(function(){
		$('.typedel').click(function(){
			var result = confirm('是否删除？')
			if(result)
			{
				var cid = $(this).attr('name');
				$.ajax({
					   type: "POST",
					   url: "/admin/category/execType",
					   data: "type=del&cid="+cid
				});
			}
		})
	})
	
	
	
</script>