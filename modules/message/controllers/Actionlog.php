<?php

/**
 * Class description.
 *
 * $Id: async.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class Actionlog_Controller extends Controller {
	public function Recall($lid=0)
	{
		Kohana::log("debug","asycn log processing :$lid ....");
		$bs = new Backservice();
		$bs->log_process($lid);
		Kohana::log("debug","asycn log processed :$lid");
	}
}
?>