<?php
/**
 * This class offers an mapping implementation from chinese word to pinyin spelling.
 * Based on preference, this class use cache, with category "pinyin", to avoid exceeding
 * database query.
 *
 * Usage:
 * PinyinMap::instance()->transform(chinese);
 *
 *
 * $Id: PinyinMap.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    Preference
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class PinyinMap extends Preference {
	const CATEGORY = "pinyin";
	const ENCODING = "UTF-8";

	const OPT_PINYIN  = 0x01;
	const OPT_ABBR    = 0x02;
	const OPT_UCWORD  = 0x04;
	const OPT_POLYPHONE = 0x08;
	const OPT_DEFAULT = 0x05;

	private static $CHINESE_START = 0x3400;

	private static $SUGGESTIONS = array(
		"卡" => "ka",
		"广" => "guang",
		"术" => "shu",
		"汉" => "han",
		"溱" => "qin",
		"娜" => "na",
		"沈" => "shen",
		"漯" => "luo"
	);

	/**
	 * Returns a singleton instance of PinyinMap.
	 *
	 * @return  PinyinMap
	 */
	public static function & instance($driver = null)
	{
		$category = self::CATEGORY;

		if (!isset(Preference::$instances[$category])) {
			// driver not given , create driver by configuration
			if (is_null($driver)) {
				$driver_name = config::item("preference.$category.driver", false,
					config::item("preference.default.driver", true));
				if (!Kohana::auto_load($driver_name))
					throw new Kohana_Exception('core.driver_not_found', $driver_name, "Preference");
				else
					$driver = new $driver_name();
			}
			// cache adapter
			if ($driver instanceof Cache_Driver) {
				$driver = new Preference_Cache_Driver($driver);
			}
			Preference::$instances[$category] = new PinyinMap($category, $driver);
		}

		return Preference::$instances[$category];
	}

	/**
	 * Translate string into pinyin, options are available to return necessary result(s).
	 * Options are combination of following option:
	 * OPT_PINYIN  return pinyin spelling.
	 * OPT_ABBR    return abbreviation form of pinyin spelling.
	 * OPT_UCWORD  return pinyin spelling with letter of each word uppercased.
	 * OPT_POLYPHONE return an array of possible pinyin spelling result if one or more word is(are) polyphone.
	 *
	 * @param string String to translate.
	 * @param int Options, default PinyinMap::OPT_PINYIN | PinyinMap::OPT_UCWORD
	 * @return mixed String or array(pinyin, abbr) or array (pinyins, abbrs) "," separated
	 */
	public function transform($str, $option = self::OPT_DEFAULT) {
		// The param $str should be encoded by UTF-8
		$output_py = ($option & self::OPT_PINYIN) > 0;
		$output_jp = ($option & self::OPT_ABBR) > 0;
		$ucword = ($option & self::OPT_UCWORD) > 0;
		$poly = ($option & self::OPT_POLYPHONE) > 0;

		$chars = unpack("V*", iconv("UTF-8", "UTF-32LE", $str));
		$length = count($chars);

		$words = array(); // separater to words
		$backup = array();
		$polyphones = array();
		$buffer = array();
		for($i = 1; $i <= $length; $i++) {
			$c = iconv("UTF-32LE", "UTF-8", pack("V", $chars[$i]));
			if ($this->is_alpha_numeric($c)) {
				$buffer[] = $c;
				continue;
			}
			else {
				// alpha numeric
				if (!empty($buffer)) {
					$word = implode("", $buffer);
					if ($ucword) $word = ucwords($word);
					array_push($words,$word);
					unset($buffer);
					$buffer = array();
				}
				if (!$this->is_chinese($chars[$i]))
					continue;
			}

			// deal chinese
			$spelling = $poly ? explode(",", $this->get_detail($chars[$i], $ucword)) :
				array($this->get($chars[$i], $ucword));
			if ($spelling[0] == "") {
				continue;
			}
			array_push($words, $spelling[0]);
			if ($poly && count($spelling) > 1) {
				// put elem 0 as indicator.
				array_unshift($spelling, 2);
				$polyphones[count($words) - 1] = $spelling;
			}
		}
		if (!empty($buffer)) {
			$word = implode("", $buffer);
			if ($ucword) $word = ucwords($word);
			array_push($words, $word);
			unset($buffer);
		}

		// No more process if no polyphones detected.
		if (empty($polyphones)) {
			return $this->output($words, $option);
		}

		$outputs = array();
		$word_count = count($words);
		while (!empty($words)) {
			if (count($words) != $word_count) {
				array_push($words, array_pop($backup));
				continue;
			}

			// met a possible solution
			$output = $this->output($words, $option);
			if (!is_array($output))
				$outputs[] = $output;
			else {
				$outputs[0][] = $output[0];
				$outputs[1][] = $output[1];
			}

			for ($i = $word_count - 1; $i >= 0; $i--) {
				$word = array_pop($words);
				if (!isset($polyphones[$i])) {
					array_push($backup, $word);
					continue;
				}

				$polyphone = &$polyphones[$i];
				if ($polyphone[0] != count($polyphone)) {
					array_push($words, $polyphone[$polyphone[0]++]);
					break;
				}
				else {
					$polyphone[0] = 1;
					array_push($backup, $polyphone[$polyphone[0]++]);
				}
			}
		}

		return !is_array($outputs[0]) ? implode(",", $outputs) :
			array(implode(",", $outputs[0]), implode(",", $outputs[1]));
	}

	/**
	 * Get the pinyin for a chinese word, if word is a polyphone, only first or most common match will return.
	 *
	 * @param   string  chinese word
	 * @return  string  pinyin, non chinese word will return an empty string.
	 */
	public function get($cnword, $ucword = false)
	{
		return preg_replace("/,.*/", "", $this->get_detail($cnword, $ucword));
	}

	/**
	 * Get the pinyin for a chinese word, if word is a polyphone, return matchs concatenated with ",";
	 *
	 * @param   string  chinese word
	 * @return  string  pinyin, non chinese word will return an empty string.
	 */
	public function get_detail($cnword, $ucword = false)
	{
		if (is_string($cnword)) {
			$cnword = unpack("V", iconv("UTF-8", "UTF-32LE", $cnword));
			$cnword = $cnword[1];
		}

		// Sanitize the ID
		$group = $this->get_group_index($cnword);

		$cache = &Cache::instance($this->category);
		$map = $cache->get($group);
		if ($map == null) {
			$map_iter = $this->driver->entries("$this->category:$group");
			$map = array();
			foreach ($map_iter as $entry)
				$map[hexdec($entry->key)] = $entry->value;
			$cache->set($group, $map);
		}
		$pinyin = !isset($map[$cnword]) ? "" : $map[$cnword];
		if ($ucword && $pinyin != "") {
			$pinyins = explode(",", $pinyin);
			foreach ($pinyins as $idx => $py) {
				$pinyins[$idx] = ucwords($pinyins[$idx]);
			}
			$pinyin = implode(",", $pinyins);
		}
		return $pinyin;
	}

	/**
	 * Generate the "application/controllers/batch/dict_spelling.sql" sql file.
	 *
	 ********************************************************
	 * Step to generate the sql file.             *
	 *                                                      *
	 * 1 Change the $_multi_prons in this file if needed    *
	 * this step used to process the multi-pronunce problem *
	 *                                                      *
	 * 2 Call the following link:                           *
	 * base_url/batch/spelling_process/generate             *
	 ********************************************************
	 *
	 * Be careful because the old sql file will be overwritten when you run it.
	 *
	 */
	function generate($table,$run=false) {
		$db = & Database::instance();

		$re = $db->query("SELECT * FROM $table where `dic_id` = 'pinyin:15:3400' ");
		$needrun = false;
		if(count($re) < 1)
		{
			$needrun = $run;
		}


		$path = Kohana::find_file ( 'vendor', 'Unihan_Readings', FALSE, "txt" );
		if ($path === FALSE) {
			echo "Unihan_Readings.txt not found under any vendor directory.<br/>";
			return false;
		}
		else {
			$path = dirname($path);
		}

		if (@file_exists("$path/spelling.sql")) {
			echo "spelling.sql generated($path/spelling.sql).<br/>";
			if($needrun)
			{
				$filename = "$path/spelling.sql";
				$handle = fopen($filename, "r");
				$sqlscript = fread($handle, filesize ($filename));
				fclose($handle);
				$sqlscript = str_ireplace('^##*-^',$db->table_prefix(),$sqlscript);				
				$sqlarray = split(";",$sqlscript);
				foreach($sqlarray as $sql)
				{
					$sql = trim($sql);
					if(!empty($sql))
					$db->query($sql);
				}
				echo "excuete spelling.sql finished<br/>";
			}
			return true;
		}

		$f = @fopen("$path/Unihan_Readings.txt","r");
		if ($f === FALSE) {
			echo "Can't open '$path/Unihan_Readings.txt', check your permission settings.<br/>";
			return false;
		}

		$wf = @fopen("$path/spelling.sql","w");
		if ($wf === FALSE) {
			echo "Can't write '$path/spelling.sql', check your permission settings.<br/>";
			return false;
		}

		$key = 0;
		$groupnum = 1000;
		$time = time();

		while (!feof($f)) {
			$line = fgets($f);
			if ( ($line[0] == "U" && ($line[1] == "+"))  ) {
				// Process here
				$line_arr = explode("\t", $line);
				if ( $line_arr[1] == "kMandarin" ) {
					// Transform code to utf8 character.
					$code = substr($line_arr[0],2);
					$utf8Chr = $this->get_UTF8_Char($code);
					// Tolower, and remove sound indicator.
					$pinyins = preg_replace("/[0-9]|\\r|\\n/", "", strtolower($line_arr[2]));
					$pinyins = explode(" ", $pinyins);
					if (array_key_exists($utf8Chr, self::$SUGGESTIONS)) {
						// Rearrange order, put the suggested pinyin as first if any.
						array_unshift($pinyins, self::$SUGGESTIONS[$utf8Chr]);
					}
					// Remove duplication
					$pinyins = array_unique($pinyins);
					$pinyin = implode(",", $pinyins);
					$pinyin = str_replace("Ü", "v", $pinyin);
					$idx = $this->get_group_index($utf8Chr);
					$text = ",\n";
					if ($key++ % $groupnum == 0) {
						$text = ($key == 1) ? "" : ";\n";
						$text .= "insert into $table (`dic_id`, `category`, `val`, `auto`, `timestamp`) values ";
					}
					$text .= "('pinyin:$idx:$code', 'pinyin:$idx', '$pinyin', 1, $time)";
					//////$sql = $sql.$text;
					fwrite($wf, $text);
				}
			}
		}
		fwrite($wf, ";\n");
		fclose($wf);
		fclose($f);
		echo "spelling.sql generated($path/spelling.sql).<br/>";
		if($needrun)
		{
			$filename = "$path/spelling.sql";
			$handle = fopen($filename, "r");
			$sqlscript = fread($handle, filesize ($filename));
			fclose($handle);
			$sqlarray = split(";",$sqlscript);
			foreach($sqlarray as $sql)
			{
				$sql = trim($sql);
					if(!empty($sql))
					$db->query($sql);
			}
			echo "excuete spelling.sql finished";
		}
		return true;
	}

	/**
	 * Translate the unicode string to UTF
	 *
	 * For example: the string "4EBA" will be translate to word "中" with the UTF-8 encoding
	 */
	private function get_UTF8_Char($str) {
		return iconv("UTF-32LE", "UTF-8", pack("V", hexdec($str)));
	}

	private function get_group_index($c) {
		$category_num = config::item("preference.pinyin.groups", true, 25);

		/*
		 * The algorithm is compatible for 32 and 64 bit match
		 *
		 * The 32th bit for some Chinese UTF-8 word is 1, which will be consider negative number in 32 bit match and positive number in 64 bit match
		 * For example, the binary representation for the Chinese UTF-8 word "南" is 0xB02E2BB8, the 32th bit is
		 *
		 * In order to generate the same index both in 32 and 64 bit match, Use the following algorithm
		 * Step:
		 * 1 Check if the 32th bit for the Chinese UTF-8 word is 1 (& with 0x80000000)
		 * 2 If it is 1, calculate the complement for the the Chinese UTF-8 word
		 ********************************************************
		 *   The complement is calculated by:                   *
		 *   a Set the 32th bit to 0 (& 0x7FFFFFFF)            *
		 *   b Calculte the 32 bit anti-code ( 0x7FFFFFFF - )   *
		 *   c Add 1 to get the complement                      *
		 ********************************************************
		 * 3 Caculate the mod
		 */
		if (is_int($c))
			$c = iconv("UTF-32LE", "UTF-8", pack("V", $c));

		$crc32 = crc32($c);
		if ( ($crc32 & 0x80000000) != 0 ) {
			$crc32 = 0x7FFFFFFF - ($crc32 & 0x7FFFFFFF) + 1;
		}
		return ($crc32 % $category_num) + 1;
	}

	private function is_chinese($char) {
		return $char >= self::$CHINESE_START;
	}

	private function is_alpha_numeric($char) {
		return preg_match("/[0-9a-zA-Z]/", $char) > 0;
	}

	private function output($words, $option) {
		$pinyin = ($option & self::OPT_PINYIN) > 0;
		$jianpin = ($option & self::OPT_ABBR) > 0;

		if ($pinyin) {
			$pinyin = implode("", $words);
		}
		if ($jianpin) {
			$jianpin = "";
			foreach ($words as $word)
				$jianpin .= $word[0];
		}
		if ($pinyin !== FALSE && $jianpin !== FALSE)
			return array($pinyin, $jianpin);
		else
			return $pinyin !== FALSE ? $pinyin : $jianpin;
	}

	/**
	 * Deployment scripts
	 */
	public static function deploy() {
		$db = & Database::instance();
		self::instance()->generate("{$db->table_prefix()}Dictionary",true);

	}
}
?>