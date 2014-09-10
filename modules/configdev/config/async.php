<?php

/**
 * Class description.
 *
 * $Id: log.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
$config['mq_addr'] = '192.168.1.108';	// active mq address
$config['mq_queue']['default'] = "/queue/micoQueue";
$config['mq_queue']['log'] = "/queue/micoLog";
$config['mq_queue']['queue'] = "/queue/micoQueue";


$config['mq_enable']["async_log"] = true;
$config['mq_enable']["async_queue"] = true;

$config["async_limit"]["log"] = 100;
$config["async_limit"]["queue"] = 100;
?>