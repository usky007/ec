<?php
class Camp_Standard_TweetComments_Model extends Cluster_ORM
{
	protected $_primary_key = 'tweetComments_id';
	protected $_table_name = '';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');

    public function __construct($data=null, $table_name = null){
         if (isset($table_name)) {
            $table_name = 'Camp_'.$table_name.'_TweetComments';
        }
        parent::__construct($data, $table_name);
    }

}