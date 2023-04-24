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
 
namespace CELLA\Models\Pallet;

use \CELLA\Helpers\MovementType;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\Arrays as Arr;

class PalletModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='pallets';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'pid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['reference','operator','supplier','sorder','suppalref','customer','corder','status','size',
							  'stack','stacknr','stackpos','order','height','location','xsnumber',
							  'stacktruck','putaway','access','enabled'];
	
	protected $validationRules =
	 [
	 	'reference'=>'required|is_unique[pallets.reference,pid,{pid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'pid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'reference'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE],
		'operator'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE,'foreignkey'=>['users','username','CASCADE','SET NULL']],
		'supplier'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'suppalref'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>TRUE],
		'sorder'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'customer'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'corder'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'size'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'foreignkey'=>['pallets_size','name','CASCADE','SET NULL']],
		'stack'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'foreignkey'=>['pallets_stack','code','CASCADE','SET NULL']],
		'stacknr'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>TRUE],
		'stackpos'=>	['type'=>'VARCHAR','constraint'=>'5','null'=>TRUE],
		'stacktruck'=>	['type'=>'VARCHAR','constraint'=>'5','null'=>TRUE],
		'order'=>		['type'=>'INT','constraint'=>'11','null'=>TRUE],
		'height'=>		['type'=>'VARCHAR','constraint'=>'10','null'=>FALSE],
		'location'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'putaway'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	
	/**
	 *  Return all records from table
	 *  
	 * @param  array   $filters  		Array with filters (key is field, value is field value)
	 * @param  string  $orderby  		Order by field name
	 * @param  string  $paginate 		Pagination settings
	 * @param  integer $logeduseraccess Loged user access level
	 * @return array
	 */
	public function filtered(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
	{
		$this->select($this->table.'.*,(IF(length(corder) > 0, corder, sorder)) as orderref');
		return parent::filtered($filters,$orderby,$paginate,$logeduseraccess,$Validation);
	}
	
	/**
	 * Finds all pallet data with customer datat for label
	 * 
	 * @param  mixed $ref
	 * 
	 * @return Array
	 */
	function findForLabel($ref)
	{
		$filters=[];
		$model=$this;
		$cust=model('Owners/CustomerModel')->table;
		if (is_numeric($ref))
		{
			$model=$model->where($model->table.'.pid',$ref);
		}else
		if (is_array($ref))
		{
			$ref=Arr::Prefix($ref,$model->table.'.');
			$model=$model->filtered($ref);
		}else
		{
			$model=$model->where($model->table.'.reference',$ref);
		}
		$select=[$model->table.'.*'];
		$select[]=$cust.'.*';
		$model=$model->select(implode(',',$select))
					 ->join($cust,$cust.'.code='.$model->table.'.customer','Left');
		return is_array($ref) ? $model : $model->first();
	}
	
	/**
	 * Generate and returns next pallet reference number
	 * 
	 * @return string
	 */
	function generatePalletReference($patern=null,$saveNew=TRUE)
	{	
		$ref=$patern==null ? model('Settings/SettingsModel')->get('pallets.next_palletid'):$patern;
		$db=$this->selectMax('reference')->first();
		if (is_array($db) && array_key_exists('reference', $db) && $patern==null)
		{
			$ref=$db['reference'];
		}
		$int_val=preg_replace('/[^0-9]/', '', $ref);
		if (is_numeric($int_val))
		{
			$int=ltrim($int_val,'0');
			$int++; 
			$ref= substr($ref,0,strlen($ref)-strlen($int_val)).str_pad($int++, strlen($int_val), '0', STR_PAD_LEFT);
		}
		$ref=strftime($ref,time());
		
		if ($saveNew)
		{
			model('Settings/SettingsModel')->write('next_palletid',$ref);
		}
		
		return $ref;
	}
	
	/**
	 * Returns array with pallet references (as keys) and locations (as values)
	 * 
	 * @param  array $pids Array with pallets ids
	 * 
	 * @return array
	 */
	function getForMoveForm(array $pids=[])
	{
		return $this->getFieldsFromPid($pids,'reference',['location','putaway','sorder','pid']);
	}
	
	
	function getPalletsForPutaway(array $filters=[])
	{
		$arr=[];
		$filters['pallets.status >']=-1;
		$filters['pallets.access']='@loged_user';
		$data=$this->select('pallets.*,tasks.action as putaway')
				   ->join('tasks','tasks.tref=pallets.pid and tasks.status=0 and tasks.enabled=1','Right')
				   ->filtered($filters)
				   ->find();
		foreach($data as $pallet)
		{
			$arr[$pallet['reference']]=['location'=>$pallet['location'],'putaway'=>$pallet['putaway'],'sorder'=>$pallet['sorder'],'pid'=>$pallet['pid']];
		}
		return $arr;
	}
	
	/**
	 * Returns array with pallets fields
	 * 
	 * @param  array  $pids       Array with pallets ids
	 * @param  string $keyField   Column name which will be used as array keys
	 * @param  string $valueField Column name which will be used as array values
	 * 
	 * @return array
	 */
	function getFieldsFromPid(array $pids=[],$keyField,$valueField)
	{
		$this->select('*');
		if (count($pids)> 0)
		{
			$records=$this->whereIn('pid',$pids)->find();
		}else
		{
			$records=$this->findAll();
		}
		
		$arr=[];
		$valueField=is_array($valueField) ? $valueField : [$valueField];
		foreach ($records as $value) 
		{
			$tkeyField=$value[$keyField];
			
			foreach ($value as $key => $svalue) 
			{
				if (!in_array($key, $valueField))
				{
					unset($value[$key]);
				}
			}
			if (count($value)==1)
			{
				$value=array_values($value);
				$value=$value[0];
			}
			
			$arr[$tkeyField]=$value;
		}
		return $arr;
	}
	
	/**
	 * Change pallet locations to order location (only pallets from order)
	 * 
	 * @param  string $order    Order reference
	 * @param  string $location New Location
	 * @param  string $type     Order type (corder, sorder)
	 * 
	 * @return bool
	 */
	function addLocFromOrder($order,$location,$type)
	{
		return $this->builder()
					->set('location',$location)
					->whereIn('status',explode(',', $type))
					->groupStart()
					->where('corder',$order)
					->where('sorder',$order)
					->groupEnd()
					->update();
	}
	
	/**
	 * Returns qty of pallet in order
	 * 
	 * @param  string $order    Order reference
	 * @param  int    $status	Optional status of pallet
	 * @param  string $type     Optional Order type (corder, sorder)
	 * 
	 * @return INT
	 */
	function getQtyOfPalletsFromOrder($order,$status=null,$type=1)
	{
		$type=$type==1 ? 'corder' : 'sorder';
		$filters=['access'=>'@loged_user',$type=>$order,'enabled'=>1];
		if (is_numeric($status))
		{
			$filters['status']=$status;
		}else
		if (Str::contains($status,' '))
		{
			$status=explode(' ', $status);
			$filters['status '.$status[0]]=$status[1];
		}else
		if (is_array($status))
		{
			$filters=array_merge($filters,$status);
		}
		
		return $this->count($filters);
	}
	
	function getQtyOfIncompletePalletsForOrders(array $orders,$field='reference',$type=1)
	{
		$tbl=model('Warehouse/OrdersModel')->table;
		$type=$type==1 ? 'corder' : 'sorder';
		$filters[$this->table.'.status <']=100;
		$filters[$this->table.'.status >']=-1;
		$where="`pallets`.`status` < 100 AND `pallets`.`status` > '-1'";
		if (count($orders) > 0)
		{
			$where.=" AND `pallets`.`".$type."` = `orders`.`reference` ";
		}
		 
		$sql=$this->select($tbl.'.'.$field.' as reference,count(pid) as qty')
				  ->join($tbl,$tbl.'.oid IN ('."'".implode("','",$orders)."'".')','Left')
				  ->where($where,null,FALSE)
				  ->groupBy($field);
		
		$arr=[];
		foreach($sql->find() as $value)
		{
			$arr[$value['reference']]=$value['qty'];
		}
		return $arr;
	}
	
	/**
	 * Returns qty of pallet still in delivery
	 * 
	 * @param  string $order Order reference
	 * 
	 * @return int
	 */
	function getQtyOfPalletsInDelivery($order)
	{
		return $this->count(['access'=>'@loged_user','enabled'=>1,'sorder'=>$order,'status'=>0]);	
	}
	
	/**
	 * Returns qty of pallet still in picking
	 * 
	 * @param  string $order Order reference
	 * 
	 * @return int
	 */
	function getQtyOfPalletsInPick($order)
	{
		return $this->count(['access'=>'@loged_user','enabled'=>1,'corder'=>$order,'status <>'=>model('Settings/SettingsModel')->get('pallets.pallet_type_comp'),'status > '=>1]);	
	}

	function getQty($mode=0)
	{
		$data=[
				'All Pallets'=>$this->count(['enabled'=>1]),
				'Incomplete pallets'=>$this->count(['enabled'=>1,'( status'=>1,'|| status > )'=>24]),
				'Pallets on the floor'=>$this->count(['enabled'=>1,'location'=>'new'])
			];
		if ($mode<2)
		{
			return $data['All Pallets'];
		}else
		if ($mode>2)
		{
			unset($data['All Pallets']);
			return $data;
		}
		return $data;
	}
	
	/**
	 * Marks selected pallet(s) as compeleted from receipt
	 * 
	 * @param  mixed  pallets          Single pallet pid or array with pallets pid`s
	 * @param  string $order       	   Supplier order (receipt) reference
	 * @param  int	  $statusindel	   In delivery status code
	 * @param  int	  $statuscompleted Completed status code
	 * 
	 * @return bool
	 */
	function completeReceiptPallets($pallets,$order,$statusindel,$statuscompleted)
	{
		$pallets=is_array($pallets) ? $pallets : [$pallets];
		$result=$this->builder()->set('corder',$order)->set('status',$statuscompleted)->whereIn($this->primaryKey,$pallets)->where('sorder',$order)->where('status',$statusindel)->update();
		if (!$result)
		{
			return FALSE;
		}
		$result=$this->builder()->set('corder',$order)->whereIn($this->primaryKey,$pallets)->where('sorder',$order)->update();
		$pallets=$this->getFieldsFromPid($pallets,'pid','reference');
		$result=model('Warehouse/MovementsModel')->addItem(MovementType::receipt_complete,loged_user('username'),null,null,$pallets,$order);
		return $result;
	}
	
	/**
	 * Change order nr on pallets
	 * 
	 * @param  string/array $reference
	 * @param  string       $newOrderRef
	 * @param  bool         $receipt
	 * 
	 * @return bool
	 */
	function changePalletsOrder($reference,$newOrderRef,$receipt=TRUE)
	{
		$receipt=$receipt ? 'sorder' : 'corder';
		$model=$this->builder()->set($receipt,$newOrderRef);
		if (is_array($reference))
		{
			$model=$model->whereIn($this->primaryKey,$reference)
				 ->orWhereIn('reference',$reference);
		}else
		{
			$model=$model->where($receipt,$reference)->orWhere('reference',$reference);
		}
		return $model->update();
	}
	
	function generatePutawayFromOrder($order)
	{
		$order=is_array($order) && array_key_exists('reference', $order) ? $order: null;
		if ($order==null)
		{
			return FALSE;
		}
		$putaway_status=model('Settings/SettingsModel')->get('pallets.putaway_status');
		$pallets=$this->select('pallets.pid,pallets.height,pallets_size.width as pallet_width,pallets_size.length as pallet_len')
					  ->join('pallets_size','pallets_size.name=pallets.size','LEFT')
					  ->filtered(['sorder'=>$order['reference'],'putaway'=>'','status %'=>$putaway_status])
					  ->find();
		$data=[];
		
		foreach ($pallets as $pallet)
		{
			$location=model('Warehouse/LocationModel')->select('locations.code')
													  ->where('locations.height >=',$pallet['height'])
													  ->where('locations.width >=',$pallet['pallet_width'])
													  ->where('locations.length >=',$pallet['pallet_len'])
													  ->where('locations.code NOT IN(select location from pallets)',null,FALSE)
													  ->where('locations.code NOT IN(select putaway from pallets)',null,FALSE)
													  ->first();
			
			$pallet['putaway']='@';
			
			if (is_array($location) && count($location)>0)
			{
				$pallet['putaway']=$location['code'];
			}
			
			if ($this->save(['pid'=>$pallet['pid'],'putaway'=>$pallet['putaway']]))
			{
				model('Tasks/TaskModel')->addNewPutaway($pallet['pid'],$pallet['putaway'],$order['access']);
			}
			
		}
	}
	
	function changePalletStatusFromOrder($order,$status)
	{
		$order=model('Warehouse/OrdersModel')->filtered(['oid'=>$order,'|| reference'=>$order,'enabled'=>1])->first();
		if (!is_array($order))
		{
			return FALSE;
		}
		return $this->filtered(['enabled'=>1,'corder'=>$order['reference'],'status'=>2])->set(['status'=>$status,'operator'=>loged_user('username')])->update();
	}
	
	/**
	 * Returns array of pallets data with given status
	 * 
	 * @param  Int    $status
	 * @param  string $field
	 * @param  string $value
	 * 
	 * @return Array
	 */
	function getPalletsForFormWithStatus($status,$field=null,$value=null)
	{
		$filters=['status'=>$status];
		return $this->getForForm($field,$value,FALSE,null,$filters);
	}

	function countAll()
	{
		return $this->count(['enabled'=>1]);
	} 
	
	function getQtyOfPalletsWithStatus($status)
	{
		if (is_numeric($status))
		{
			$filters=['status'=>$status];
			return $this->count($filters);
		}else
		{
			if (Str::contains($status,','))
			{
				$status=explode(',',$status);
			}
			$arr=[];
			$types=model('Settings/SettingsModel')->get('pallets.pallet_types',TRUE);
			if (!is_array($status))
			{
				$status=array_keys($types);
			}
			foreach ($status as $value) 
			{
				if (array_key_exists($value, $types))
				{
					$arr[$types[$value]]=$this->count(['status'=>$value]);
				}
				
			}
			return $arr;
		}		
	}
	
	/**
	 * Returns array of pallets ready for load
	 * 
	 * @param  string $field
	 * @param  string $value
	 * 
	 * @return Array
	 */
	function getReadyForLoadPalletsForForm($field=null,$value=null)
	{
		$filters=['status'=>model('Settings/SettingsModel')->get('pallets.pallet_type_load')];
		return $this->getForForm($field,$value,FALSE,null,$filters);
	}
	
	/**
	 * Returns array of pallets ready for load
	 * 
	 * @param  string $ref
	 * 
	 * @return Array
	 */
	function getReadyForLoadPallets($filters=[])
	{
		$filters['status']=model('Settings/SettingsModel')->get('pallets.pallet_type_load');
		$filters['enabled']=1;
		return $this->filtered($filters)->find();
	}
	
	/**
	 * Add pallet movement item to audit table
	 * 
	 * @param  string $locfrom
	 * @param  string $locto
	 * @param  string $pallref
	 * 
	 * @return bool
	 */
	function addMovementHistory($locfrom,$locto,$pallref)
	{
		return model('Warehouse/MovementsModel')->addItem(1,loged_user('username'),$locfrom,$locto,$pallref,null,null,'pallets');
	}
	
	/**
	 * Add pallet loading item to audit table
	 * 
	 * @param  bool   $loaded
	 * @param  string $pallref
	 * @param  string $loadref
	 * 
	 * @return bool
	 */
	function addLoadingHistory($loaded,$pallref,$loadref)
	{
		return model('Warehouse/MovementsModel')->addItem($loaded ? 11 : 10,loged_user('username'),null,null,$pallref,$loadref,null,'pallets');
	}
	
	/**
	 * Arcived deleted pallet
	 * 
	 * @param  Array $data
	 * @param string $user
	 * 
	 * @return bool
	 */
	function deletePallets($data,$user)
	{
		if (array_key_exists('pid', $data))
		{
			$data=$data['pid'];
		}
		$movements=model('Warehouse/MovementsModel');
				   
		$sql=$this->set('status',model('Settings/SettingsModel')->get('system.pallet_type_delete'))->set('enabled',0);
		foreach ($this->orWhereIn('pid',$data)->find() as $key => $value) 
		{
			$sql=$sql->orWhere('pid',$value['pid']);
			$movements->save([
						   'mhtype'=>MovementType::delete,
				   		   'mhto'=>'archive',
				  		   'mhuser'=>$user,
				   		   'mhdate'=>formatDate(),
						   'mhfrom'=>$value['location'],
						   'mhref'=>$value['reference']
				   		   ]);
		}
		
		return $sql->update();
	}
}