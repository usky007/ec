<?php
	class Index_Controller extends LayoutController
	{
        const PREFERENCE_KEY = 'WEIBO';
        const PREFERENCE_MENTION_SINCE_ID = 'MENTION';
        const PREFERENCE_USER_SINCE_ID = 'USER';
        const RIGHT_DB_STATUS = 3;

		public function index()
		{
            /*$value = '"\u5206\u4eab\u56fe\u7247"';
            $tmp = '分享图片';
            if(preg_match('/'.$value.'/', $tmp)){
                echo 11;
            }else{
                echo 22;
            }
            exit;*/

            /*$tm = new Camp_Standard_Tweet_Model('test');
            $tm->where('tweet_id', 2313)->find();
            if(!$tm->loaded){
                echo 1;
            }else{
                echo 2;
            }

            echo '<br/>',2321312,'sdsd';exit;*/

         /*   $pfc = Preference::instance('weibo');
            $since_id = $pfc->get('USER');
            $since_id = isset($since_id) ? $since_id : 0;
            var_dump($since_id);exit;*/

            $camp_list = array();

            $camp = new Camp_Model();
            $list = $camp->find_all()->as_array();
            foreach($list as $camp){
                if($camp->status==1){
                    //$camp_name = $l->name;
                    //var_dump($l->camp_id,$l->name);
                    if($camp->db_status == self::RIGHT_DB_STATUS){
                        $camp_list[] = $camp;
                        /*$category = $camp->category;
                        var_dump($category);
                        $category = json_decode($category, true);
                        var_dump($category);*/
                    }
                }
            }

            if(!empty($camp_list)){
                $wb = new Weibo();
                $wb->getTweetStandard($camp_list);
            }else{
                echo '没有开启任何活动';
            }



            exit;
	    }
    }
?>