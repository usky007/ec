<?php
/**
 * re001 Project
 *
 * LICENSE
 *
 * http://www.re001.com/license.html
 *
 * @category   re001
 * @package    ChangeMe
 * @copyright  Copyright (c) 2010 re001 Team.
 * @author     maskxu
 */
class Grid_Controller extends Controller
{
 	public $default_layout = "admin/grid";

	protected $additions;
	protected $page ;

	protected $fields;
	protected $filter;


	protected $binddata;
	protected $pk;

	protected $checked_rows =array();


	public function __construct(){
		$this->_ini_binddata();
		parent::__construct();
	}

	private function _ini_binddata()
	{
		$data['dt_rows'] = array();
 		$data['dt_columns'] = array();
 		$data['pk'] = "";
 		$data['form'] = array('id'=>'form1','name'=>'form1','action'=>"",'method'=>'post');
 		$data['table'] = array('id'=>'table1','name'=>'table1');
 		$data['filter'] = array();
 		$this->binddata = $data;
	}

	public function add_field($type,$field,$settings)
	{
		if(method_exists($this,'to_'.$type))
		{
			$this->fields[] = array('type'=>$type,'field'=>$field,'settings'=>$settings);
		}
		return $this;
	}


	public function add_filter($type,$field,$where,$settings)
	{
		if(method_exists($this,'to_'.$type))
		{
			$this->filter[] = array('type'=>$type,'field'=>$field,'where'=>$where,'settings'=>$settings);
		}
		return $this;
	}


	public function set_checkrows($rowpks)
	{
		$this->checked_rows = $rowpks;
	}

	public function get_filter_data()
	{
		$where = array();
		$like = array();
		$in = array();
		foreach($this->filter as $item)
		{

			if(!isset($_GET[$item['field']]))
				continue;
			$val = $_GET[$item['field']];
			if($val === $item['settings']['defVal'])
				continue;

			switch($item['where']['method'])
			{
				case "where":$where[$item['field']]=$val;break;
				case "like":$like[$item['field']]="%$val%";;break;
				case "in":$in[$item['field']]=$val;;break;
			}

		}
		return array('where'=>$where,'like'=>$like,'in'=>$in);
	}

	public function set_form($key,$value)
	{
		$this->binddata['form'][$key] = $value;
	}
	public function set_pk($field)
	{
		$this->pk = $field;
	}

	public function set_addurl($val)
	{
		$this->binddata['addurl'] = $val;
	}

	public function set_delbtnval($val)
	{
		$this->binddata['delbtnval'] = $val;
	}

 	private function _binddata($rows)
 	{
 		//bind grid data
 		$dt_rows = array();
 		if(is_null($rows))
 		{
	 		$rows = array();
 		}

 		foreach($rows as $row)
 		{

 			$row = is_array($row)?$row:$row->as_array();


 			$datacells = array();
 			foreach($this->fields as $column)
 			{

 				$funname = "to_".$column['type'];

 				$datacells[] = $this->$funname($column['settings'],$row[$column['field']]);
 			}
 			$dt_rows[] = array('value'=> $row[$this->pk],'cells'=>$datacells);
 		}

 		$dt_columns = array();
 		foreach($this->fields as $column)
 		{
 			$dt_columns[] = $column;
 		}

 		$this->binddata['dt_rows'] = $dt_rows;
 		$this->binddata['dt_columns'] = $dt_columns;

 		if(!empty($this->filter)){
	 		foreach($this->filter as $item)
			{
				$data['label'] = $item['settings']['label'];


				$funname = "to_".$item['type'];
				$field = $item['field'];
				$val = isset($item['settings']['defVal'])?$item['settings']['defVal']:"";
				$val = isset($_REQUEST[$field])?$_REQUEST[$field]:$val;
				$data['value'] = $this->$funname($item['settings'],$val,$field);
				$this->binddata['filter'][] = $data;
			}
 		}
 		$this->binddata['checked_rows'] = $this->checked_rows;
 		return $this->binddata ;

 	}

 	public function view($data = null, $print = TRUE)
 	{
 		$file = $this->default_layout;
 		$view = new View($file);
 		$datas = $this->_binddata($data);

 		foreach($datas as $key=>$val)
 		{
 			$view->set($key,$val);
 		}
 		return $view->render($print);


 	}

	///////////////////////////////////////////////////
	private function to_label($settings,$value)
 	{
 		$moreHTML = isset($settings['moreHTML'])?$settings['moreHTML']:"";
 		return "<span>$value</span>".$moreHTML;
 	}

	private function to_button($settings,$value)
 	{
 		return "<input value=\"$value\" />";
 	}

	private function to_link($settings,$value)
 	{
 		return '<a href="'.$this->_str($settings['href'],$value).'" >'.$settings['text'].'</a>';
 	}

 	private function to_input($settings,$value,$field="")
 	{
 		$name = empty($field)?"":'name="'.$field.'"';
 		$moreHTML = isset($settings['moreHTML'])?$settings['moreHTML']:"";
 		$attrs = isset($settings['attrs']) ? $settings['attrs'] : '';
 		$input_attr = '';
 		$style = isset($settings['style'])?$settings['style']:"";
 		if(is_array($attrs)){
 			foreach ($attrs as $attr => $v){
 				$input_attr .= $attr.'="'.$v.'" ';
 			}
 		}
 		return '<input type="input" style="'.$style.'" value="'.$value.'" '.$input_attr.$name.'/>'.$moreHTML;
 	}

 	private function to_select($settings,$value,$field="")
 	{
 		//$data = $this->datarow;
 		//$value = isset($data[$field['field']])?$data[$field['field']]:"";


 		$multiple = false;
 		if(isset($field['settings']['multiple']))
 			$multiple = true;


 		$style = isset($settings['style'])?$settings['style']:"";
 		$name = $multiple?$field.'[]':$field;
 		$value = $multiple?explode(',',$value):$value;

 		$mstr = $multiple?'multiple="multiple"':"";

 		$html = '<select name="'.$name.'" style="'.$style.'" '. $mstr .'>';

 		if(isset($settings['values']))
 		{

 			if(isset($settings['emptyValue']))
 			{
 				$html .= '<option value="'.$settings['emptyValue'].'" >-请选择-</option>';
 			}

 			$lists = $settings['values'];
 			foreach($lists as $group=>$item)
 			{
 				$selected = "";


 				if(!isset($item['value']))
 				{

					$html .='<optgroup label="'.$group.'">';
					foreach($item as $subitem)
					{
						if($multiple)
			 				$selected = in_array($subitem['value'],$value)?"selected":"";
		 				else
			 				$selected = $value==$subitem['value']?"selected":"";

		 				$html .= '<option value="'.$subitem['value'].'" '.$selected.'>'.$subitem['label'].'</option>';

					}

					$html .='</optgroup>';
 				}
 				else
 				{
	 				if($multiple)
		 				$selected = in_array($item['value'],$value)?"selected":"";
	 				else
		 				$selected = $value==$item['value']?"selected":"";

	 				$html .= '<option value="'.$item['value'].'" '.$selected.'>'.$item['label'].'</option>';

 				}

// 				if($multiple)
//	 				$selected = in_array($item['value'],$value)?"selected":"";
// 				else
//	 				$selected = $value==$item['value']?"selected":"";
//
// 				$html .= '<option value="'.$item['value'].'" '.$selected.'>'.$item['label'].'</option>';
 			}
 		}

 		$html .= "</select>";
 		return $html;
 		//return "<a>$value</a>";

 	}

 	private function _str($string,$param)
 	{
 		$count = count($param);
 		$search = array();
 		for($i=0;$i<$count;$i++)
 		{
 			$search[] = "{".$i."}";
 		}
 		return str_replace($search,$param,$string);
 	}
	/*private function _array_to_str($ary)
	{
		if(!is_array($ary) && count($ary)==0)
			return "";
		$str = "";
		foreach($ary as $item)
		{
			$str .=  $item.",";
		}
		if(strlen($str))
		{
			$str = substr($str,0,strlen($str)-1);
		}
		return $str;
	}*/
 	private function _callbackvalue()
 	{

 	}




} // End Index_Controller



