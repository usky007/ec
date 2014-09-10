<div class="container page">
<?php if (isset($feature)) foreach ($feature as $f) { ?>
	<div class="post mixed white">
		<div class="inside">
			<div class="microblog"><a href="<?php echo $f['link']['@href']; ?>" class="light"><?php echo $f['title'] ?></a></div>
		</div>
	</div>
<?php } ?>
<?php if (isset($link[0])) foreach ($link as $ln) { ?>
	<div>
		<?php echo isset($ln['@rel']) ? $ln['@rel'] : "home"; ?> <a href="<?php echo $ln['@href']; ?>"><?php echo $ln['@href'] ?></a>
	</div>
<?php } ?>
</div>