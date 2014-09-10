<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Base controller for public pages
 *
 * $Id: public.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    front
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
abstract class Mobile_Controller extends LayoutController {
    const PLATFORM_UNKNOWN = null;
    const PLATFORM_IOS = 'iOS';
    const PLATFORM_ANDROID = 'Android';

    const APP_UNKNOWN = null;
    const APP_WEIXIN = 'weixin';

	protected $allcities = array();
	protected $original_foot_data = null;
	protected $original_page_header = null;
	protected $fromWeixinService = null;
	protected $agentAndroid = false;

	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
		parent::__construct();
		AppLayout_View::set_layout("layouts/mobile");
		$this->positions["headers"]["header"] = "mobile/header";
		$this->positions["footers"]["footer"] = "mobile/footer";

		if($this->get_app() !== self::APP_UNKNOWN)
		{
			$this->body_classes .= ' '.$this->get_app();
		}

		/**
		 * modifier by usky
		 */
		$act = Account::instance();
		$act->checklogin(false);
		$user = $act->loginuser;

		$authorize_wechat_type = intval(Input::instance()->query('authorize_wechat_type',  0));
		$override_cred_check = intval(Input::instance()->query('override_cred_check',  0));

		if ($this->is_weixin()){
			$provider = "weixin";			

			// This parameter is work in wechat only, so far.
			// ovrride || (base credential exists + no credential upgrade request)
			if ($override_cred_check || (!is_null(Session::instance()->get("{$provider}_credential_base",null)) && $authorize_wechat_type != 1)) {
				// This parameter is first priority.
				return;
			}
            //查看url中是否有fromweixinservice 　参数
            $this->fromWeixinService = Input::instance()->get('fromWeixinService',null);
            if($this->fromWeixinService!=null)
                $this->set_js_context('inherited_arg_list',config::ditem("gamerule.inherited_arg_list.weixin",false,null));

            $this->agentAndroid = $this->is_android();
			
			if (!is_null($user)) {
				// Check binding status
				$cred = new Credential($provider, $user);
				if ($cred->is_deleted()) {
					$this->login_wechat($provider,$authorize_wechat_type);
				}
				return;
			}

			if(!is_null(Session::instance()->get("{$provider}_credential",null)) && $authorize_wechat_type == 1){
				$url = url::site("/m/login/$provider?override_cred_check=1&return=".urlencode(Router::$complete_uri));
				url::redirect($url);
			}

			$this->login_wechat($provider,$authorize_wechat_type);
			return;
		}
	}

	protected function login_wechat($provider,$authorize_wechat_type = 0){
		$return = Router::$complete_uri;

		$support_providers = array_keys(config::item("socialapi.providers"));
		log::debug("support providers:".var_export($support_providers, true));
		if (!in_array($provider, $support_providers)) {
			//throw new UKohana_Exception("E_APP_INVALID_PARAMETER", "errors.unsupported");
			return;
		}

		$callback = url::site("social/cb/weixin?return=".$return,false,$provider); // wechat has some bug for urlencoded parameters.
		$query = null;
		if (!empty($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $query);
		}
		log::debug("ready to request $provider credential");		

		$query['scope'] ="snsapi_base";
		if($authorize_wechat_type){
			$query['scope'] ="snsapi_userinfo";			
		}

		$url = Credential::request($provider, $callback, $query,true);

		$url = preg_replace("/client_id/", "appid", $url);

		if (preg_match('/^(.+?)(?:\?([^#]*))?(#.*)?$/', $url, $matches)) {
			parse_str($matches[2], $query);
			ksort($query);
			$url = $matches[1].'?'.http_build_query($query).(isset($matches[3]) ? $matches[3] : "");
		}

		log::debug("request  : ".$url);
		url::redirect($url);
	}

	public function _prepare_footer()
	{
		$view = Event::$data["view"];
		if (!is_null($this->original_foot_data)) {
			$view->original_foot_data = $this->original_foot_data; // add param force=true
		}else{
			$view->original_foot_data = array();
		}
		//$view->gavar = $this->_get_gavar_html();
	}

	public function _prepare_header()
	{
		$view = Event::$data["view"];
		if (!is_null($this->original_page_header)) {
			$view->original_page_header = $this->original_page_header; // add param force=true
		}else{
			$view->original_page_header = '';
		}
	}

    protected function get_app()
    {
        if ( config::item("mobile.allow_func_for_les_autres_application", false, false)) {
            return self::APP_WEIXIN;
        }

        $app = Kohana::user_agent('application');
        switch ($app) {
            case self::APP_WEIXIN:
                return $app;
            default:
                return self::APP_UNKNOWN;
        }
    }

    protected function get_platform()
    {
        $platform = Kohana::user_agent('platform');
        switch ($platform) {
            case self::PLATFORM_IOS:
            case self::PLATFORM_ANDROID:
                return $platform;
            default:
                return self::PLATFORM_UNKNOWN;
        }
    }

    protected function is_ios()
    {
        return $this->get_platform() === self::PLATFORM_IOS;
    }

    protected function is_android()
    {
        return $this->get_platform() === self::PLATFORM_ANDROID;
    }

    protected function is_weixin()
    {
        return $this->get_app() === self::APP_WEIXIN;
    }

}
