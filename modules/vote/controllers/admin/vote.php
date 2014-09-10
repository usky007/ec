<?php
class Vote_Controller extends Admin_Controller
{
	public function setting()
	{
		$content = new Grid_Controller();
 		$content->set_pk('id');
 		$content->add_field('label','votekey',array('label'=>'投票标识'));
 		$content->add_field('label','name',array('label'=>'投票名'));

 		$content->add_field('link','id',array('label'=>'操作','text'=>'修改','href'=>'/admin/vote/edit/update?id={0}'));
 		$mod = new Vote_Model();
 		$records = $mod->find_all()->as_array();
 		//$records = $mod->location->find_all()->as_array();

		$datas = array();
 		foreach($records as $row)
 		{
			
			$newrow['id'] = $row->id;
			$newrow['name'] =  $row->name;
			$newrow['votekey'] = $row->voteKey;
			$datas[] = $newrow;
 		}
		

 		//$datas = $records;
 		$content->set_form('action',"/admin/city/record_del");
 		$html = $content->view($datas,false);
 		$html .= '<br><a href="/admin/guide/city">返回</a>';
		$this->add_css('admin.css');
 		$this->set_output(array('pagenation'=>'','content'=>$html));
	}
	
	public function edit($type)
	{
		$content = new Form_Controller();
		$content->set_form('action','save/'.$type);
		$content->add_field('label','votekey',array('label'=>'投票标识'));
 		$content->add_field('label','name',array('label'=>'投票名'));
 		$content->add_field('label','name',array('label'=>'投票名'));
 		$datas = '';
		if($type == 'update')
		{
			$id = Input::instance()->get('id',null);
			if(empty($id))
			{
				$this->add_success_message('参数错误！');
				url::redirect('/admin/vote/setting');
			}
			$voteMod = new Vote_Model($id);
			$voteMod = $voteMod->find();
			if(!$voteMod->loaded())
			{
				$this->add_success_message('参数错误！');
				url::redirect('/admin/vote/setting');
			}
			$newrow['name'] =  $row->name;
			$newrow['votekey'] = $row->voteKey;
			$datas[] = $newrow;
			
		}
		$html = $content->view($datas,false);
		$this->add_css('admin.css');
		$this->set_output(array('pagenation'=>'','content'=>$html));
		
	}
	
	public function save($type)
	{
		switch ($type)
		{
			case 'post':
				return $this->post();
				break;
			case 'update':
				return $this->update();
				break;
		}
	}
	
	
	
	private function post()
	{
		
	}
	private function update()
	{
	
	}
	private function get_symbol_name($symbol)
	{
		return ;
	}
}
