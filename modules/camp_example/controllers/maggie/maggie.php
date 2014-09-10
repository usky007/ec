<?php
/**
 * Class description.
 *
 * $Id: maggie.php 55 2011-07-27 11:34:50Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xiongxiaoqiang
 * @copyright  (c) 2008-2010 UUTUU
 */
class Maggie_Controller extends LayoutController {
	protected static $provider = "sina";

	public function __construct()
	{
		parent::__construct();

		AppLayout_View::set_layout("layouts/timeline");
		$this->positions = array(
			"headers" => array(),
			"footer"  => "timeline/footer"
		);
	}

	public function index(){
		return $this->feature();
	}

	public function feature($id = null) {
		if ($id == null) {
			$credential = $this->check_credential(self::$provider);
		}
		else {
			if (!$this->is_public_feature($id)) {
				throw new UKohana_Exception(E_MICO_UNSUPPORTED, "errors.unsupported");
			}
			$credential = Credential::get_credential_by_identity(self::$provider, $id);
			if ($credential == null || !$credential->is_valid()) {
				throw new UKohana_Exception(E_MICO_UNSUPPORTED, "errors.unsupported");
			}
		}
		
		$mypage = $id == null;
		View::set_global("mypage", $mypage);
		$timeline = $this->maggie_impl($credential, null, "");
		$user = new SocialUser($credential);
		$user = $user->get($credential->identity)->data();
		
		if ($mypage && count($timeline) > 0 && $timeline->uptodate) {
			$this->set_js_context('last_status_id', $timeline[0]['id']);
		}
		
		$this->get_layout()->add_view("headers", new View("timeline/user_header", $user));
		$this->get_layout()->add_css( "css/central/timeline.css" )
			->add_js( "js/central/timeline.js" );
	}

	public function mentions() {
		$credential = $this->check_credential(self::$provider);
		
		View::set_global("mypage", true);
		$timeline = $this->maggie_impl($credential, new MentionTimeline($credential), "");
		$user = new SocialUser($credential);
		$user = $user->get($credential->identity)->data();
		
		try {
			$timeline->reset();
		}
		catch (Exception $e) {
			// ignore;
		}

		$this->get_layout()->add_view("headers", new View("timeline/user_header", $user));
		$this->get_layout()->add_css( "css/central/timeline.css" )
			->add_js( "js/central/timeline.js" );
	}

	public function user($id = null) {
		$credential = $this->check_credential(self::$provider);

		if ($id == null) {
			$id = $credential->identity;
		}

		if (!is_numeric($id)) {
			$id = preg_replace('/^@?(.+)$/', '@\\1', $id);
		}
		
		$mypage = $id == $credential->identity;
		View::set_global("mypage", $mypage);
		
		$timeline = $this->maggie_impl($credential, $id, preg_replace("/^@/", "", $id));
		if (count($timeline) > 0) {
			$user = $timeline[0]['user'];
		}
		else {
			// No need to check user here, checked already when get timeline.
			$user = new SocialUser($credential);
			$user = $user->get($id)->data();
		}
		
		if (!$mypage) {
			// friendship check
			$friendship = new AuthorizedRestObject($credential, "Friendship", self::$provider);
			$friendship = $friendship->get(array("target_id" => $user['id']));
			$user['followed'] = $friendship['target']['followed_by'];
			
			// favorite check
			$pref = Preference::instance($credential->identity, new Preference_Dictionary_Driver(new Favorite_Timelines_Model()));
			$user['marked'] = !is_null($pref->get($user['id']));
		}
		
		// update social user info
		foreach ($timeline as $status) {
			if (!empty($status['bmiddle_pic'])) {
				$userinfo = Socialusers_Model::from_profile(self::$provider, $status['user']);
				if (!isset($userinfo->lastStatusId) || $timeline[0]['id'] > intval($userinfo->lastStatusId)) {
					$userinfo->lastStatusId = (string)$timeline[0]['id'];
					$userinfo->recentMediaLink = $status['bmiddle_pic'];
				}
				if (!$userinfo->saved()) {
					$userinfo->save();
				}
				break;
			}
		}
		
		$this->get_layout()->add_view("headers", new View("timeline/user_header", $user));
		$this->get_layout()->add_css("css/central/timeline.css" )
			->add_js( "js/central/timeline.js" );
	}

	public function topic($topic) {
		$credential = $this->check_credential(self::$provider);

		View::set_global("mypage", false);
		$timeline = $this->maggie_impl($credential, $topic, $topic);

		$this->get_layout()->add_view("headers", new View("timeline/topic_header", array("topic"=>$topic)));
		$this->get_layout()->add_css( "css/central/timeline.css" )
			->add_js( "js/central/timeline.js" );
	}
	
	public function favorites() {
		$credential = $this->check_credential(self::$provider);
		
		$this->get_layout();
		View::set_global("mypage", true);
		$pref = Preference::instance($credential->identity, new Preference_Dictionary_Driver(new Favorite_Timelines_Model()));
		$page = $pref->entries();
		
		$html = "";
		foreach ($page as $identity => $updated) {
			$user = new Socialusers_Model();
			if (!$user->find(array("srcAgent"=>self::$provider, "srcId"=>$identity))->loaded()) {
				continue;
			}
			
			$data = array(
				"suid"	   => $user->suid,
				"identity" => $identity,
				"username" => $user->username,
				"photo"	   => isset($user->recentMediaLink) ? $user->recentMediaLink : $user->avatar,
				"ttl"      => time() - $user->updated
			);
			$html .= new View("timeline/feature", $data);
		}
		
		$user = new SocialUser($credential);
		$user = $user->get($credential->identity);
		
		
		$data = array(
			"timeline" => $html,
			"uptodate" => true,
			"nomore" => true,
			"upto" => 1,
			"since" => 1,
			"uri" => miurl::subsite("maggie/", 'favorite'),
			"identity" => $credential->identity
		);
		
		$this->set_view("timeline/content");
		$this->set_output($data);
		
		$this->get_layout()->set_title(Kohana::lang("titles.maggie/favorites"))
			->add_view("headers", new View("timeline/user_header", $user))
			->add_css( "css/central/timeline.css" )
			->add_js( "js/central/timeline.js" );
	}

	protected function check_credential($provider, $redirect = true) {
		$act = &Account::instance();
		if (!$act->checklogin($redirect))
			return null;

		$credential = new Credential($provider, $act->loginuser);
		if ($credential->is_valid()) {
			return $credential;
		}
		else if ($redirect) {
			redirect(url::site("social/authorize/$provider?return=".urlencode(url::site(URI::instance()->string()))));
		}
		else {
			return null;
		}
	}

	protected function maggie_impl($credential, $key, $title_key) {
		$input = Input::instance();
		$since = $input->get("since", Timeline::ID_FIRST, true);
		$upto = $input->get("upto", Timeline::ID_RECENT, true);

		$timeline = $key;
		if (!($timeline instanceof Timeline)) {
			$timeline = Timeline::get_timeline($credential, $key)->since($since)->upto($upto);
		}
		$this->get_layout()->set_title(Kohana::lang("titles.".preg_replace('/^maggie\/(.+?)($|\/.*)/', 'maggie/\1', Router::$routed_uri), $title_key));
		
		if (Input::instance()->get("debug", 0, true) == 1) {
			echo "<pre>";
			foreach ($timeline as $id => $status) {

				var_dump($status);
			}
			echo "</pre>";
		}
		else {
			$segments = explode("/", URI::instance()->string());
			$segments[count($segments) - 1] = urlencode($segments[count($segments) - 1]);
			
			$medias = array();
			foreach ($timeline as $id => $status) {
				if (isset($status['media'])) {
					$medias[$status['tcid']] = 
						array_intersect_key($status['media'], array("type"=>null, "video"=>null));
				}
			}

			$data = array(
				"timeline" => new Timeline_View($timeline),
				"uptodate" => $timeline->uptodate,
				"nomore" => $timeline->nomore,
				"upto" => (count($timeline) > 0) ? $timeline[0]['id'] : 0,
				"since" => (count($timeline) > 0) ? $timeline[count($timeline) - 1]['id'] : 0,
				"uri" => join("/", $segments),
				"identity" => $credential->identity
			);
			$this->set_js_context("medias", $medias);
			$this->set_view("timeline/content");
			$this->set_output($data);
		}

		return $timeline;
	}
	
	public function uploadpic()
	{
		if (!empty($_FILES) && !empty($_FILES['uploadfile']['name']) ) 
		{
			$upload->set_allowed_types(config::item('upload.allowed_types'));
			$upload->do_upload('pic','coupon',$url);
			$full_url = format::get_local_storage_url($url,'photo');
		}
		$data = array();
		$credential = $this->check_credential(self::$provider);
		$user = new SocialUser($credential);
		$user = $user->get($credential->identity);
		$this->add_view("headers", new View("timeline/user_header", $user));
		$this->set_view("timeline/uploadpic");
		$this->set_output($data);
	}
	
	protected function is_public_feature($identity) {
		$preference = Preference::instance("maggie_features");
		foreach ($preference->entries() as $key => $entry) {
			if ($identity === $key)
				return true;
		}
		return false;
	}

}
?>