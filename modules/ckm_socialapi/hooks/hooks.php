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
if (config::item("account.support_guest", false, false)) {
	Event::add(Account::EVENT_ON_LOGIN, array('Credential', 'update_credential_flags'));
	Event::add(Account::EVENT_ON_INITIALIZE, array('Credential', 'account_on_initialize_handler'));
}
?>