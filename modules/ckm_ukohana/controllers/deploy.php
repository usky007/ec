<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Deploy controller.
 * "deployment.scripts" in config contains deployment callbacks to execute.
 * "deployment.depend" in config contains "class" dependency for deployment.
 * Deploy callback protocol: int deploy($current_version).
 * Controller will automatically try: int deploy_$ver() for update deployment recurrsively.
 *
 * $Id: deploy.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Deploy_Controller extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;
	private $_configDepend;
	private $_firstJobQueue;
	function index()
	{
		set_time_limit(86400000);
		
		$depend_rules = config::item("deployment.depend", false, array());
		$deploy_scripts = config::item("deployment.scripts", false, array());
		
		foreach ($deploy_scripts as $class => $methods) {
			$deploy_scripts[$class] = array(
				"func" => (is_int($class) ? $methods : array($class, $methods)),
				"priority" => 1
			);
		}
		
		// Check depends and reorder deploy order.
		$error = false;
		foreach($depend_rules as $class => $rule)
		{
			$error = $this->_cb_apply_depend_rule($deploy_scripts, $depend_rules, $class, $rule) || $error;
		}
		if ($error) {
			return;
		}
		
		$job_queue = array_values($deploy_scripts);
		usort ($job_queue, array($this, "_cb_compare_priority"));
		echo "Deployment will execute as following order:<br/>";
		foreach ($job_queue as $job_script) {
			echo "[{$job_script['priority']}] => ".var_export($job_script['func'], true)."<br/>";
		}
		
		// If use dictionary driver as version control, deploy it first.(Updates for dicentry could be done later)
		$preference = Preference::instance("deploy");
		if ($preference->get_driver() instanceof Preference_Dictionary_Driver) {
			Dicentry_Model::deploy();
		}
		
		// Execute deployment script.
		echo "Start deploy...<br/>";
		foreach ($job_queue as $job_script) {
			$this->_cb_deploy($job_script['func']);
		}
	}
	
	function _cb_apply_depend_rule(&$deploy_scripts, &$depend_rules, $class, $rule = NULL) {
		if (is_null($rule)) {
			$rule = $depend_rules[$class];
		}
	
		$error = false;
		$depends = is_array($rule) ? $rule : array($rule);
		foreach ($depends as $depend) {
			if (isset($deploy_scripts[$depend])) {
				$deploy_scripts[$depend]["priority"]++;
				if (isset($depend_rules[$depend])) {
					$error = $this->_cb_apply_depend_rule($deploy_scripts, $depend_rules, $depend) || $error;
				}
			}
			else {
				echo "Warning: {$class}'s deployment depends on {$depend}, which no deployment script found.<br/>";
				$error = true;
			}
		}
		return $error;
	}
	
	function _cb_compare_priority($a, $b) {
		return $b['priority'] - $a['priority'];
	}
	
	function _cb_deploy($script) {
		$preference = Preference::instance("deploy");
		$verkey = is_array($script) ? $script[0]."::".$script[1] : $script;
		
		$ver = $preference->get($verkey);
		log::debug("Got deployment status:{$ver}, script key:{$verkey}").
		$new_ver = 1;
		$executed_once = false;
		while (true) {
			if ($ver == NULL) {
				log::debug("Script($verkey) never runned, run first version.");
				$ver = 0;
				$new_ver = call_user_func_array($script, array(0));
				$ver = $this->_save_deploy_version($verkey, $new_ver);
				$executed_once = true;
			}
			else {
				$ver_script = null;
				if (is_array($script)) {
					$ver_script = array($script[0], $script[1]."_$ver");
				}
				else {
					$ver_script = $script."_$ver";
				}
				
				// call depley script
				if (is_callable($ver_script)) {
					log::debug("Script($verkey), run deploy_{$ver}.");
					$new_ver = call_user_func($ver_script);
					
				}
				else if (!$executed_once) {
					log::debug("Script($verkey), run deploy({$ver}).");
					$new_ver = call_user_func_array($script, array($ver));
				}
				else {
					// deploy($ver) would run twice.
					break;
				}
				
				// if script didn't returned a proper version, stop
				$executed_once = true;
				if ($new_ver && $new_ver > $ver) {
					$ver = $this->_save_deploy_version($verkey, $new_ver);
				}
				else {
					break;
				}
			}
		}
		log::debug("Script($verkey), stop at ver:{$new_ver}.");
	}
	
	function _save_deploy_version($key, $new_ver) {
		$preference = Preference::instance("deploy");
		$ver = $preference->get($key);
		if ($ver == NULL) {
			$ver = 0;
		}
		$new_ver = $new_ver ? $new_ver : 1;
		$preference->set($key, $new_ver);
		return $new_ver;
	}
}
?>