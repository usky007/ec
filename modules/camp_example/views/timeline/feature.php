<div style="position: absolute;" class="post photo feature" id="u_<?php echo $suid; ?>" ttl="<?php echo $ttl; ?>">
	<div class="inside"><div class="media">
		<a href="<?php echo miurl::subsite('maggie/', "user/$identity"); ?>"><img src="<?php echo $photo; ?>">
		<div class="overlay"><br/></div>
		<div class="name"><?php echo $username; ?></div>
		</a>
	</div></div>
	<a href="#<?php echo miurl::subsite('maggie/', "ajax/unmark"); ?>" op="remove" identity="<?php echo $identity; ?>"><img class="delete" src="<?php echo $layout->resource_path("images/timeline/delete.png"); ?>"/></a>
</div>