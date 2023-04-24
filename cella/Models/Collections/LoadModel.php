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
 
namespace CELLA\Models\Collections;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\MovementType;

class LoadModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='loads';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'lid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['reference','duein','status','location','operator','operator_created','created','loaded','progress','lcfg','access','enabled'];
	
	protected $validationRules =
	[
		'reference'=>'required|is_unique[loads.reference,lid,{lid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'lid'=>	 				['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'reference'=>			['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE,'unique'=>TRUE],
		'duein'=>				['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'status'=>				['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'location'=>			['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'operator'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'operator_created'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'created'=>				['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'loaded'=>				['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'lcfg'=>				['type'=>'TEXT','null'=>TRUE],
		'access'=>				['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>				['type'=>'INT','constraint'=>'11','null'=>FALSE]		
	];
	
	
	public function filtered(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
	{
		$result=parent::filtered($filters,$orderby,$paginate,$logeduseraccess,$Validation);
		$palletsqty=model('Collections/ItemsModel')->selectCount('iid')->where('loadref='.$this->table.'.reference',null,FALSE)->getCompiledSelect();
		$palletsqty_non=model('Collections/ItemsModel')->selectCount('iid')->where('loadref='.$this->table.'.reference AND status =3',null,FALSE)->getCompiledSelect();
		$result->select($this->table.'.*,CONCAT(('.$palletsqty_non."),' / ',(".$palletsqty.')) as progress');
		return $result;
	}
	
	/**
	 * Set pallets from given order as dispatched
	 * 
	 * @params  string $ref
	 * 
	 * @return bool
	 */
	function dispatchPallets($ref)
	{
		$ref=model('Collections/ItemsModel')->getForForm('pallref','pallref',FALSE,null,['loadref'=>$ref]);
		return model('Pallet/PalletModel')->whereIn('pid',$ref)->builder()->set('status',model('Settings/SettingsModel')->get('pallets.pallet_type_disp'))->update();
	}
	
	/**
	 * Returns array with ready to be completed loads references
	 * 
	 * @param  bool $exceptCompleted
	 * 
	 * @return Array
	 */
	function getReadyToCompleteLoads($exceptCompleted=TRUE,$field='reference')
	{
		$model=$this;
		$items=model('Collections/ItemsModel');
		$model=$model->select($this->table.'.*')
			         ->join($items->table,$items->table.'.loadref='.$model->table.'.reference AND '.$items->table.'.status=2','Right')
					 ->groupby('reference')
					 ->where($model->table.'.status <',$exceptCompleted ? 2 : 3);					 
		$arr=[];
		foreach ($model->find() as  $value) 
		{
			$arr[]=$value[$field];
		}
		return $arr;
	}
	
	/**
	 * Returns array with completed loads references
	 * 
	 * @param  string $field
	 * @param  string $value
	 * @param  Array  $filters
	 * 
	 * @return Array
	 */
	function getCompletedLoads($field='reference',$value='reference',array $filters=[])
	{
		$filters['status']=2;
		return $this->getForForm($field,$value,FALSE,null,$filters);
	}
	
	/**
	 * Returns array with loads refrences which have pallets / items assigned to them
	 * 
	 * @return Array
	 */
	function getLoadsWithPallets()
	{
		$data=[];
		foreach (model('Collections/ItemsModel')->select('loadref')->distinct()->find() as  $value) 
		{
			$data[]=$value['loadref'];
		}
		return $data;
	}
	
	function getLoadsForMobile($field=null,$status=0)
	{
		$items=model('Collections/ItemsModel')->table;
		$filters=[$this->table.'.enabled'=>1,$this->table.'.access'=>'@loged_user','reference In'=>$this->getLoadsWithPallets()];
		if ($status==0 || $status==1)
		{
			$filters[$items.'.status <']=2;
			$filters[$this->table.'.status <']=2;
			
		}else
		{
			$filters[$items.'.status']=$status;
		}
		$data=$this->filtered($filters)
					->select($this->table.'.reference,lid')
					->join($items,$items.'.loadref='.$this->table.'.reference','Left')
					->groupby($this->table.'.reference',$this->table.'.lid')
					->find();
		if ($field==null)
		{
			return $data;
		}
		if (is_array($data) && count($data) > 0)
		{
			if (!array_key_exists($field, $data[0]))
			{
				return [];
			}
		}
		$arr=[];
		foreach ($data as $value) 
		{
			if (strlen($value[$field]) > 0)
			{
				$arr[]=$value[$field];
			}
			
		}
		return $arr;
	}
	
	function createMovementTasksForLoad($ref)
	{
		$model=$this;
		$items=model('Collections/ItemsModel');
		
		if (!is_array($ref))
		{
			return FALSE;
		}
		
		$pallets=$items->getLoadPallets($ref['reference']);
		if (!is_array($pallets))
		{
			return FALSE;
		}
		$result=false;
		foreach ($pallets as  $value) 
		{
			$result=model('Tasks/TaskModel')->addNewPalletMovement($value['pid'],$ref['location'],null,2);
			if (!$result)
			{
				return $result;
			}
		}
		return $result;
	}
	
	function changeLoadStatus($ref,$status,$addMovements=FALSE,$field='reference')
	{
		if ($status==2 || $status=="2")
		{
			$orders=model("Collections/ItemsModel")->getOrdersForLoad($ref);
			if (is_array($orders) && count($orders)>0)
			{
				model('Warehouse/OrdersModel')->builder()->set('status',-2)->whereIn('reference',$orders)->update();
			}
		}
		if($this->filtered(['enabled'=>1,$field=>$ref])->set('status',$status)->update())
		{
			if ($addMovements)
			{
				return $this->createMovementsHistoryForLoad($ref,$status);
			}
		}
	}
	
	
	
	
	/**
	 * Creates movements tasks for given load
	 * 
	 * @param  string $ref
	 * @param  Int    $status
	 * @param  bool   $onlypall
	 * 
	 * @return bool
	 */
	function createMovementsHistoryForLoad($ref,$status,$onlypall=FALSE)
	{
		return 1;
	 	$model=$this;
		$items=model('Collections/ItemsModel');
		
	 	$ref=$model->filtered(['enabled'=>1,'reference'=>$ref])->first();
		
		if (!is_array($ref))
		{
			return FALSE;
		}
		
		$pallets=$onlypall!=-1 ? $items->getLoadPallets($ref['reference']) : [];
		
		if (!is_array($pallets))
		{
			return FALSE;
		}
		if ($status>0)
		{
			$tasks=[];
			if (!$onlypall)
			{
				$tasks[]=
					[
						'mhref'=>$ref['reference'],
						'mhtype'=>$status==2 ? MovementType::load_done : MovementType::load_loading,
						'mhuser'=>loged_user('username'),
						'mhdate'=>formatDate()
					];
			}
			
			foreach ($pallets as $value) 
			{
				$tasks[]=
				[
					'mhref'=>$value['reference'],
					'mhtype'=>$status==2 ? MovementType::load_done : MovementType::load_loading,
					//'mhinfo'=>$value['corder'],
					'mhuser'=>loged_user('username'),
					'mhdate'=>formatDate()
				];
			}
			if (count($tasks)>0)
			{
				model('Warehouse/MovementsModel')->insertBatch($tasks);
			}
			 
		}
		
	 }
	
	
}