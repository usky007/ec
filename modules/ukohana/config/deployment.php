<?php
/**
 * Class description.
 *
 * $Id: deployment.php 330 2011-06-21 09:46:50Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
$config['scripts']['Sequence_Model'] = 'deploy';
$config['scripts']['Dicentry_Model'] = 'deploy';
$config['scripts']['Cachetag_Dicentry_Model'] = 'deploy';
$config['scripts']['Camp_Model'] = 'deploy';
//$config['scripts']['PinyinMap'] = 'deploy';

$config['depend']['Cachetag_Dicentry_Model'] = 'Dicentry_Model';
$config['depend']['PinyinMap'] = 'Cachetag_Dicentry_Model';
?>