<?php

/**
 * Class description.
 *
 * $Id: storage.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
class Storage_Model extends ORM{

	protected $_primary_key = 'stid';
	protected $_table_name = "Storages";
	protected $_table_names_plural = FALSE;

	public static function deploy() {
		$db = & Database::instance();
		try {
			$result = $db->query(
				"CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Storages` (
					  `stid` int(10) NOT NULL,
					  `name` varchar(100) NOT NULL,
					  `ip` varchar(32) NOT NULL,
					  `uri` varchar(100) NOT NULL,
					  `occupancy` float NOT NULL default '0',
					  `used` bigint(20) NOT NULL default '0',
					  `capacity` bigint(20) NOT NULL default '0',
					  `weight` int(10) NOT NULL default '0',
					  `enable` tinyint(1) NOT NULL default '0',
					  `application` varchar(20) NOT NULL,
					  PRIMARY KEY  (`stid`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8");
			echo __CLASS__." deployed.<br />";

			$count = $db->count_records("Storages");

			$ist_sql ="INSERT INTO `{$db->table_prefix()}Storages`
				(`stid`, `name`, `ip`, `uri`, `occupancy`, `used`, `capacity`, `weight`, `enable`, `application`) VALUES
				(1001, 'photol', '', 'local/', 0, 0, 0, 10, 1, 'photo'),
				(1002, 'coupon', '', 'coupon/', 0, 0, 0, 10, 1, 'coupon'),
				(1003, 'highlight', '', 'highlight/', 0, 0, 0, 10, 1, 'highlight');";
			 if($count==0)
			 {
				$result = $db->query($ist_sql);
				echo __CLASS__." Data Initialized <br />";
			 }
		}
		catch (Kohana_Database_Exception $ex)
		{
			echo "Error occurs on deploy ".__CLASS__.":{$ex->getMessage()}<br />";
		}
	}
}
?>