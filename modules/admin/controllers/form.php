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
class Form_Controller extends Controller
{
 	protected $default_layout = "admin/form";


	protected $fields;
	protected $datarow;
	protected $binddata;

	protected $footHTML;


	public function __construct(){
		$this->_ini_binddata();
		parent::__construct();
	}

	private function _ini_binddata()
	{
		//['dt_rows'] = array();
 		$data['dt_fields'] = array();
 		//$data['pk'] = "";
 		$data['form'] = array('id'=>'form1','name'=>'form1','action'=>"",'method'=>'post','submitlabel'=>'保存','preview'=>'');
 		//$data['table'] = array('id'=>'table1','name'=>'table1');
 		$this->binddata = $data;
	}

	public function set_form($key,$value)
	{
		$this->binddata['form'][$key] = $value;
	}

	public function add_field($type,$field,$settings)
	{
		if(method_exists($this,'to_'.$type))
		{
			$this->fields[] = array('type'=>$type,'field'=>$field,'settings'=>$settings );
		}
		return $this;
	}

	public function add_foot_HTML($html)
	{
		$this->footHTML = $html;
		return $this;
	}

	public function view($data = null, $print = TRUE)
 	{
 		/*$file = $this->default_layout;
 		$this->datarow = $data;
 		$data = $this->_binddata();
 		return parent::view($file, $data, $print);*/


 		$file = $this->default_layout;
 		$view = new View($file);
 		$datas = $this->_binddata($data);

 		foreach($datas as $key=>$val)
 		{
 			$view->set($key,$val);
 		}
 		return $view->render($print);



 	}

 	private function _binddata($data)
 	{
 		$this->datarow = $data;
 		$dt_fields = array();

 		foreach($this->fields as $field)
 		{
 			//var_dump($field);exit;
 			$dt_field['label'] =  isset($field['settings']['label'])?$field['settings']['label']:$field['field'];
 			$funname = "to_".$field['type'];

 			$dt_field['value'] =  call_user_func (array($this,$funname),$field);

 			$dt_fields[] = $dt_field;
 		}
 		$this->binddata['dt_fields'] = $dt_fields;

 		$this->binddata['footHTML'] = $this->footHTML;

 		return $this->binddata ;
 	}



	///////////////////////////////////////////////////
	private function to_label($field)
 	{
 		$data = $this->datarow;
 		$value = isset($data[$field['field']])?$data[$field['field']]:(isset($field['settings']['value']) ? $field['settings']['value'] :"");
 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";
 		return "<span style=\"$style\">$value</span>".$moreHTML;
 	}

	private function to_input($field)
 	{

 		$data = $this->datarow;
 		$value = isset($data[$field['field']])?$data[$field['field']]:(isset($field['settings']['value']) ? $field['settings']['value'] : "");
 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";

 		$readonly = isset($field['settings']['readonly'])?$field['settings']['readonly']: false;


 		$readonly = $readonly?"readonly":"";

 		return "<input name=\"".$field['field']."\" style=\"$style\" value=\"$value\" $readonly />".$moreHTML;
 	}

	private function to_pic($field,$uploadpath="pic")
 	{
 		$this->set_form('enctype','multipart/form-data');
 		$data = $this->datarow;
 		$value = isset($data[$field['field']])?$data[$field['field']]:"";
 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";
 		$src = isset($field['settings']['src'])?$field['settings']['src']:"";
 		$readonly = isset($field['settings']['readonly'])?$field['settings']['readonly']: false;
 		$img_style = isset($field['settings']['img_style'])?$field['settings']['img_style']:"";

 		$readonly = $readonly?"readonly":"";
 		$out = "";
 		if(!empty($src))
 			$out = '<img src="'.$src .'" style="'.$img_style.'"/>';
 		if(!$readonly)
 			$out .= '<input type="file" name="'.$field['field'].'" style="'.$style.'" value="'.$value.'" />';

 		$out .= $moreHTML;

 		return $out;
 	}


	private function to_textarea($field)
 	{

 		$data = $this->datarow;
 		$value = isset($data[$field['field']])?$data[$field['field']]:"";
 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";

 		$readonly = isset($field['settings']['readonly'])?$field['settings']['readonly']: false;


 		$readonly = $readonly?"readonly":"";
 		$name = isset($field['settings']['name'])?$field['settings']['name']:$field['field'];
 		return "<textarea id=\"".$field['field']."\" name=\"".$name."\" style=\"$style\" $readonly />$value</textarea> $moreHTML";
 	}

	private function to_password($field)
 	{
 		$data = $this->datarow;
 		$value = isset($data[$field['field']])?$data[$field['field']]:"";
 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";

 		return "<input type=\"password\" name=\"".$field['field']."\" style=\"$style\" value=\"$value\" />".$moreHTML;
 	}


	private function to_select($field)
 	{

 		$data = $this->datarow;

 		$value = isset($data[$field['field']])?$data[$field['field']]:"";
  		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";

 		$multiple = false;
 		if(isset($field['settings']['multiple']))
 			$multiple = true;


 		$style = isset($field['settings']['style'])?$field['settings']['style']:"";
 		$name = $multiple?$field['field'].'[]':$field['field'];
 		$value = $multiple?explode(',',$value):$value;

 		$mstr = $multiple?'multiple="multiple"':"";

 		$html = '<select id="sel_'.$name.'" name="'.$name.'" style="'.$style.'" '. $mstr .'>';

 		if(isset($field['settings']['values']))
 		{
 			if(isset($field['settings']['emptyValue']))
 			{
 				$html .= '<option value="'.$field['settings']['emptyValue'].'" >-请选择-</option>';
 			}
 			$lists = $field['settings']['values'];


 			foreach($field['settings']['values'] as $group=>$item)
 			{
 				$selected = "";
 				if(empty($value))
 					$value = isset($field['settings']['defVal'])?$field['settings']['defVal']:'';

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
 			}
 		}

 		$html .= "</select>".$moreHTML;
 		return $html;
 		//return "<a>$value</a>";
 	}

	public function to_seluser($field)
	{
		// $field['settings']['values'] =
		$data = $this->datarow;
		$value = isset($data[$field['field']])?$data[$field['field']]:"";
 		$moreHTML = isset($field['settings']['moreHTML'])?$field['settings']['moreHTML']:"";
		if(!empty($value))
		{
			$user = User_BaseInfo_Access::instance()->getRecord(intval($value));
			//var_dump($user);
			$name = $user['nick_name'];
			$values[] = array('label'=>$name,'value'=>$value);
			$field['settings']['values'] = $values;
		}

		$html = $this->to_select($field);
		$html .= '账号:<input id="schval_'.$field['field'].'"><input id="schbtn_'.$field['field'].'" type="button" value="搜索">';
		$html .= '<script>$("#schbtn_'.$field['field'].'").click(function(){
						var serchval = $("#schval_'.$field['field'].'").val();
						if(!serchval)
							return false;
						ajaxGetUsers(serchval,"'.$field['field'].'");});</script>'.$moreHTML;


		return $html;
	}



} // End Index_Controller



