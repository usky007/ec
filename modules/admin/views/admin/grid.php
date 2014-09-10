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
background: #fff;
font-size:11px;
padding: 6px 6px 6px 12px;
color: #4f6b72;
}
/*power by www.winshell.cn*/

td.alt {
background: #F5FAFA;
color: #797268;
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

<script >
function unMarkAllRows()
{
	var objs = document.getElementsByName('checkrow[]');
	for(var i=0 ; i<=objs.length ; i++ )
	{
		objs[i].checked = false;
	}
}

function markAllRows()
{
	var objs = document.getElementsByName('checkrow[]');
	for(var i=0 ; i<=objs.length ; i++ )
	{
		objs[i].checked = true;
	}
}

</script>
<p></p>
<?php 
 
if(!empty($filter)){?>
搜索：
<form action="" method="get" name="form_filter">
	<table class="dt_filter" style="width:50%" >
		<?php foreach($filter as $ft){?>
		<tr>
			<td style="width:100px">
				<?php echo $ft['label']?>
			</td>
			<td>
				<?php echo $ft['value']?>
			</td>
		</tr>
		<?php  }?>
		<tr>
			<td colspan="2">
				<input type="submit" value="搜索">
			</td>
		</tr>
	</table>
</form>
<br/> 
<?php }?>
<form action="<?php echo $form['action']?>" method="<?php echo $form['method']?>" name="<?php echo $form['name']?>" id="<?php echo $form['id']?>">
<?php if($form['action'] !==""){?>
<a href="#" onclick="markAllRows();return false;">Check All</a>
 / 
<a href="#" onclick="unMarkAllRows(); return false;">Uncheck All</a>

<i>With selected:</i>
<?php }?>


<?php if(isset($addurl)){?>
<input type="button" value="add" onclick="document.location='<?php echo $addurl?>'"><?php }?>

<?php if($form['action'] !==""){?>
<input type="submit" value="<?php echo isset($delbtnval)?$delbtnval:"delete"; ?>"><?php }?>
<table id="<?php echo $table['id']?>" class="data ajax"  >
<thead>
	<tr>
		<th style="width:8px"></th>
		<?php 
		foreach($dt_columns as $column)
		{
			$columnname = $column['settings']['label'];
			$columnfield = $column['field'];
			$needorder = isset($column['settings']['order'])?$column['settings']['order']:false;
			if($needorder)
			{
				$pathinfo = $rurl = strpos($_SERVER['REQUEST_URI'],"?")?substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"?")):$_SERVER['REQUEST_URI'];
				$getparam = $_GET;
				
				if(isset($getparam['sort']))
					if($getparam['order']==$columnfield)
						$getparam['sort'] = $getparam['sort']=='asc'?'desc':'asc';
					else
						$getparam['sort'] = 'asc';
				else
					$getparam['sort'] = 'asc';
 
				$getparam['order'] = $columnfield;
				$orderurl = $pathinfo."?".http_build_query($getparam);
				 
				echo "<th><a href=\"$orderurl\">$columnname</a></th>";
				
			}
			else
				echo "<th>$columnname</th>";
		}
		?>
	</tr>
</thead>
<tbody>
<?php 
for($rowIdx=0;$rowIdx<count($dt_rows);$rowIdx++){?>
	<tr class="<?php echo $rowIdx%2==1?"even":"odd"; ?>">
		<?php if($form['action'] !==""){
			$ischecked = in_array($dt_rows[$rowIdx]['value'],$checked_rows)?"checked":"";	
		?>
		<td><input type="checkbox" name="checkrow[]" id="ck_<?php echo $dt_rows[$rowIdx]['value']?>"
		<?php echo $ischecked?> value="<?php echo $dt_rows[$rowIdx]['value']?>"/></td>
		<?php }else{?>
		<td><?php echo $rowIdx;?></td>
		<?php }?>
		<?php foreach($dt_rows[$rowIdx]['cells'] as $cell){
			echo "<td>$cell</td>";
		}?>
	</tr>
<?php }?>
</tbody>
</table>

 
</form>

