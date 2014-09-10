<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Class description.
 *
 * $Id: Layout_View.php 330 2011-06-21 09:46:50Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2009 UUTUU
 */
class Layout_View extends View {
	protected static $layout = "layouts/default";

	protected $kohana_local_data = array(
		"title" => null,
		"keywords" => null,
		"description" => null,
		"css" => null,
		"js" => null
	);

	private $componentSets = array();

	public static function set_layout($layout)
	{
		self::$layout = $layout;
	}

	public static function get_layout()
	{
		return self::$layout;
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
		if ($name instanceof View) {
			$this->content = $name;
		}
		else {
			$this->content = new View($name, $data, $type);
		}
		View::set_global("layout", $this);
		parent::__construct(self::$layout);
	}

	public function set_title($title)
	{
		return $this->add_component("title", $title, 'title');
	}

	public function set_keywords($keywords)
	{
		return $this->add_component("keywords", $keywords, 'keywords');
	}

	public function set_description($description)
	{
		return $this->add_component("description", $description, 'description');
	}

	public function set_js_context($js)
	{
		return $this->add_component("js", $js, 'inline:js_context' , -100);
	}

	public function add_js($js, $inline = false,$properties=array())
	{
		if (!$inline && !preg_match('/(^http)|(\.js(\?.*)?$)/', $js)  ) {
			$from_config = config::cascade($js);
			if (!is_null($from_config)) {
				$js = $from_config;
			}
		}
		if(!$inline)
		{
			if(!empty($properties))
			{
				$component = $properties;
				$component['src'] = $js;
				$js = $component;
			}
		}
		return $this->add_component("js", $js, $inline ? 'inline:'.md5($js) : null);
	}

	public function add_css($css ,$inline=false)
	{
		if (!preg_match('/\.css$/', $css) && (!$inline) ) {
			$from_config = config::cascade($css);
			if (!is_null($from_config)) {
				$css = $from_config;
			}
		}
		return $this->add_component("css", $css, $inline ? 'inline:'.md5($css) : null);
		//return $this->add_component("css", $css);
	}

	public function add_view($pos, View $view, $name = null, $order = 0)
	{
		return $this->add_component($pos, $view, $order);
	}

	public function add_component($pos, $component, $name = null, $order = 0)
	{
		if (!isset($this->componentSets[$pos])) {
			$this->componentSets[$pos] = array();
			if (isset($this->kohana_local_data[$pos]) &&
				!empty($this->kohana_local_data[$pos])) {
				$this->kohana_local_data[$pos] = array($this->kohana_local_data[$pos]);
				$this->componentSets[$pos][] = 0;
			}
		}
		$key = empty($name) ? count($this->componentSets[$pos]) : $name;
		$this->kohana_local_data[$pos][$key] = $component;
		$this->componentSets[$pos][$key] = $order ? $order : count($this->componentSets[$pos]);
		return $this;
	}

	public function get_component($pos, $name) {
		if (!isset($this->kohana_local_data[$pos]) ||
			!isset($this->kohana_local_data[$pos][$name]))
			return NULL;
		return $this->kohana_local_data[$pos][$name];
	}

	/**
	 * Magically sets a view variable.
	 *
	 * @param   string   variable key
	 * @param   string   variable value
	 * @return  void
	 */
	public function __set($key, $value)
	{
		if (array_key_exists($key, $this->componentSets))
			unset($this->componentSets[$key]);
		$func = "set_$key";
		if (is_callable(array($this, $func)))
			$this->$func($value);
		else
			parent::__set($key, $value);
	}

	/**
	 * Renders a view.
	 *
	 * @param   boolean   set to TRUE to echo the output instead of returning it
	 * @param   callback  special renderer to pass the output through
	 * @return  string    if print is FALSE
	 * @return  void      if print is TRUE
	 */
	public function render($print = FALSE, $renderer = FALSE)
	{
		////////$has_js_context = false;
		foreach ($this->componentSets as $pos => $order) {
			// ensure component's existence
			if (empty($order)) {
				continue;
			}
			$values = $this->kohana_local_data[$pos];

			// sort according to $order suggested
			array_multisort($order, SORT_ASC, SORT_NUMERIC, $values);

			$fragment = "";
			$func = "generate_$pos";
			if (is_callable(array($this, $func))) {
				if (is_array($values)) {
					foreach ($values as $name=>$val)
					{
						$name = is_int($name)?null:$name;
						$fragment .= $this->$func($val,$name);
					}
				}
				else {
					$fragment .= $this->$func($values, NULL);
				}
			}
			else {
				// use default "toString" convention.
				foreach ($values as $val)
					$fragment .= $val;
			}
			$this->kohana_local_data[$pos] = $fragment;
		}
		return parent::render($print, $renderer);
	}

	protected function generate_title($title,$name)
	{
		return "<title>".html::specialchars($title)."</title>\n";
	}

	protected function generate_keywords($keywords,$name)
	{
		if (empty($keywords))
			return "";

		return "<meta name=\"keywords\" content=\"".html::specialchars($keywords)."\" />\n";
	}

	protected function generate_description($description,$name)
	{
		if (empty($description))
			return "";

		return "<meta name=\"description\" content=\"".html::specialchars($description)."\" />\n";
	}

	protected function generate_css($css,$name)
	{
		if (empty($css))
			return "";

		$media = "all";
		if (is_array($css))
		{
			$media = $css[1];
			$css = $css[0];
		}
		if(strpos($name, "inline:") === 0)
		{
			$txt = "<style type=\"text/css\">$css</style>\n";
			return $txt;
		}
		else
		{
			return "<link href=\"{$this->resource_path($css)}\" rel=\"stylesheet\" type=\"text/css\" media=\"{$media}\" />\n";
		}

	}

	protected function generate_js($js,$name)
	{
		if (empty($js))
			return "";
		if(is_array($js))
		{
			$js_src = $js['src'];
			$props = "";
			foreach($js as $key=>$val)
			{
				if($key != 'src')
				{
					$props .= " $key=\"$val\"";
				}
			}
			if(preg_match('/^http/', $js_src) && !preg_match('/^https?:\/\/'.$_SERVER['HTTP_HOST'].'/', $js_src)  )
			{
				return "<script type=\"text/javascript\" src=\"$js_src\" $props ></script>\n";
			}
			else
			{
				return "<script type=\"text/javascript\" src=\"{$this->js_path($js_src)}\" $props ></script>\n";
			}
		}
		if(strpos($name, "inline:") === 0)
		{
			$txt = "<script type=\"text/javascript\">$js</script>\n";
			return $txt;
		}
		elseif(preg_match('/^http/', $js) && !preg_match('/^https?:\/\/'.$_SERVER['HTTP_HOST'].'/', $js)  )
		{
			return "<script type=\"text/javascript\" src=\"$js\" ></script>\n";
		}
		else
		{
			return "<script type=\"text/javascript\" src=\"{$this->js_path($js)}\" ></script>\n";
		}

	}

	public function resource_path($path) {
		return $path;
	}

	public function js_path($path) {
		return $path;
	}
}
?>