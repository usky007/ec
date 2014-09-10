<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: front.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
abstract class Layout_Controller extends Controller {

	// layout setting
	protected $view = null;
	protected $layout = null;
	protected $disables = array();
	protected $positions = null;

	// Default to do auto-rendering
	protected $auto_render = TRUE;

	// Output container
	protected $output = array();
	protected $js_content;
 	protected $templates = array();
 	protected $body_classes = "";

	protected $home_active=false;
	protected $boutique_active = false;
	/**
	 * Template loading and setup routine.
	 */
	public function __construct()
	{
//		Context_Input::instance();

		parent::__construct();

		if ($this->auto_render == TRUE)
		{
			// Render the template immediately after the controller method
			Event::add('system.post_controller_constructor', array($this, '_buffer'));
			Event::add('system.post_controller', array($this, '_render'));
		}

		//$this->js_context = array('base_url'=>url::base());
		$this->js_context["base_url"] = url::base();
		AppLayout_View::set_layout("layouts/layout");
		$this->positions = array(
			"headers" => array("header"=>"default/header"),
			"footers" => array("footer"=>"default/footer"),
			"templates"	=> "",
			"dialogs" => "",
			"tracker" =>  array("tracker"=>"default/tracker")
		);

		// generate body classes
		$engine = Kohana::user_agent('engine');
		if ($engine != null);
			$this->body_classes .= ' '.strtolower($engine);

		$browser = Kohana::user_agent('browser');
		if ($browser != null);
			$this->body_classes .= ' '.strtolower($browser);

		$system = Kohana::user_agent('platform');
		if ($system != null && stripos($system, 'win') !== FALSE) {
			$this->body_classes .= ' win';
		}
		else if ($system != null && stripos($system, 'mac') !== FALSE) {
			$this->body_classes .= ' mac';
		}

		Event::add('layout.prepare_component.footer', array($this, '_prepare_footer'));
		Event::add('layout.prepare_component.tracker', array($this, '_prepare_tracker'));
	}

	public function _buffer()
	{
		ob_start();
	}

	/**
	 * Render the loaded template.
	 */
	public function _render($return = FALSE)
	{
		$buffer = ob_get_clean();
//		$buffer = "";

		$layout = $this->get_layout();
		$layout->body_classes = $this->body_classes;

		foreach ($this->positions as $pos => $components)
		{
			if (!array_key_exists($pos, $this->disables))
			{
				if (!is_array($components))
					$components = array($components);
				foreach ($components as $name => $component) {

					if (is_string($component) && !empty($component)) {
						$component = new View($component);
					}
					$name = is_numeric($name) ? NULL : $name;
					$data = array("controller" => $this, "view" => $component);
					if (!is_null($name)) {
						Event::run("layout.prepare_component.$name", $data);
					}
					$layout->add_component($pos, $component, $name);
				}
			}
			else if (!isset($layout->$pos))
			{
				$layout->$pos = "";
			}
		}
		// set view;
		if (is_string($this->view))
		{
			$layout->content->set_filename($this->view);
			$layout->content->set($this->output);
		}
		else if ($this->view instanceof View)
		{
			$layout->content = $this->view;
			$layout->content->set($this->output);
		}
		else
		{
			$layout->content = "";
		}
		$layout->content = $buffer.$layout->content;



		// js_context
		if (!isset($this->js_context['res_url'])) {
			$this->js_context['res_url'] = $this->get_layout()->resource_path("");
		}
		if (!isset($this->js_context['js_url'])) {
	 		$this->js_context['js_url'] = $this->get_layout()->js_path("");
	 	}
		$layout->set_js_context("js_context=".json_encode($this->js_context));

		// benchmark
		if (!IN_PRODUCTION) {
			$input = Input::instance();
			$benchmark = $input->get("benchmark", false);
			if ($benchmark === false) {
				$benchmark = $input->cookie("benchmark", false);
			}
			if ($benchmark == "1") {
				cookie::set("benchmark", "1");
				$profiler = new Profiler();
			}
			else if ($benchmark !== false) {
				cookie::delete("benchmark");
			}
		}

		// render
		if ($return === TRUE)
			return $layout->render();

		$layout->render(TRUE);
	}

	protected function disable($pos) {
		$this->disables[$pos] = true;
		return $this;
	}

	protected function enable($pos) {
		unset($this->disables[$pos]);
		return $this;
	}

	protected function set_view($path) {
		$this->view = $path;
		return $this;
	}

	protected function set_js_context($key,$value) {
		$this->js_context[$key] =  $value ;
		return $this;
	}

	protected function set_template($template_array) {
		foreach($template_array as $tmp)
		{
			$this->add_template($tmp_id);
		}
		return $this;
	}

	protected function add_template($tmp_id) {
		if(!in_array($tmp_id,$this->templates))
		{
			$this->get_layout()->add_view("templates",
			new View("template/$tmp_id", array("tmp_id"=>$tmp_id)));
		}
		return $this;
	}



	protected function add_dialog($dlg_id) {
		$this->get_layout()->add_view("dialogs",
			new View("dialog/$dlg_id", array("dlg_id"=>$dlg_id)));
		return $this;
	}

	protected function get_layout() {

		if (is_null($this->layout))
			$this->layout = new AppLayout_View();
		return $this->layout;
	}

	/**
	 *
	 */
	protected function set_output($data, $reset = false) {
		if (is_null($data)) {
			$data = array();
		}
		elseif (is_object($data)) {
			$data = get_object_vars($data);
		}
		elseif (!is_array($data)) {
			$data = array("data"=>$data);
		}

		if ($reset) {
			$this->output = $data;
		}
		else {
			$this->output = array_merge($this->output, $data);
		}
		return $this;
	}

	/**
	 * Prepare for rightbar with guide infos
	 */
	public function _prepare_footer()
	{
		$view = Event::$data["view"];

		$html = @$this->_kohana_load_view(config::item('gamerule.static_path', true).'index_top_s.html');
		if (!empty($html)) {
			// remove header
			$html = @preg_replace('/([\s\S]*?)<body[^>]*>(.*)/','$2',$html);
			// remove footer
			$html = @preg_replace('/([\s\S]*?)<\/body>[^<]*<\/html>/','$1',$html);
			// replace resource
			$html = @preg_replace('/\.\/(css|images|js)\//',config::item('gamerule.static_res_url', true).'/$1/', $html);
		}
		$view->mobile = $html;

		//$view->gavar = $this->_get_gavar_html();

	}

	public function _prepare_tracker()
	{

		// $view = Event::$data["view"];

		// $act = Account::instance();
		// $act->checklogin(false);
		// $userObj = $act->user;
		// $html = "";
		// $loguid=0;
		// $useract = "GUEST";
		// // if(!$userObj->is_guest())
		// // {
		// // 	$loguid = $userObj->uid;
		// // 	$useract = $userObj->email;
		// // 	$html .= "_gaq.push(['_setCustomVar', 1,'sessionid','".$userObj->uid."',1]);";
		// // }

		// $logip = long2ip($loguid);
		// Slog::instance()->add(null,null,$logip,$useract)->render();
		// $view->gavar = $html;

	}




} // End ServiceController
?>