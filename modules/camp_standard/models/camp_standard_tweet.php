<?php
class Camp_Standard_Tweet_Model extends Cluster_ORM
{
	protected $_primary_key = 'tweet_id';
	protected $_table_name = '';
	protected $_table_names_plural = FALSE;
	protected $_created_column = array ("column" => 'created');
	protected $_updated_column = array ("column" => 'updated');

    protected static $_last_table_name;

    public function __construct($data = null, $table_name = null){
       /* if(!isset($args[1]))
            throw new Kohana_Exception("缺少table_name", __CLASS__, __FUNCTION__);*/
        if (isset($table_name)) {
            $table_name = 'Camp_'.$table_name.'_Tweet';
        }
        parent::__construct($data, $table_name);
    }


    public function getTweets($keyword = '', $orderby = array(), $range = 0, $start = 0 )
    {
        $db = & Database::instance();
        $db->from($this->_table_name);
        if($keyword != '') {
            $db->where(array('keyword' => $keyword));
        }

        if(is_array($orderby) && count($orderby) == 1) {
            foreach ($orderby as $key => $value) {
                $db->orderby($key, $value);
            }           
        }

        $db->select('*');
        if($range != 0) {
            $db->limit($range, $start);
        }
        
        $tweets = $db->get();
        return $tweets;
    }

}