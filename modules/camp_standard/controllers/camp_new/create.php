<?php
	class Create_Controller extends LayoutController
	{
		public function index()
		{
            $pfc = Preference::instance('weibo');
            $since_id = $pfc->get('USER');
            $since_id = isset($since_id) ? $since_id : 0;
            var_dump($since_id);exit;
            $camp = new Camp_Model();
            $list = $camp->find_all()->as_array();
            foreach($list as $camp){
                if($camp->status==1){
                    //$camp_name = $l->name;
                    //var_dump($l->camp_id,$l->name);
                    $this->createTable($camp);

                }
            }
            exit;
	    }

        protected function createTable($camp){
            $db = & Database::instance();
            $name = $camp->name;
            if($camp->db_status==0){
                try {
                    $result = $db->query("
                        CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_{$name}_Tweet` (
                            `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
                            `pic` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博图片',
                            `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT  '微博内容',
                            `uid` BIGINT NOT NULL COMMENT  '微博作者新浪id',
                            `name` VARCHAR( 200 ) NOT NULL COMMENT  '作者名字',
                            `avatar` VARCHAR( 200 ) NOT NULL COMMENT  '作者头像',
                            `link` VARCHAR( 200 ) NOT NULL COMMENT  '作者链接',
                            `heat` INT( 11 ) NOT NULL COMMENT  '热度',
                            `source` VARCHAR( 50 ) NOT NULL COMMENT  '来源',
                            `created` INT(11) NOT NULL COMMENT  '抓取时间',
                            `updated` INT NOT NULL COMMENT  '最后更新时间',
                            PRIMARY KEY (  `tweet_id` )
                        ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博表';
                        ");
                    echo  'Camp_'.$name.'_Tweet created.<br />';
                    $camp->db_status = 1;
                    $camp->save();
                }
                catch (Kohana_Database_Exception $ex)
                {
                    echo "Error occurs on craeted Table Camp_".$name."_Tweet:{$ex->getMessage()}<br />";
                }
            }

            if($camp->db_status==1){
                try {
                    $result = $db->query("
                                CREATE TABLE IF NOT EXISTS `{$db->table_prefix()}Camp_{$name}_TweetComments` (
                                 `tweetComments_id` BIGINT(20) NOT NULL COMMENT  '微博评论id',
                                 `tweet_id` BIGINT(20) NOT NULL COMMENT  '微博id',
                                 `content` TEXT NOT NULL COMMENT  '评论内容',
                                 `name` VARCHAR( 200 ) NOT NULL COMMENT  '评论者姓名',
                                 `avatar` VARCHAR( 200 ) NOT NULL COMMENT  '评论者头像',
                                 `link` VARCHAR( 200 ) NOT NULL COMMENT  '评论者链接',
                                 `created` INT NOT NULL ,
                                 PRIMARY KEY (  `tweetComments_id` ),
                                 KEY `tweet_id` (`tweet_id`)
                                ) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT =  '微博评论表';
                            ");
                    echo  'Camp_'.$name.'_TweetComments created.<br />';
                    $camp->db_status = 2;
                    $camp->save();
                }
                catch (Kohana_Database_Exception $ex)
                {
                    echo "Error occurs on craeted Table Camp_".$name."_TweetComments:{$ex->getMessage()}<br />";
                }
            }

            if($camp->db_status==2){
                try {
                    $result = $db->query("
                            CREATE TABLE  IF NOT EXISTS  `{$db->table_prefix()}Camp_{$name}_Category` (
                              `id` int(11) NOT NULL COMMENT 'id',
                              `tweet_id` bigint(20) NOT NULL,
                              `keyword` varchar(20) NOT NULL,
                              PRIMARY KEY (`id`),
                              KEY `tweet_id_keyword` (`keyword`, `tweet_id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                            ");
                    echo  'Camp_'.$name.'_Category created.<br />';
                    $camp->db_status = 3;
                    $camp->save();
                }
                catch (Kohana_Database_Exception $ex)
                {
                    echo "Error occurs on craeted Table Camp_".$name."_Category:{$ex->getMessage()}<br />";
                }
            }
        }
    }
?>