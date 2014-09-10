<?php
/**
 * Class description.
 *
 * $Id: SinaCall_Test.php 2654 2011-06-21 02:34:29Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class AuthorizedObject_Test extends Unit_Test_Case {

	// Disable this Test class?
	const DISABLED = FALSE;

	public $setup_has_run = FALSE;
	private $user = null;
	private $credential = null;

	public function setup() {
		$model = new Credential_Model();
		$model->token = "ABCD";
		$model->secret = "EFGH";
		$model->provider = "test";
		$model->status = 0;
		
		$user = new User_Model();
		$user->uid = 1;
		$user->email = "zhangjyr@qq.com";
		
		$this->credential = new Credential($model, $user);
		$this->setup_has_run = TRUE;
	}

	public function setup_test() {
		$this->assert_true_strict($this->setup_has_run);
		$this->assert_true_strict($this->credential->is_valid());
		$this->assert_equal("test", $this->credential->provider);
	}

	public function testcall_test() {
		$testapi = new TestAPI($this->credential);
		$result = $testapi->test();
		
		$this->assert_same($testapi, $result, "Test caller get returned");
		
		$this->assert_true_strict(isset($result['id']), "Test simple offset access(isset).");
		$this->assert_true_strict(isset($result['status.id']), "Test cascaded offset access(isset).");
		$this->assert_true_strict(isset($result['records[0].id']), "Test cascaded offset with array access(isset).");
		
		$this->assert_equal(123, $result['id'], "Test simple offset access(get)");	
		$this->assert_equal(456, $result['status.id'], "Test cascaded offset access(get)");
		$this->assert_equal(789, $result['records[0].id'], "Test cascaded offset with array access(get)");
		
		$testval1 = "TEST1";
		$testval2 = "TEST2";
		$testval3 = "TEST3";
		$testfield1 = md5(uniqid(rand(), true));
		$testfield2 = md5(uniqid(rand(), true));
		$this->assert_false_strict(isset($result[$testfield1]), "Confirm test field1 not exist.");
		$this->assert_false_strict(isset($result[$testfield2]), "Confirm test field2 not exist.");
		$result[$testfield1] = $testval1;
		$result["{$testfield2}.{$testfield1}"] = $testval2;
		$result["{$testfield2}[0].{$testfield1}"] = $testval3;
		
		$this->assert_equal($testval1, $result[$testfield1], "Test simple offset access(set).");
		$this->assert_equal($testval2, $result["{$testfield2}.{$testfield1}"], "Test cascaded offset access(set).");
		$this->assert_equal($testval3, $result["{$testfield2}[0].{$testfield1}"], "Test cascaded offset with array access(set).");
		
		unset($result[$testfield1]);
		unset($result["{$testfield2}.{$testfield1}"]);
		unset($result["{$testfield2}[0]"]);
		$this->assert_false_strict(isset($result[$testfield1]), "Test simple offset access(unset).");
		$this->assert_false_strict(isset($result["{$testfield2}.{$testfield1}"]), "Test cascaded offset access(unset).");
		$this->assert_false_strict(isset($result["{$testfield2}[0].{$testfield1}"]), "Test cascaded offset with array access(unset).");
		$this->assert_false_strict(isset($result["{$testfield2}[0]"]), "Test cascaded offset with array(root) access(unset).");
	}
	
	public function putcall_test() {
		$testapi = new TestAPI($this->credential);
		
		$result = $testapi->put_test();
		$this->assert_same($testapi, $result, "Test caller get returned");
		$this->assert_true_strict(isset($result['id']), "Test existence of id field.");
		$this->assert_true_strict(isset($result['method']), "Test existence of method field.");
		$this->assert_equal(963, $result['id'], "Test value of id field.");	
		$this->assert_equal("put", $result['method'], "Test value of method field.");
		
		$result = $testapi->put_test(true);
		$this->assert_same($testapi, $result, "Test caller get returned(multipart).");
		if (!$result['success']) {
			$this->assert_true_strict(isset($result['errcode']), "Test existence of errcode of multipart call on fail.");
			$this->assert_true_strict(isset($result['message']), "Test existence of message of multipart call on fail.");
			$this->assert_true_strict($result['success'], "Test result of multipart call:{$result['message']}({$result['errcode']})");
		}
		else {
			$this->assert_true_strict(isset($result['id']), "Test existence of id field of multipart call.");
			$this->assert_true_strict(isset($result['method']), "Test existence of method field of multipart call.");
			$this->assert_equal(963, $result['id'], "Test value of id field of multipart call.");	
			$this->assert_equal("put", $result['method'], "Test value of method field of multipart call.");
		}
	}
}

class TestAPI extends AuthorizedObject 
{
	public function __construct(Credential $cred, $gateway = NULL)
	{
		parent::__construct($cred, $gateway);
	}

	public function test() {
		$this->_object = $this->http_get("get_test");
		return $this;
	}
	
	public function put_test($multi = false) {
		$this->_object = $this->http_put("put_test", array("id" => 963), $multi);
		return $this;
	}
}
?>