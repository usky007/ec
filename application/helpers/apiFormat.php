<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Format Helper.
 *
 * $Id: format.php 1579 2012-08-13 06:54:36Z xuronghua $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class apiFormat {

	function getLink_UserGuides($user)
	{
		return url::site("api/user?uid={$user->uid}");
	}
	
	function getLink_Location($location)
	{
		return url::site("api/location?lid={$location->lid}");
	}
	
	function getLink_GuideLocation($guideLocation = null, $op = null)
	{
		$url = url::site("api/myLocation".(is_null($guideLocation) ? "" : "?id={$guideLocation->id}"));
		if (!is_null($guideLocation) && !is_null($op)) {
			$url .= "&op=$op";
		}
		return $url;
	}
	
	function getLink_Suggestion($op=null)
	{
		return url::site("api/suggestion/".$op);
	}

	function getLink_Guide($guide=null)
	{
		
		return url::site("api/guide".(is_null($guide) ? "" : "?gid={$guide->gid}"));
	}
	
	function getLink_GuideComment($comment=null)
	{
		
		return url::site("api/comment?type=guide".(is_null($comment) ? "" : "&id={$comment->id}"));
	}
	
	function getLink_GuideLocationComment($comment=null)
	{
		
		return url::site("api/comment?type=myLocation".(is_null($comment) ? "" : "&id={$comment->id}"));
	}
	function getLink_City($city=null)
	{
		return url::site("api/city".(is_null($city) ? "" : "?citycode={$city->citycode}"));
	}
}
?>