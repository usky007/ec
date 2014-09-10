<?php
/**
 * Class description.
 *
 * $Id: PinyinMap_Test.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class PinyinMap_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;
	private $pinyinMap = null;

	public function setup() {
		$this->pinyinMap = PinyinMap::instance();
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function general_test() {
		$chars = unpack("V*", iconv("UTF-8", "UTF-32LE", "张倞源Richard"));
		$this->assert_equal(10, count($chars));
		$this->assert_array_key(1, $chars);
	}

	public function get_test() {
		$this->assert_equal("Jing", $this->pinyinMap->get("倞", true));
		$this->assert_equal("jing", $this->pinyinMap->get("倞", false));
		$this->assert_equal("jing", $this->pinyinMap->get("倞"));
	}

	public function get_detail_test() {
		$this->assert_equal("Jing,Liang", $this->pinyinMap->get_detail("倞", true));
		$this->assert_equal("jing,liang", $this->pinyinMap->get_detail("倞", false));
		$this->assert_equal("jing,liang", $this->pinyinMap->get_detail("倞"));
	}

	public function transform_test() {
		$string = "张倞源Richard";
		$expected = "ZhangJingYuanRichard";
		$expected_polyphone = array(
			"ZhangJingYuanRichard,ZhangLiangYuanRichard",
			"ZJYR,ZLYR");

		$this->assert_equal($expected, $this->pinyinMap->transform($string));

		$actual_polyphone = $this->pinyinMap->transform($string,
			PinyinMap::OPT_PINYIN | PinyinMap::OPT_ABBR | PinyinMap::OPT_UCWORD | PinyinMap::OPT_POLYPHONE);
		$this->assert_array($actual_polyphone);
		$this->assert_equal(2, count($actual_polyphone));
		$this->assert_array_key(0, $actual_polyphone);
		$this->assert_array_key(1, $actual_polyphone);
		$this->assert_equal($expected_polyphone[0], $actual_polyphone[0]);
		$this->assert_equal($expected_polyphone[1], $actual_polyphone[1]);

		$string = "仇Richard源倞";
		$expected_polyphone = "chouRichardyuanjing,chouRichardyuanliang,qiuRichardyuanjing,qiuRichardyuanliang";
		$actual_polyphone = $this->pinyinMap->transform($string, PinyinMap::OPT_PINYIN | PinyinMap::OPT_POLYPHONE);
		$this->assert_equal($expected_polyphone, $actual_polyphone);

		$string = "张源";
		$expected_polyphone = "zhangyuan";
		$actual_polyphone = $this->pinyinMap->transform($string, PinyinMap::OPT_PINYIN | PinyinMap::OPT_POLYPHONE);
		$this->assert_equal($expected_polyphone, $actual_polyphone);

		$string = "同仁Z医院Z";
		$expected_polyphone = array("TongRenZYiYuanZ", "TRZYYZ");
		$actual_polyphone = $this->pinyinMap->transform($string, PinyinMap::OPT_PINYIN | PinyinMap::OPT_ABBR | PinyinMap::OPT_UCWORD | PinyinMap::OPT_POLYPHONE);
		$this->assert_array($actual_polyphone);
		$this->assert_equal(2, count($actual_polyphone));
		$this->assert_array_key(0, $actual_polyphone);
		$this->assert_array_key(1, $actual_polyphone);
		$this->assert_equal($expected_polyphone[0], $actual_polyphone[0]);
		$this->assert_equal($expected_polyphone[1], $actual_polyphone[1]);
	}
}
?>