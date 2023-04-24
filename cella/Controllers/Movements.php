<?php
/*
 *  This file is part of Cella WMS  
 * 
 * 
 *  @version: 1.1					
 *  @author Artur W <https://github.com/awiater>			
 *  @copyright Copyright (c) 2021 All Rights Reserved				
 *
 *  @license MIT https://opensource.org/license/mit/
 */

  
namespace CELLA\Controllers;

use \CELLA\Helpers\AccessLevel;
use \CELLA\Helpers\MovementsTypes;
use \CELLA\Helpers\Strings as Str;

class Movements extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'		=>AccessLevel::modify,
		'locations'	=>AccessLevel::delete,
		'location'	=>AccessLevel::delete,
		'kpi'	=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'movements'=>'Warehouse/Movements',
		//'product'=>'Warehouse/Product',
	];
	
		/**
	 * Array with controller method remaps ($key is fake function name and $value is actuall function name)
	 */
	public $remaps=
	[
		'index'=>'kpi'
		
	];
	
	function kpi($data=[])
	{
		$this->setFormView('Warehouse/KPI/index_kpi');
		
		if (array_key_exists('rsql', $data))
		{
			$data['rsql']=parse_ini_string($data['rsql'],TRUE);
		}else
		{
			$data['rsql']=[];
		}
		
		if (array_key_exists('rfilters', $data))
		{
			foreach (explode(PHP_EOL,$data['rfilters']) as $value) 
			{
				$value=explode('|',$value);
				$this->view=$this->view->addCustomFieldFromData(
				[
					'value'=>$value[1],
					'name'=>$value[0],
					'cfid'=>$value[0],
					'type'=>$value[2],
					'label'=>count($value) > 3 ? $value[3] : ucwords($value[0]),
					'options'=>count($value) > 4 ? $value[4] : ''
				],FALSE,'@name');
			}
		}
		
		$data=$data['rsql'];
		
		
		$filters=['from'=>null,'to'=>null,'user'=>loged_user('username'),'status'=>[]];
		
		$post=$this->request->getPost();
		if (is_array($post) && count($post)>0)
		{
			
			
			if (array_key_exists('from', $post))
			{
				$filters['from']=$post['from'];
			}
			
			if (array_key_exists('to', $post))
			{
				$filters['to']=$post['to'];
			}
			
			if (array_key_exists('user', $post))
			{
				$filters['user']=base64_decode($post['user']);
			}
		}

		$filters['from']=$filters['from']==null ? formatDate('now','- 1 months') : $filters['from'];
		$filters['to']=$filters['to']==null ? formatDate('now') : $filters['to'];
		$filters['to']=convertDate($filters['to'],'DB','Ymd');
		$filters['from']=convertDate($filters['from'],'DB','Ymd');
	
		
		$view['colors']=['red','green','blue','yellow'];
		
		
		//$view['data']=$this->model_Movements->getUserDataByType($filters['user'],$filters['start'],$filters['end'],$filters['status']);
		if (array_key_exists('args', $data['data_fetch']))
		{
			$data['data_fetch']['args']=explode(',',  $data['data_fetch']['args']);
			foreach ($data['data_fetch']['args'] as $key=>$value) 
			{
				if (array_key_exists($value, $filters))
				{
					$data['data_fetch']['args'][$key]=$filters[$value];
				}
				if (array_key_exists($value, $data))
				{
					$data['data_fetch']['args'][$key]=$data[$value];
				}
			}
		}else
		{
			$data['data_fetch']['args']=null;
		}
		$view['data']=loadModule($data['data_fetch']['controller'],$data['data_fetch']['action'],$data['data_fetch']['args']);
		
		$this->view
			 ->setFormAction(current_url(FALSE,FALSE),null,[],[])
			 ->addData('data',$view)
		     ->addData('url',
				[
					'download'=>url($this,'downloaddata',[],['refurl'=>current_url(FALSE,TRUE)])
				])
			->addChartObject('bar','KPI',$view['data'],['class'=>'col-8']);
			//->addDropDownField('system.reports.kpi_user','user',$this->model_Auth_User->getForForm('username','name'),$filters['user'],[])
			//->addDatePicker('system.reports.kpi_from','from',$filters['from'].'0000',[])
			//->addDatePicker('system.reports.kpi_to','to',$filters['to'].'0000',[])
			//->addCheckList('system.reports.mevements','status',array_keys($filters['status']),$view['status'],[]);
		return $this->view->render();
	}
	
	function downloaddata()
	{
		$data=$this->session->get('_chartdata');
		$data=array_values($data);
		//getcsv(array $data,$name,$firsRecordColumns=FALSE)
		return loadModule('Documents','getcsv',[$data,'file',TRUE]);
	}
	
	function mevementsbyref($ref,array $columns=[])
	{
		return $this->mevementItems($ref,'mhref',$columns);
	}
	
	function mevementsbyinfo($ref,array $columns=[])
	{
		return $this->mevementItems($ref,'mhinfo',$columns);
	}
	
	function mevementItems($ref,$field,array $columns=[])
	{
		if (is_array($ref))
		{
			$field=[$field=>$ref[0],'type %'=>$ref[1]];
		}else
		{
			$field=[$field=>$ref];
		}
		 
		 $this->setTableView('System/movement')
		 	  ->setData('movements','mhdate DESC',FALSE,null,$field)
			  ->setCustomTable(['thead_open'=>'<thead class="thead-dark">','table_class'=>['table-striped'],'table_id'=>'movementTable'])
			  ->setAsDataTable(['pageLength'=>25,'ordering'=>'false','dom'=>'lftip']);
			  
		 foreach ($columns as $key => $value) 
		 {
		 	$list=[];
			$format=null;
			if (is_array($value))
			{
				if (array_key_exists('list', $value))
				{
					$list=$value['list'];
				}
				if (array_key_exists('format', $value))
				{
					$format=$value['format'];
				}
				if (array_key_exists('value', $value))
				{
					$value=$value['value'];
				}else
				{
					$value='';
				}
			}
		 	if (is_numeric($key))
			{
				$key=$value;
			}
			
			if ($key=='mhdate')
			{
				$format='d/m/Y H:i';
			}
			
			if (!Str::contains($value,'.'))
			{
				$value='system.movements.'.$value;
			}
			
			$this->view->addColumn($value,$key,TRUE,$list,$format);
		 }
		 
		 return $this->view->render('text');
	}

}