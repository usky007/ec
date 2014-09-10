<?php
/**
 * Class description.
 *
 * $Id: Timeline_Test.php 3 2011-06-07 03:00:48Z zhangjyr $
 *
 * @package    timeline
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Timeline_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;

	public function setup() {
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
	}

	public function detect_timeline_type_test() {
		$keyword = "1234567";
		$expected_type = Timeline::TYPE_USERID;
		$expected_keyword = "1234567";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "user id detection test");
		$this->assert_equal($expected_keyword, $keyword, "user id detection test");

		$keyword = "Testcase";
		$expected_type = Timeline::TYPE_TOPIC;
		$expected_keyword = "Testcase";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "plain keyword detection test");
		$this->assert_equal($expected_keyword, $keyword, "plain keyword detection test");

		$keyword = "@Tianium";
		$expected_type = Timeline::TYPE_USERNAME;
		$expected_keyword = "Tianium";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "user detection test");
		$this->assert_equal($expected_keyword, $keyword, "user detection test");

		$keyword = "@123456";
		$expected_type = Timeline::TYPE_USERNAME;
		$expected_keyword = "123456";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "user with numeric-name detection test");
		$this->assert_equal($expected_keyword, $keyword, "user with numeric-name detection test");

		$keyword = "#Tianium#";
		$expected_type = Timeline::TYPE_TOPIC;
		$expected_keyword = "Tianium";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "topic detection test");
		$this->assert_equal($expected_keyword, $keyword, "topic detection test");

		$keyword = "#Tianium";
		$expected_type = Timeline::TYPE_TOPIC;
		$expected_keyword = "Tianium";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "unclosed syntax topic detection test");
		$this->assert_equal($expected_keyword, $keyword, "unclosed syntax topic detection test");

		$keyword = "#12345#";
		$expected_type = Timeline::TYPE_TOPIC;
		$expected_keyword = "12345";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "topic with numeric-char detection test");
		$this->assert_equal($expected_keyword, $keyword, "topic with numeric-char detection test");

		$keyword = "12345#";
		$expected_type = Timeline::TYPE_TOPIC;
		$expected_keyword = "12345#";
		$this->assert_equal($expected_type, Timeline::detect_timeline_type($keyword), "err-form topic detection test");
		$this->assert_equal($expected_keyword, $keyword, "err-form topic detection test");
	}
}
?>