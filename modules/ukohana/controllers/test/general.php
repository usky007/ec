<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Contains general purpose tests for ukohana module.
 *
 * $Id: general.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    ukohana
 * @author     UUTUU
 * @copyright  (c) 2008-2009 UUTUU
 */
class General_Controller extends Controller {

	// Do not allow to run in production
	const ALLOW_PRODUCTION = FALSE;

	public function __construct() {
		Context_Input::instance();

		parent::__construct();
	}

	/**
	 * Displays a list of available tests
	 */
	public function index()
	{
		// Get the methods that are only in this class and not the parent class.
		$tests = array_diff
		(
			get_class_methods(__CLASS__),
			get_class_methods(get_parent_class($this))
		);

		sort($tests);

		echo "<strong>Tests:</strong>\n";
		echo "<ul>\n";

		foreach ($tests as $method)
		{
			if ($method == __FUNCTION__)
				continue;

			echo '<li>'.html::anchor('test/general/'.$method, $method)."</li>\n";
		}

		echo "</ul>\n";
		echo '<p>'.Kohana::lang('core.stats_footer')."</p>\n";
	}

	public function framework()
	{
		$profiler = new Profiler;
	}

	public function context()
	{
		$input = Input::instance();
		echo "context: ";
		var_dump($input->context());
		echo "<br/>rebuild context: ".$input->build_uri();
		echo "<br/>rebuild context, key geouri: ".$input->build_uri("", array("geouri"));
	}

	public function reference()
	{
		foreach (array("array","array_reassign","obj","obj_reassign") as $key => $name)
		{
			$out = null;
			$var = & $this->return_by_ref($out, $key);
			echo "$name test:</br>";
			echo "Is out evaluated:". var_export(isset($out), true) . "</br>";
			echo "Is var and out identical:". var_export($var === $out, true) . "</br>";
			unset($out);
			unset($var);
		}
	}

	public function interface_test()
	{
		$cls = new Test_Class_B();
		echo $cls->F_C();
		echo $cls->F_B();
	}

	public function id($name = NULL)
	{
		if (is_null($name))
			$name = "test_id";
		$num = 5000;

		Benchmark::start('id_gen');
		$db = & Database::instance();
		for ($i = 0; $i < $num; $i++)
			ID_Factory::next_id($name);
		Benchmark::stop('id_gen');
		$result = Benchmark::get('id_gen');
		var_dump($result);
	}

	public function iterator()
	{
		$values = array();
		$it = new MyIterator($values);

		foreach ($it as $a => $b) {
		    print "$a: $b\n";
		}
	}

	public function pinyin()
	{
		$phrases = array ("上海", "MT酒吧", "张倞源");
		$map = &PinyinMap::instance();
		foreach ($phrases as $phrase) {
			echo $phrase." => ";
			var_dump($map->transform($phrase,
				PinyinMap::OPT_PINYIN | PinyinMap::OPT_ABBR | PinyinMap::OPT_UCWORD));
			echo "<br/>";
		}
	}

	private function & return_by_ref(&$out, $type)
	{
		switch ($type)
		{
			case 0:
				$var = array("d"=>5);
				$out = $var;
				$var['d'] = 3;
				return $var;
			case 1:
				$var = array("d"=>5);
				$out = &$var;
				$var['d'] = 3;
				return $var;
			case 2:
				$var = new stdClass();
				$var->d = 5;
				$out = $var;
				$var->d = 3;
				return $var;
			case 3:
				$var = new stdClass();
				$var->d = 5;
				$out = &$var;
				$var->d = 3;
				return $var;
			default:
				$out = new stdClass();
				return $out;
		}
	}
}

interface A {
	public function F_A();
}

interface B extends A {
	public function F_B();
}

interface C {
	public function F_C();
}

class Test_Class_A implements A, C {
	const CON_A = "5";

	public function F_A() {
		return "A<br/>";
	}

	public function F_C() {
		return "C<br/>";
	}
}

class Test_Class_B extends Test_Class_A implements B {
	public function F_B() {
		echo self::CON_A."<br/>";
		return "B<br/>";
	}
}

class MyIterator implements Iterator
{
    private $var = array();

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function rewind() {
        echo "rewinding\n";
        reset($this->var);
    }

    public function current() {
        $var = current($this->var);
        echo "current: $var\n";
        return $var;
    }

    public function key() {
        $var = key($this->var);
        echo "key: $var\n";
        return $var;
    }

    public function next() {
        $var = next($this->var);
        echo "next: $var\n";
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        echo "valid: {$var}\n";
        return $var;
    }
}
?>