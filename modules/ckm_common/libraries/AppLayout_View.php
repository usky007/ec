<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: AppLayout_View.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class AppLayout_View extends Layout_View {

	public static function set_layout($layout)
	{
		Layout_View::$layout = config::cascade($layout, false, $layout);
	}

	public static function get_default_layout()
	{
		return Layout_View::$layout;
	}

	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Kohana_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name = NULL, $data = NULL, $type = NULL)
	{
		parent::__construct($name, $data, $type);
	}

	protected function generate_title($title,$name)
	{
		return "<title>".html::specialchars(Kohana::lang("titles.prefix").$title.Kohana::lang("titles.postfix"))."</title>\n";
	}

	public function resource_path($path) {
		$respath = config::item('core.resource_domain', true, url::base()."res/");
		$path = preg_replace("/(.+?)\.(css|jpg|gif|png|cur)$/i", "\\1".$this->get_ver().".\\2",  $path);
		return $respath.$path;
	}

	public function js_path($path) {
		if(!preg_match('/^http/',$path) )
		{
			$path = url::base()."res/".$path;
		}
		return preg_replace("/(.+?)\.(js)$/i", "\\1".$this->get_ver().".\\2",  $path);
	}

	public function get_ver() {
		return str_replace("ver", ".v", config::item("config.resource_ver", false, ""));
	}
}
?>