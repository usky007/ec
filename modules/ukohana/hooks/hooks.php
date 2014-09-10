<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * ukohana module hook.
 *
 * $Id: hooks.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */
if (config::item("context.enable")) {
	Event::add_before('system.routing', array('Router', 'setup'), array('Context_Input', 'parse_uri'));
}

Event::add('system.post_routing', array('UKohana_Exception', 'set_handler'));
Event::add('system.post_routing', array('Advance_Router', 'setup'));
?>