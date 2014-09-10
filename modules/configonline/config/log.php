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
$config['mq_queue']['async_log'] = "/queue/micoLog";
$config['mq_queue']['boardcast_uh'] = "/topic/micoUHLog";
$config['mq_enable']['send_msg'] = false;					// global msg sending settings, "false" to disable
$config['mq_enable']['do_optmize'] = true;					// enable/disable photo post optimization.
$config['mq_enable']['async_delete'] = true;					// enable/disable photo resource deletion on delete(complete).
$config['mq_enable']['boardcast_uh'] = true;				// enable/disable user history(action log) boardcast
$config['mq_enable']["async_log"] = true;
$config['mq_enable']["async_queue"] = true;


$config["log"]['logaction_enable'] = true;
$config["log"]['logdb_connect'] = true;

$config["log"]["log_percent"] = 100; //according to "async_log"
$config["log"]["async_limit"] = 100; //the number of dealing with logs that action is visit
$config["log"]["del_interval"] = 30;//day

$config["async_host"] = "http://dev.mico.cc/";
$config["async_dir"] = "async/LogProcess/";
?>