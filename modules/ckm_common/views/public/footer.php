<div class="" id="footer">
	<div class="block">
		<h2>找到你的目的地</h2>
		<div class="partial-site-cities">
			<?php
			$col = 7;
			if(isset($allcities) && isset($allcities['open']))
			{
					$minsize = 7;
					if (count($allcities['open']) <= $col * $minsize) {
							// if total cities less than 50, use simple layout
							// keep minimum column
							$size = ceil(count($allcities['open']) / $col);
							echo '<ul class="column">';
							for($i = 0;$i < $size * $col; $i++) {
									if($i > 0 && $i % $size == 0)
											echo '</ul><ul class="column">';
									$j = $i % $size * $col + floor($i / $size);
									if ($j >= count($allcities['open'])) {
											continue;
									}
									echo '<li><a href="'.url::site('/city/'.$allcities['open'][$j]->citycode).'">'.$allcities['open'][$j]->cityname.'</a></li>';
							}
							echo '</ul>';
					} else {
							// advanced layout with alphabit index
							$last_group = substr($allcities['open'][0]->citycode, 0, 1);
							$last_index = substr($allcities['open'][0]->citycode, 0, 1);
							$city_groups = array();
							$city_groups[$last_group] = array();
							$limit = ceil(count($allcities['open']) / 5);
							for ($i = 0; $i < count($allcities['open']); $i++) {
							$letter = substr($allcities['open'][$i]->citycode, 0, 1);
							if (count($city_groups[$last_group]) >= $limit && $letter != $last_index) {
									if ($last_index != $last_group) {
											$city_groups["{$last_group} - {$last_index}"] = $city_groups[$last_group];
											unset($city_groups[$last_group]);
									}
									$last_group = $letter;
									$last_index = $letter;
									$city_groups[$last_group] = array();
							}
							else if ($letter != $last_index) {
									$last_index = $letter;
							}
							$city_groups[$last_group][] = $allcities['open'][$i];
							}
							if ($last_index != $last_group) {
							$city_groups["{$last_group} - {$last_index}"] = $city_groups[$last_group];
							unset($city_groups[$last_group]);
							}
							foreach ($city_groups as $index => $city_group) {
									$size = ceil(count($city_group) / $col);
									echo '<ul class="first column" style="width:40px;"><li><index>'.strtoupper($index).'</index></li>';
									for($i = 1; $i < $size; $i++) {
											echo '<li><br/></li>';
									}
									echo '</ul><ul class="first column">';
									for($i = 0;$i < $size * $col; $i++)
									{
											if($i > 0 && $i % $size == 0)
													echo '</ul><ul class="column">';
											$j = $i % $size * $col + floor($i / $size);
											if ($j >= count($city_group)) {
													echo '<li><br/></li>';
											}
											else {
													echo '<li><a href="'.url::site('/city/'.$city_group[$j]->citycode).'">'.$city_group[$j]->cityname.'</a></li>';
											}
									}
									echo '</ul>';
							}
					}
			}
			?>
			<div class="clear"></div>
		</div>
	</div>
</div>

<div class="clear"></div>

<div style="height:380px; background:url(<?php echo $layout->resource_path('images/bg_box.jpg'); ?>) center bottom no-repeat;"></div>
<div id="u_copyright">
	Copyright 2007-<?php echo date('Y');?>, uutuu.com 版权所有. 
	<a href="http://www.miibeian.gov.cn/state/outPortal/loginPortal.action" target="_blank">沪ICP备11036621号</a> |
	增值电信业务经营许可证沪B2-20070198号 |
	<a href="http://www.uutuu.com/files/xingzhe.jpg" target="_blank">营业执照</a> |
	<a href="http://www.tclub.cn" target="_blank">公司官网</a> |
	<a href="http://www.uutuu.com/tops/privacy" target="_blank">隐私保护</a> |
	<a href="http://www.uutuu.com/static/link.html" target="_blank">友情链接</a><br/>
	<a target="_blank" href="http://sh.cyberpolice.cn/infoCategoryListAction.do?act=initjpg"><img src="<?php echo $layout->resource_path('images/cert/icp_110.png'); ?>"></a>
	<a style="margin-left:18px" target="_blank" href="http://www.zx110.org/"><img src="<?php echo $layout->resource_path('images/cert/icp_zx110.png'); ?>"></a>
	<a style="margin-left:18px" target="_blank" href="http://www.sgs.gov.cn/lz/licenseLink.do?method=licenceView&amp;entyId=2011122717063481"><img src="<?php echo $layout->resource_path('images/cert/icp_ic.png'); ?>"></a>
</div>
