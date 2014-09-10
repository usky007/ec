
<p>合并地点</p>
<table width="597" border="1">
  <tr>
  	<td>字段</td>
    <td>地点</td>
    <?php

    	foreach($sameLocs as $loc){

		echo '<td> <input value="合并到此地点" type="button" onclick="document.location=\'/admin/location/mergeSameLocationSave/'.
		 $baseLoc->lid.'/'.$loc->lid.'?reurnURL='.$reurnURL.'\';"/> </td>';


 	}?>
  </tr>

  <tr>
    <td>
		lid
    </td>
 	<td>
 		<?php echo $baseLoc->lid;?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$loc->lid.'</td>';
 	}?>
  </tr>

  <tr>
    <td>
		citycode
    </td>
 	<td>
 		<?php echo $baseLoc->citycode;?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$loc->citycode.'</td>';
 	}?>
  </tr>

  <tr>
    <td>
		名称
    </td>
 	<td>
 		<?php echo $baseLoc->name;?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$loc->name.'</td>';
 	}?>
  </tr>


  <tr>
    <td>
		内容
    </td>
 	<td>
 		<?php echo $baseLoc->content;?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$loc->content.'</td>';
 	}?>
  </tr>


  <tr>
    <td>
		地址
    </td>
 	<td>
 		<?php echo $baseLoc->address;?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$loc->address.'</td>';
 	}?>
  </tr>

  <tr>
    <td>
		出现过的攻略
    </td>
 	<td>
 		<?php echo $relativeGuideStr[$baseLoc->lid];?>
 	</td>
 	<?php foreach($sameLocs as $loc){
		echo '<td>'.$relativeGuideStr[$loc->lid].'</td>';
 	}?>
  </tr>



</table>