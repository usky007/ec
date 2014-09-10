<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * admin pagination style
 * 
 * @preview  ‹ First « Previous  < 1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26 > Next » Last ›
 */
?>

<table class="pagination">
	<tr>
	<td>
	<?php if ($first_page): ?>
		<span><a href="<?php echo str_replace('{page}', 1, $url) ?>">&lsaquo;&nbsp;<?php echo Kohana::lang('pagination.first') ?></a></span>
	<?php endif ?>

	<?php if ($previous_page): ?>
		<span><a href="<?php echo str_replace('{page}', $previous_page, $url) ?>">&laquo;&nbsp;<?php echo Kohana::lang('pagination.previous') ?></a></span>
	<?php endif ?>


	<?php if ($total_pages < 13): /* « Previous  1 2 3 4 5 6 7 8 9 10 11 12  Next » */ ?>

		<?php for ($i = 1; $i <= $total_pages; $i++): ?>
			<?php if ($i == $current_page): ?>
				<span><strong><?php echo $i ?></strong></span>
			<?php else: ?>
				<span><a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a></span>
			<?php endif ?>
		<?php endfor ?>

	<?php elseif ($current_page < 9): /* « Previous  1 2 3 4 5 6 7 8 9 10 … 25 26  Next » */ ?>

		<?php for ($i = 1; $i <= 10; $i++): ?>
			<?php if ($i == $current_page): ?>
				<span><strong><?php echo $i ?></strong></span>
			<?php else: ?>
				<span><a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a></span>
			<?php endif ?>
		<?php endfor ?>

		<span>&hellip;</span>
		<span><a href="<?php echo str_replace('{page}', $total_pages - 1, $url) ?>"><?php echo $total_pages - 1 ?></a></span>
		<span><a href="<?php echo str_replace('{page}', $total_pages, $url) ?>"><?php echo $total_pages ?></a></span>

	<?php elseif ($current_page > $total_pages - 8): /* « Previous  1 2 … 17 18 19 20 21 22 23 24 25 26  Next » */ ?>

		<span><a href="<?php echo str_replace('{page}', 1, $url) ?>">1</a></span>
		<span><a href="<?php echo str_replace('{page}', 2, $url) ?>">2</a></span>
		<span>&hellip;</span>

		<?php for ($i = $total_pages - 9; $i <= $total_pages; $i++): ?>
			<?php if ($i == $current_page): ?>
				<span><strong><?php echo $i ?></strong></span>
			<?php else: ?>
				<span><a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a></span>
			<?php endif ?>
		<?php endfor ?>

	<?php else: /* « Previous  1 2 … 5 6 7 8 9 10 11 12 13 14 … 25 26  Next » */ ?>

		<span><a href="<?php echo str_replace('{page}', 1, $url) ?>">1</a></span>
		<span><a href="<?php echo str_replace('{page}', 2, $url) ?>">2</a></span>
		<span>&hellip;</span>

		<?php for ($i = $current_page - 5; $i <= $current_page + 5; $i++): ?>
			<?php if ($i == $current_page): ?>
				<span><strong><?php echo $i ?></strong></span>
			<?php else: ?>
				<span><a href="<?php echo str_replace('{page}', $i, $url) ?>"><?php echo $i ?></a></span>
			<?php endif ?>
		<?php endfor ?>

		<span>&hellip;</span>
		<span><a href="<?php echo str_replace('{page}', $total_pages - 1, $url) ?>"><?php echo $total_pages - 1 ?></a></span>
		<span><a href="<?php echo str_replace('{page}', $total_pages, $url) ?>"><?php echo $total_pages ?></a></span>

	<?php endif ?>


	<?php if ($next_page): ?>
		<span><a href="<?php echo str_replace('{page}', $next_page, $url) ?>"><?php echo Kohana::lang('pagination.next') ?>&nbsp;&raquo;</a></span>
	<?php endif ?>

	<?php if ($last_page): ?>
		<span><a href="<?php echo str_replace('{page}', $last_page, $url) ?>"><?php echo Kohana::lang('pagination.last') ?>&nbsp;&rsaquo;</a></span>
	<?php endif ?>
	<span>(<?php echo $total_pages.' '. Kohana::lang('pagination.pages')?>)</span>
	</td>
	</tr>
</table>