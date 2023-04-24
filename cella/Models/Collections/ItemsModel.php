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

class ItemsModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='loads_items';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'iid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['loadref','pallref','orderref','status','created_operator','operator','assign','completed','access','enabled'];
	
	protected $validationRules= [];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'iid'=>	 				['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'loadref'=>				['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'pallref'=>				['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'orderref'=>			['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'status'=>				['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'operator'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'created_operator'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'assign'=>				['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'completed'=>			['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'access'=>				['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>				['type'=>'INT','constraint'=>'11','null'=>FALSE]		
	];
	
	/**
	 * Returns array with ready to load pallets
	 * 
	 * @return Array
	 */
	function getReadyForLoadPallets()
	{
		$ref=model('Collections/ItemsModel')->getForForm('pallref','pallref');
		
		$filters=['status'=>model('Settings/SettingsModel')->get('pallets.pallet_type_load'),'enabled'=>1];
		if (is_array($ref) && count($ref)>0)
		{
			$filters['pid notIn']=$ref;
		}
		return model('Pallet/PalletModel')->filtered($filters)->find();
	}
	
	function getOrdersForLoad($loadRef)
	{
		$tasks=$this->table;
		$pall=model('Pallet/PalletModel')->table;
		$ord=model('Warehouse/OrdersModel')->table;
		$data=$this->select($ord.'.reference')
				   ->join($pall,$pall.'.reference='.$tasks.'.pallref','RIGHT')
				   ->join($ord,$ord.'.reference='.$pall.'.corder','RIGHT')
				   ->where('loadref',$loadRef)
				   ->groupby($ord.'.reference')
				   ->find();
		$arr=[];
		foreach ($data as  $value) 
		{
			$arr[]=$value['reference'];
		}
		return $arr;
	}
	
	/**
	 * Disables ONLY_FULL_GROUP_BY mode for mysql
	 */
	 function disableOnlyFullGroupBy()
	 {
	 	$sqlmode=$this->db->query("SELECT INSTR(@@sql_mode, 'ONLY_FULL_GROUP_BY') > 0 ;")->getResultArray();
		
		if (is_array($sqlmode) && count($sqlmode) > 0 && is_array($sqlmode[0]) && in_array(1, $sqlmode[0]))
		{
			return $this->db->query("SET GLOBAL sql_mode=(SELECT REPLACE(REPLACE(REPLACE(@@sql_mode, ',ONLY_FULL_GROUP_BY', ''),'ONLY_FULL_GROUP_BY,',''),'ONLY_FULL_GROUP_BY',''));");
		}
		return FALSE;
	 	
	 }
	
	/**
	 * Returns array with ready to load pallets
	 * 
	 * @return Array
	 */
	function getReadyForLoadJobs($collection)
	{
		$sql=$this->db();
		if ($collection!=null)
		{
			$sql=$sql->table('vw_jobs_for_dispatched_loads')
					   ->where('load',$collection);
		}else
		{
			$sql=$sql->table('vw_jobs_for_load');
		}
		return $sql->get()->getResultArray();	
	}
	
	/**
	 * Get array with load pallets / items
	 * 
	 * @param  string $ref
	 * @param  bool   $excludeLoaded
	 * 
	 * @return Array
	 */
	function getLoadPallets($ref,$excludeLoaded=FALSE,$pallRef=null)
	{
		$pallets=model('Pallet/PalletModel')->table;
		
		$filters=['enabled'=>1,'loadref'=>$ref];
		if ($pallRef!=null)
		{
			$filters['pallref']=$pallRef;
		}
		
		if (!is_bool($excludeLoaded) && ($excludeLoaded==1 || $excludeLoaded==2 || $excludeLoaded==0 || $excludeLoaded=33))
		{
			$filters['status']=$excludeLoaded;
		}else
		if (is_bool($excludeLoaded) && $excludeLoaded)
		{
			$filters['status <']=2;
		}
                return $this->getView('vw_stackingplan_collection')
                            ->filtered($filters)
                            ->orderby('_order_a')
                            ->orderby('_order_b')
                            ->find();
                /*Removed from version 3.0.7
		$select=[];
		$select[]=$this->table.'.*';
		$select[]='pal.location';
		$select[]='pal.customer';
		$select[]='pal.stack as lmh';
		$select[]="concat(pal.stacknr,pal.stackpos) as 'cstacking'";
		$select[]="`pal`.`stacktruck`+0 as '_order_a'";
                $select[]="REPLACE(`pal`.`stacktruck`,`pal`.`stacktruck`+0,'') as '_order_b'";
                
		$select[]="pal.stacktruck as 'stacking'";
                //
		return $this->filtered($filters)
					->select(implode(',',$select))
					->join($pallets.' as `pal`','pal.reference='.$this->table.'.pallref','LEFT')
					->orderBy('stacking')
					->find();*/			
	}
		
	
	function changeTaskStatus($ref,$status,$location=null)
	{
		
		$model=$this->where('pallref',$ref);
		$model=$model->builder()->set('status',$status);
		if ($location!=null)
		{
			model('Pallet/PalletModel')->builder()->set('location',$location)
									   ->where('reference',$ref)
									   ->update();
		}
		if ($status==1)
		{
			$model=$model->set('assign',formatDate())->set('operator',loged_user('username'));
		}else
		if ($status==2)
		{
			if ($model->set('completed',formatDate())->set('operator',loged_user('username'))->update())
			{
				return model('Pallet/PalletModel')->builder()->set('status',model('Settings/SettingsModel')->get('pallets.pallet_type_disp'))
									   ->where('reference',$ref)
									   ->update();/**/
			}
		}
		
		return $model->update();
	}
	
	
	/**
	 * Returns qty of dispatthed items/pallet from given load
	 * 
	 * @param  string $ref
	 * 
	 * @param  Array
	 */
	function getQtyOfNotDispatchedItems($ref)
	{
		return $this->count(['loadref'=>$ref,'status <'=>2]);
	}
	
	/**
	 * Change status on given items to complete
	 * 
	 * @param  array $refs
	 * 
	 * @return bool
	 */
	function completePallets(array $refs)
	{
		return $this->builder()
					->set('status',2)
					->set('assign',formatDate())
					->set('operator',loged_user('username'))
					->whereIn('pallref',$refs)
					->update();
	}
	
	function getLoadPalletsForStackPlan($ref,$type='load',$showType=FALSE)
	{
		$pallets=model('Pallet/PalletModel')->table;
		$size=model('Pallet/PalletSizeModel')->table;
		$supplier=model('Owners/SupplierModel')->table;
		
		$columns=[];
		
		$columns[]=$pallets.'.corder as custref';
		$columns[]='CONCAT('.$supplier.'.name," ",'.$pallets.'.supplier) as supplier';
		$columns[]=$pallets.'.reference';
		$columns[]=$pallets.'.location';
		$columns[]=$pallets.'.order as palno';
		$columns[]=$pallets.'.stack as lmh';
		$columns[]=$size.'.length';
		$columns[]=$size.'.width';
		$columns[]='('.$pallets.'.height*10) as height';
		$columns[]='concat('.$pallets.'.stacknr,'.$pallets.'.stackpos) as stacking';
		$columns[]=$pallets.'.stacknr';
		$columns[]=$pallets.'.stackpos';
		$columns[]=$pallets.'.stacktruck';
		$columns[]=$pallets.'.customer';
		if ($showType)
		{
			$columns[]=$size.'.type as iseur';
		}
		$data=$this;
		if ($type=='load')
		{
			$data=$data->join($pallets,$pallets.'.reference='.$this->table.'.pallref OR '.$pallets.'.corder='.$this->table.'.pallref','Left')
					   ->where($this->table.'.loadref',$ref);
		}else
		{
			$data=model('Pallet/PalletModel')->where($pallets.'.corder',$ref)
											 ->groupStart()
											 ->where($pallets.'.status',model('Settings/SettingsModel')->get('pallets.pallet_type_load'))
											 ->orWhere($pallets.'.status',1)
											 ->groupEnd();
		}

		$data=$data->select(implode(',',$columns))
				   ->join($size,$pallets.'.size='.$size.'.name','Left')
				   ->join($supplier,$pallets.'.supplier='.$supplier.'.code','Left')
				   ->orderBy('stacktruck')//length(stacking),
				   ->find();
		return $data;
	}

	function getOrdersFromLoad($ref)
	{
		$pallets=model('Pallet/PalletModel')->table;
		$data=$this->select($pallets.'.corder')
				   ->join($pallets,$pallets.'.reference='.$this->table.'.pallref','Left')
				   ->where('loadref',$ref)
				   ->find();
		$arr=[];
		foreach ($data as $value) 
		{
			$arr[]=$value['corder'];
		}
		return $arr;
	}
}