<div class="comment" id="<?php echo $id; ?>">
<div class="avatar"><img src="<?php echo $user['profile_image_url']?>"></div>
<div class="text"><a href="<?php echo miurl::subsite("maggie/", "user/".urlencode('@'.$user['profile_image_url'])); ?>" target="_blank" class="light"><?php echo $user['name'];?></a> &nbsp;<?php echo $text;?>
<span class="time">(<?php echo date('Y-m-d H:i:s',strtotime($created_at))?>)</span>&nbsp;&nbsp;
<span class="replyspan"><a href="javascript:void(0);" class="replybtn light" cid="<?php echo $id?>" mid="<?php echo isset($status_id)?$status_id:$status['id'];?>" sw="回复@<?php echo $user['name']?>:">回复</a></span></div>
</div>