<?php
/**
 * Class description.
 *
 * $Id: maggie.php 25 2011-06-21 09:06:11Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2008-2010 UUTUU
 */
class Features_Controller extends LayoutController {
	protected static $provider = "sina";
	
	public function __construct()
	{
		parent::__construct();
		$this->import_config("maggie/".__CLASS__);
		//$this->set_format("xml");
	}
	
	public function index() {
		switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
			case 'POST': return $this->add_new();
			case 'GET':
			default:
				return $this->get();
		}
	}
	
	public function get() {
		$preference = Preference::instance("maggie_features");
		
		$features = array(
			"title" => "Features",
			"link" => array ("@rel"=>"self", "@href"=>miurl::subsite("maggie/", "features"))
		);
		
		foreach ($preference->entries() as $entry) {
			$val = unserialize($entry->value);
			$feature = array(
				"title" => $val['nickname'],
				"identity" => $entry->key,
				"link" => array ("@href"=>miurl::subsite('maggie/', 'feature/'.$entry->key))
			);
			$features['feature'][] = $feature;
		}
		$this->set_output($features);
	}
	
	public function guardian($set = null) {
		if (!isset($set) && Account::instance()->checklogin(false)) {
			url::redirect(url::site("logout?return=".urlencode(miurl::subsite("maggie/", "features/guardian/set"))));
			return;
		}
		
		$account = Account::instance();
		$account->checklogin();
		
		$credential = new Credential(self::$provider, $account->loginuser);
		if (!$credential->is_valid()) {
			throw new UKohana_Exception(E_MICO_CREDENTIAL_NOT_FOUND, "errors.credential_not_found");
		}
		
		$user = $account->loginuser;
		$preference = Preference::instance("maggie_features");
		$preference->set("guardian", serialize(array("uid" => $user->uid, "nickname" => $user->nickname)));
		
		$help = array (
			"title" => "Guardian $user->nickname Set",
			"link" => array(
				array ("@href" => miurl::subsite("maggie/", "features")),
				array ("@rel"=>"self", "@href" =>miurl::subsite("maggie/", "features/guardian/set")),
				array ("@rel"=>"add", "@href"=>miurl::subsite("maggie/", "features/add"))
			)
		);
		$this->set_output($help);
	}
	
	public function add($hint = null) {
		if (isset($hint)) {
			$func = "add_$hint";
			return $this->$func();
		}
		
		$help = array (
			"title" => "How to Add Features",
			"link" => array(
				array ("@href" => miurl::subsite("maggie/", "features")),
				array ("@rel"=>"setGrardian", "@href"=>miurl::subsite("maggie/", "features/guardian/set")),
				array ("@rel"=>"self", "@href"=>miurl::subsite("maggie/", "features/add")),
				array ("@rel"=>"addCurrent", "@href" =>miurl::subsite("maggie/", "features/add/current")),
				array ("@rel"=>"addNew", "@href" =>miurl::subsite("maggie/", "features/add/new"))
			)
		);
		
		$this->set_output($help);
	}
	
	protected function add_current() {
		$account = Account::instance();
		$account->checklogin();
		
		$credential = new Credential(self::$provider, $account->loginuser);
		if (!$credential->is_valid()) {
			throw new UKohana_Exception(E_MICO_CREDENTIAL_NOT_FOUND, "errors.credential_not_found");
		}
		
		$user = $account->loginuser;
		$preference = Preference::instance("maggie_features");
		$preference->set($credential->identity, serialize(array("uid" => $user->uid, "nickname" => $user->nickname)));
		
		$help = array (
			"title" => "Feature $user->nickname Added",
			"link" => array(
				array ("@href" => miurl::subsite("maggie/", "features")),
				array ("@rel"=>"help", "@href"=>miurl::subsite("maggie/", "features/add")),
				array ("@rel"=>"self", "@href" =>miurl::subsite("maggie/", "features/add/current")),
				array ("@rel"=>"addNew", "@href" =>miurl::subsite("maggie/", "features/add/new")),
				array ("@rel"=>"done", "@href" =>url::site("logout?return=".urlencode(miurl::subsite("maggie/", "features"))))
			)
		);
		$this->set_output($help);
	}
	
	protected function add_new() {
		if (Account::instance()->checklogin(false)) {
			url::redirect(url::site("logout?return=".urlencode(miurl::subsite("maggie/", "features/add/current"))));
		}
		
		url::redirect(miurl::subsite("maggie/", "features/add/current"));
	}
}
?>