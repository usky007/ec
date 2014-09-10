<?php
/**
 * Maggie ajax services.
 *
 * $Id: ajax.php 55 2011-07-27 11:34:50Z zhangjyr $
 *
 * @package    maggie
 * @author     UUTUU Tianium
 * @copyright  (c) 2008-2010 UUTUU
 */
class Ajax_Controller extends ServiceController {
	protected static $provider = "sina";
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function comments($status_id){
		$credential = $this->check_credential();
		
		$page = Input::instance()->get("page",1,true);
		$count = Input::instance()->get("count",5,true);
		
		$comments = new AuthorizedRestObject($credential, "/statuses/comments.json", self::$provider);
		$comments = $comments->get(array("id"=>$status_id, "page"=>$page,'count'=>$count));
		$c_count = count($comments);
		
		//combination common
		if ($c_count == 0) {
			$html = '<div class="comment">没有更多评论了</div>';
		}
		else {
			$html = '';
			foreach ($comments as $comment) {
				$comment['status_id'] = $status_id;
				$html .= new View("timeline/comment", $comment);
			}
		}
		
		if (Input::instance()->get("debug", 0, true) == 1) {
			echo $html;
			return;
		}
		
		$this->set_output(array("comments" => array($html, '@count' => $c_count,'@page' => $page)));
	}
	
	public function uploadpic()
	{
		$full_url="";
		$html="";
		$field="attach";
		if(!empty($_FILES) && !empty($_FILES[$field]['name']) ) 
		{
			$url = "";
			$upload = new Upload();
			//$upload->set_max_filesize(10*1024*1024);
			$upload->set_allowed_types(config::item('upload.allowed_types'));
			
			//$html ='<script>window.parent.document.getElementById("divUploadingPic").style.display="none";';
//			$html .= '<script>function delAttach(){window.parent.document.getElementById("hidpicurl").value="";';
//			$html .= 'window.parent.document.getElementById("div_uploadpic").style.display = "none";';
//			$html .= 'window.parent.document.getElementById("div_uploadpic").innerHTML = "";}';
//			$html .= '</script>';
			//$html = "";
			
			try{
				$upload->do_upload($field,'maggie',$url);
			}
			catch(exception $ex)
			{
				$html = '<div>上传失败!</div>' ;
				$html = '<script>window.parent.document.getElementById("div_uploadpic").value="'.$html.'";</script>';
				$html .= '<script>window.parent.document.getElementById("hidpicurl").value="";</script>';
				return;
			}
				
	 		if(!empty($url))
	 		{
	 			$full_url = format::get_local_storage_url($url,'photo');
	 			$pichtml = '<div><img class="uploadpic" src="'.$full_url.'" /><a href="javascript:delAttach();">[删除]</a></div>' ;
	 			$html .= '<script>window.parent.document.getElementById("div_uploadpic").innerHTML=\''.$pichtml.'\';</script>';
				$html .= '<script>window.parent.document.getElementById("hidpicurl").value="'.$full_url.'";</script>';
	 		}
	 		else
	 		{
	 			$html .= '<div>上传失败!</div>' ;
	 			$html = '<script>window.parent.document.getElementById("div_uploadpic").value="'.$html.'";</script>';
	 			$html .= '<script>window.parent.document.getElementById("hidpicurl").value="";</script>';
	 		}
			
			
		}
		else
		{
			$html .= '<div>无法获取上传文件,上传失败!</div>' ;
			$html = '<script>window.parent.document.getElementById("div_uploadpic").value="'.$html.'";</script>';
			$html .= '<script>window.parent.document.getElementById("hidpicurl").value="";</script>';
		}
		echo $html ;
		exit;
	}
	public function comment($status_id) {
		$credential = $this->check_credential();
		
		$validation = new Validation($_POST);
		$validation->pre_filter("trim", TRUE);
		$validation->add_rules("status", "required", 'length[1,140]');
		$validation->add_rules("comment_ori", 'chars[0,1]');
		if (!$validation->validate()) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$comment = new AuthorizedRestObject($credential, "/statuses/comment.json", self::$provider);
		$params = array("id"=>$status_id, "comment"=>urlencode($validation['status']), "comment_ori" => 1);

		if (!empty($validation['comment_ori'])) {
			$params['comment_ori'] = $validation['comment_ori'];
		}
		
		$comment = $comment->create($params);
		$html = new View("timeline/comment", $comment);
		
		if (Input::instance()->get("debug", 0, true) == 1) {
			echo $html;
			return;
		}
		
		$this->set_output(array("comment" => (string)$html));
		
		if (Input::instance()->post("repost", false, true)) {
			$this->repost($status_id);
		}
	}
	
	public function unread(){
		$credential = $this->check_credential();
		$last_status_id = intval(Input::instance()->get("last_status_id", 0, true));
		
		if ($last_status_id === 0) {
			$params['with_new_status'] = 0;
		}
		else {
			$cache = Cache::instance("maggie_unread_lock");
			if (!is_null($last_call = $cache->get($credential->user->uid))) {
				$last_call['yield'] = true;
				
				if (!isset($last_call['since_id'])) {
					
				}
				else if ($last_status_id < $last_call['since_id']) {
					$last_call['new_status']++;
					$last_call['estimate'] = true;
				}
				else if ($last_status_id > $last_call['since_id']) {
					unset($last_call);
				}
				
				if (isset($last_call)) {
					$this->set_output(array('unread'=>$last_call));
					return;
				}
			}
			
			$params['with_new_status'] = 1;
			$params['since_id'] = $last_status_id;
		}
		
		$unread = new AuthorizedRestObject($credential, "/statuses/unread.json", self::$provider);
		$status = $unread->get($params);
		
		if ($last_status_id !== 0) {
			$status['since_id'] = $last_status_id;
			
			$lifetime = 60; // Or call rate_limit_status for accurate data.
			$status['timeout'] = $lifetime;
			$cache->set($credential->user->uid, $status, NULL, $lifetime);
		}
		
		$this->set_output(array('unread'=>$status));
	}
	
	public function post(){
		$credential = $this->check_credential(self::$provider);
		$params['status'] = Input::instance()->post("status");
		
		
		/*if (!empty($_FILES) && !empty($_FILES['uploadfile']['name']) ) {
				$tempFile = $_FILES['uploadfile']['tmp_name'];
				$targetPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
				$targetFile =  str_replace('//','/',$targetPath) . $_FILES['uploadfile']['name'];
			
				move_uploaded_file($tempFile,$targetFile);
				$params['annotations'] = json_encode($targetFile);
		}*/
		$comment="";
		$targetFile = Input::instance()->post("hidpicurl");
		
		$url = $targetFile==""?'/statuses/update.json':'/statuses/upload.json';
		//$url = '/statuses.json';
		//$repost = new AuthorizedRestObject($credential, $url, self::$provider);
		
		$repost = new SocialStatus($credential,self::$provider);
		
		if($targetFile != "")
		{
			//$params['annotations'] = $targetFile;	
			$params['pic'] = '@'.$targetFile;	
			$comment = $repost->upload( $params ,true);
		}
		else
		{
			$comment = $repost->update( $params );
		}
		$cache_obj = Timelinecaches_Model::from_status(self::$provider, $comment);
		$cache_obj->save();
		$comment['tcid'] = $cache_obj->tcid;
		
 		//var_dump($comment);exit;
 
		
		$html = new Timeline_View(array($comment));
		
		if (Input::instance()->get("debug", 0, true) == 1) {
			echo $html;
			return;
		}
		
		$this->set_output(array("status" => (string)$html));
	}
	
	
	
	
	public function repost() {
 
		$status_id =isset($_POST['sid'])?$status_id = $_POST['sid']:false;
 
		if($status_id === false)
		{
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
			return false;
		}
		$credential = $this->check_credential();
		
		$validation = new Validation($_POST);
		$validation->pre_filter("trim", TRUE);
		$validation->add_rules("status", 'length[0,140]');
		$validation->add_rules("is_comment", 'numeric');
		
		if ($validation->submitted() && !$validation->validate()) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$repost = new AuthorizedRestObject($credential, "/statuses/repost.json", self::$provider);
		$params = array("id"=>$status_id);
		
		
		
		if (isset($validation['status'])) {
			$params['status'] = urlencode($validation['status']);
		}
		if (isset($validation['is_comment'])) {
			$params['is_comment'] = $validation['is_comment'];
		}
		$comment = $repost->create($params);
		
		$cache_obj = Timelinecaches_Model::from_status(self::$provider, $comment);
		$cache_obj->save();
		$comment['tcid'] = $cache_obj->tcid;
		
		
		$html = new Timeline_View(array($comment));
		
		if (Input::instance()->get("debug", 0, true) == 1) {
			echo $html;
			return;
		}
		
		$this->set_output(array("status" => (string)$html));
	}
	
	public function url_decode(){
		$tcid = Input::instance()->get("tcid", false, true);
		$url = Input::instance()->get("url", false, true);
		if ($url === false) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		if ($tcid !== false) {
			$cache_obj = new Timelinecaches_Model($tcid);
			if ($cache_obj->find()->loaded() && !empty($cache_obj->mediaInfo)) {
				$data = $cache_obj->mediaInfo;
				$data['type'] = $cache_obj->mediaType;
				$data['width'] = $cache_obj->mediaWidth;
				$data['height'] = $cache_obj->mediaHeight;
				$this->set_output(array('media'=>$data));
				return;
			}
		}
		
		// get real address
		$headers = get_headers($url);
		for ($i = count($headers) - 1; $i >= 0; $i--) {
			if (preg_match('/^Location:\s*(.*)$/', $headers[$i], $match)) {
				$new_url = $match[1];
				break;
			}
		}
		if (!isset($new_url)) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		log::debug("Decoded url:$new_url($url)");
		
		$data = Video::get_info($new_url);
		if (!is_null($data)) {
			$data['type'] = Timelinecaches_Model::MEDIA_TYPE_VIDEO;
		}
		else {
			$data['type'] = Timelinecaches_Model::MEDIA_TYPE_HTML;
		}
		$data['url'] = $new_url;

		if (isset($cache_obj) && $cache_obj->loaded()) {
			$cache_obj->mediaType = $data['type'];
			$cache_obj->mediaInfo = $data;
			$cache_obj->save();
			
			if (!empty($cache_obj->rtInfo)) {
				$rt_obj = new Timelinecaches_Model($cache_obj->rtTcid);
				if ($rt_obj->find()->loaded()) {
					$rt_obj->mediaType = $data['type'];
					$rt_obj->mediaInfo = $data;
					$rt_obj->save();
				}
			}
		}
		
		$this->set_output(array('media'=>$data));
	}
	
	public function follow() {
		$credential = $this->check_credential();
		
		$identity = Input::instance()->post("identity", false, true);
		if ($identity === false) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$friendship = new AuthorizedRestObject($credential, "Friendship", self::$provider);
		$user = $friendship->create(array("user_id" => $identity));
		
		$this->set_output(array('cssClass'=>"followed"));
	}
	
	public function unfollow() {
		$credential = $this->check_credential();
		
		$identity = Input::instance()->post("identity", false, true);
		if ($identity === false) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$friendship = new AuthorizedRestObject($credential, "Friendship", self::$provider);
		$user = $friendship->delete(array("user_id" => $identity));
		
		$this->set_output(array('cssClass'=>"followit"));
	}
	
	public function mark() {
		$credential = $this->check_credential();
		
		$identity = Input::instance()->post("identity", false, true);
		if ($identity === false) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$profile = new SocialUser($credential);
		$profile = $profile->get($identity);
		
		$pref = Preference::instance($credential->identity, new Preference_Dictionary_Driver(new Favorite_Timelines_Model()));
		$pref->set($identity, time());
		
		$userinfo = Socialusers_Model::from_profile(self::$provider, $profile);
		if (!$userinfo->saved()) {
			$userinfo->save();
		}
		
		$this->set_output(array('cssClass'=>"followed"));
	}
	
	public function unmark() {
		$credential = $this->check_credential();
		
		$identity = Input::instance()->post("identity", false, true);
		if ($identity === false) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		
		$pref = Preference::instance($credential->identity, new Preference_Dictionary_Driver(new Favorite_Timelines_Model()));
		$pref->delete($identity, time());
		
		$this->set_output(array('cssClass'=>"followit"));
	}
	
	public function favo_refresh($suid) {
		$credential = $this->check_credential();
		$interval = 60;
		
		$userinfo = new Socialusers_Model();
		if (!$userinfo->find($suid)->loaded()) {
			throw new UKohana_Exception(E_MICO_INVALID_PARAMETER, "errors.invalid_parameter");
		}
		if (time() < ($userinfo->updated + $interval)) {
			$this->set_output(array('favorite'=>array(
				"suid"	   => $userinfo->suid,
				"identity" => $userinfo->srcId,
				"username" => $userinfo->username,
				"photo"	   => isset($userinfo->recentMediaLink) ? $userinfo->recentMediaLink : $userinfo->avatar,
				"ttl"	   => time() - $userinfo->updated
			)));
			return;
		}
		
		// update social user info
		$timeline = Timeline::get_timeline($credential, $userinfo->srcId);
		if (isset($userinfo->lastStatusId)) {
			$timeline->since($userinfo->lastStatusId);
		}
		foreach ($timeline as $status) {
			if (!empty($status['bmiddle_pic'])) {
				$userinfo->recentMediaLink = $status['bmiddle_pic'];
				break;
			}
		}
		$userinfo->lastStatusId = (string)$timeline[0]['id'];
		$userinfo->save();
		
		$this->set_output(array('favorite'=>array(
			"suid"	   => $userinfo->suid,
			"identity" => $userinfo->srcId,
			"username" => $userinfo->username,
			"photo"	   => isset($userinfo->recentMediaLink) ? $userinfo->recentMediaLink : $userinfo->avatar,
			"ttl"	   => 0
		)));
	}
	
	protected function check_credential() {
		$act = Account::instance();
		if (!$act->checklogin(FALSE)) {
			throw new UKohana_Exception(E_MICO_AUTH_FAILED, "errors.not_login");
		}
		
		$credential = new Credential(self::$provider, $act->loginuser);
		if ($credential->is_valid()) {
			return $credential;
		} else {
			throw new UKohana_Exception(E_MICO_CREDENTIAL_NOT_FOUND, "errors.credential_not_found");
		}
	}
	
}
?>