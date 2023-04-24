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
 
namespace CELLA\Models\Warehouse;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\MovementType;

class OrdersModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='orders';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'oid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['reference','type','duein','owner','status','location','palletsqty','operator',
							  'created','completed','ocfg','access','enabled','palletsdisp','inboard'];
	
	protected $validationRules =
	[
		'reference'=>'required|is_unique[orders.reference,oid,{oid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'oid'=>	 		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'reference'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'type'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'duein'=>		['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'owner'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'status'=>		['type'=>'VARCHAR','constraint'=>'5','null'=>FALSE],
		'location'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'operator'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'created'=>		['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'completed'=>	['type'=>'VARCHAR','constraint'=>'12','null'=>TRUE],
		'ocfg'=>		['type'=>'TEXT','null'=>TRUE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE]		
	];
	
	/**
	 * Arcived deleted order/job
	 * 
	 * @param  Array $data
	 * @param string $user
	 * 
	 * @return bool
	 */
	function deleteOrder($data,$user=null)
	{
		if (array_key_exists('oid', $data))
		{
			$data=$data['oid'];
		}
		$user=$user==null ? loged_user('username') : $user;
		$movements=model('Warehouse/MovementsModel');
				   
		$sql=$this->set('status',-1)->set('enabled',0);
		foreach ($this->orWhereIn('oid',$data)->find() as $key => $value) 
		{
			$sql=$sql->orWhere('oid',$value['oid']);
			$movements->save([
						   'mhtype'=>MovementType::delete,
				   		   'mhto'=>'archive',
				  		   'mhuser'=>$user,
				   		   'mhdate'=>formatDate(),
						   'mhref'=>$value['reference'],
						   'type'=>'orders'
				   		   ]);
		}
		
		return $sql->update();
	}

	function deleteOrderWhenNoPallets($data)
	{
		$model=$this;
		if (!array_key_exists('pid', $data))
		{
			return FALSE;
		}
		$data=model('Pallet/PalletModel')->select('GROUP_CONCAT(corder) as oid')
										 ->WhereIn('pid',$data['pid'])
										 ->find();
		return $data;
		if (!is_array($data))
		{
			return FALSE;
		}
		foreach ($data as $value) 
		{
			
		}
		if (array_key_exists('corder', $data))
		{
			$model=$model->where('reference',$data['corder']);
		}
		
		if (array_key_exists('sorder', $data))
		{
			$model=$model->orWhere('reference',$data['sorder']);
		}
		
		$data=$model->find();
	}
	
	/**
	 * Get orders status options array for given type
	 * 
	 * @param  Int   $type Order type (1=Customer, 0=Receipt)
	 * @param  Array $data Optional array with all statuses
	 * 
	 * @return Array
	 */
	public function getOrderStatusList($type,array $data=[],$start=0)
	{
		if (!Arr::keysExists(['orders_status_types_cus','orders_status_types_rec'],$data))
		{
			$data=model('Settings/SettingsModel')->get('orders.orders_status_*',TRUE);
		}
		if ($type==null)
		{
			$data[]=Arr::fromFlatten($data['orders_status_types_cus']);
			$data[]=Arr::fromFlatten($data['orders_status_types_rec']);
			$data=$data[0]+$data[1];
		}else
		if ($type==1 || $type=='1')
		{
			$data=Arr::fromFlatten($data['orders_status_types_cus']);
		}else
		if ($type==0 || $type=='0')
		{
			$data=Arr::fromFlatten($data['orders_status_types_rec']);
		}else
		{
			return [];
		}
		
		ksort($data);
		foreach ($data as $key => $value) 
		{
			if (is_numeric($key) && $key < $start )
			{
				unset($data[$key]);
			}	
		}
		return $data;
	}
	
	public function getOutsandingReceiptsForDash()
	{
		$arr=[];
		$sql_d=model('Pallet/PalletModel')->selectCount('pid')->where('status',100)->where('sorder','reference',null,FALSE)->getCompiledSelect();
		$sql_d=str_replace("'reference'", '`orders`.`reference`', $sql_d);
		$sql_i=model('Pallet/PalletModel')->selectCount('pid')->where('sorder','reference',null,FALSE)->where('status >',-1)->where('status <',100)->getCompiledSelect();
		$sql_i=str_replace("'reference'", '`orders`.`reference`', $sql_i);
		$select=[];
		$select[]='reference';
		$select[]='type';
		$select[]='ocfg';
		$select[]='('.$sql_d.') as dimmed';
		$select[]='('.$sql_i.') as incomp';
		$select=$this->select(implode(',',$select))
					 ->where('type',0)
					 ->where('status >',-1)
					 ->where('status <',2);
		
		foreach ($select->find() as  $value) 
		{
			if (Str::isJson($value['ocfg']))
			{
				$value['ocfg']=json_decode($value['ocfg'],TRUE);
				$value['incomp']=$value['ocfg']['pallqty'];
				$value['dimmed']=$value['ocfg']['boxqty'];
			}
			$arr[$value['reference'].'(Boxes | Pallets)']=$value['dimmed'].' | '.$value['incomp'];
		}

		return $arr;
	}
	
	public function getReceiptsForForm($field='reference',$value='reference')
	{
		return parent::getForForm($field,$value,FALSE,null,['type'=>0]);
	}
	
	public function getCOrdersForForm($field='reference',$value='reference')
	{
		return parent::getForForm($field,$value,FALSE,null,['type'=>1]);
	}
	
	public function getOrdersForMoveForm($field='oid',$value='reference')
	{
		return parent::getForForm($field,$value,FALSE,null,['status <'=>5,'status >'=>-1]);
	}
	
	public function filtered(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
	{
		$settings=explode(',',model('Settings/SettingsModel')->get('scheduler.avaliable_in_scheduler'));
		$result=parent::filtered($filters,$orderby,$paginate,$logeduseraccess,$Validation);
		$palletsqty=model('Pallet/PalletModel')->selectCount('pid')->where('status >-1 AND (sorder='.$this->table.'.reference OR corder='.$this->table.'.reference)',null,FALSE)->getCompiledSelect();
		$palletsqty_non=model('Pallet/PalletModel')->selectCount('pid')->where('(status >-1 AND ( status = 100 OR status = 1)) AND (sorder='.$this->table.'.reference OR corder='.$this->table.'.reference)',null,FALSE)->getCompiledSelect();
		$palletsqty_disp=model('Pallet/PalletModel')->selectCount('pid')->where('status =-2 AND (sorder='.$this->table.'.reference OR corder='.$this->table.'.reference)',null,FALSE)->getCompiledSelect();
		$sel=[];
		$sel[]=$this->table.'.*';
		$sel[]='CONCAT(('.$palletsqty_non."),' / ',(".$palletsqty.')) as palletsqty';
		$sel[]='('.$palletsqty_disp.') as palletsdisp';
		$sel[]='(SELECT IF(COUNT(`ord`.`oid`)>0,1,0) FROM '.$this->table.' as `ord` WHERE `ord`.`oid` IN ("'.implode('","',$settings).'") AND `ord`.`reference`=orders.reference) as inboard';
		
		$result->select(implode(',',$sel));
		
		return $result;
	}
	
	/**
	 * Returns array with all order statuses (receipts and customer orders)
	 * 
	 * @return Array
	 */
	function getAllOrdersStatusValues()
	{
		$status=model('Settings/SettingsModel')->get('orders.orders_*');
		$status['orders_status_types_rec']=Arr::fromFlatten($status['orders_status_types_rec']);
		$status['orders_status_types_cus']=Arr::fromFlatten($status['orders_status_types_cus']);
		return array_combine(array_merge(array_keys($status['orders_status_types_rec']),array_keys($status['orders_status_types_cus'])), array_merge($status['orders_status_types_rec'],$status['orders_status_types_cus']));
	}	
	/**
	 * Returns list of orders with given status
	 * 
	 * @param  Int    $status  Status code
	 * @param  string $field   Column name which will be used as array values
	 * @param  Int    $type    Deteremine type of order (0-receipt, 1-order)
	 * @param  Int    $enabled Deteremine if live (1) orders will be shown
	 * @param  string $access  Determine what access level odrders will be shown
	 * 
	 * @return Array
	 */
	function getOrdersWithStatus($status,$type=null,$field='reference',$enabled=1,$access='@loged_user')
	{
		$arr=[];
		$filters=['access'=>$access,'enabled'=>$enabled,'status'=>$status];
		if ($type!=null && is_numeric($type))
		{
			$filters['type']=$type;
		}
		$model=$this->filtered($filters);		
		foreach($model->find() as $value)
		{
			$arr[]=$value[$field];
		}
		
		return $arr;
	}
	
	function getAllOrdersForEachStatus($field='reference',$enabled=1,$access='@loged_user')
	{
		$select=[];
		foreach (model('Settings/SettingsModel')->get('orders.orders_status_*') as $key => $value) 
		{
			if (is_numeric($value))
			{
				$select[]='('.$this->where('FIND_IN_SET(`access`,'."'".loged_user('accessgroups')."'".') > 0')
								   ->where('enabled',$enabled)
								   ->where('status',$value)
								   ->select("CONCAT(';',GROUP_CONCAT(`".$field."` SEPARATOR ';'))")
								   ->getCompiledSelect().') as '.$key;
			}
		}
		$select=$this->query('SELECT '.implode(',',$select))->getResultArray();
		return count($select)>0 ? $select[0] : $select;
	}
	
	function getLoadPalletsForDimSheet($ref,$filters=null)
	{
		$pallets=model('Pallet/PalletModel')->table;
		$size=model('Pallet/PalletSizeModel')->table;
		$orders=$this->table;
		if (!Str::contains($filters,'status'))
		{
			$filters=$pallets.'.status >-1';
		}
		$columns=[];
		
		$columns[]=$pallets.'.reference';
		$columns[]=$pallets.'.xsnumber';
		$columns[]=$pallets.'.operator';
		$columns[]=$pallets.'.stack as lmh';
		$columns[]=$pallets.'.size';
		$columns[]=$pallets.'.height';
		$columns[]=$pallets.'.location';
		$columns[]=$pallets.'.suppalref';
		$columns[]=$pallets.'.supplier';
		$columns[]=$pallets.".stacknr as 'B/T NR'";
		$columns[]=$pallets.".stackpos as 'B/T'";
		$columns[]="DATE_FORMAT(STR_TO_DATE(".$orders.".completed, '%Y%m%d%k%i'),'%d %M %Y') as 'DIM`D'";//$orders.".completed as 'DIM`D'";
		$columns[]='(('.$pallets.'.height*10)*'.$size.'.width*'.$size.'.length'."/1000000000) as 'cube'";
		$data=model('Pallet/PalletModel')->select(implode(',',$columns))
				   ->join($size,$pallets.'.size='.$size.'.name','Left')
				   ->join($orders,$orders.'.reference='.$pallets.'.corder','Left')
				   ->where($pallets.'.corder',$ref)
				   ->where($filters)
				   ->orderBy('length('.$pallets.".stacknr)".','.$pallets.".stacknr".','.$pallets.".stackpos")
				   ->find();
		return $data;
	}
	
	function getStackingDataFromOrder($ref)
	{
		$ref=$this->filtered([(is_numeric($ref) ? 'oid' : 'refrence')=>$ref])->first();
		$ref['ocfg']=$this->getLoadPalletsForDimSheet($ref['reference'],$ref['status']==-2 ? 'pallets.status=-2' : null);
		return $ref;
		
		if (is_array($ref) && count($ref)>0)
		{
			$ref['ocfg']=json_decode($ref['ocfg'],TRUE);
			return $ref;
		}else
		{
			return [];
		}
	}
	
	/**
	 * Update order status
	 * 
	 * @param  Int    $status  Status code
	 * @param  string $order   Order reference
	 * 
	 * @return bool
	 */
	function setOrderStatus($status,$order)
	{
		$filters=['enabled'=>1];
		if (is_array($order))
		{
			$filters['reference In']=$order;
		}else
		if (is_string($order))
		{
			$filters['reference']=$order;
		}else
		{
			return FALSE;
		}
		$this->filtered($filters);
		if ($status==model('Settings/SettingsModel')->get('orders.orders_status_comp'))
		{
			$this->builder()->set('completed',formatDate());
		}
		return $this->builder()->set('status',$status)->update();
	}
	
	function changeOrderType($reference,$type=1,$createNew=0)
	{
		$reference=$this->filtered(is_numeric($reference) ? ['oid'=>$reference]:['reference'=>$reference])->first();
		
		if (is_array($reference) && Arr::KeysExists($this->allowedFields,$reference))
		{
			if ($createNew==1 && array_key_exists('oid', $reference))
			{
				unset($reference['oid']);
			}
			
			if (array_key_exists('palletsqty', $reference))
			{
				unset($reference['palletsqty']);
			}
			$reference['type']=$type;
			
			return $this->save($reference);
		}
		return FALSE;
	}
	
	/**
	 * Returns qty of live receipts (not completed)
	 * 
	 * @return INT
	 */
	function getLiveReceiptsQty($mode=0)
	{
		$status=model('Settings/SettingsModel')->get('orders.orders_*');
		if ($mode<2)
		{
			return $this->count(['status <>'=>$status['orders_status_comp'],'enabled'=>1,'type'=>0]);
		}else
		{
			return 
			[
				'system.orders.tile_receipt_out'=>$this->count(['status <>'=>$status['orders_status_comp'],'enabled'=>1,'type'=>0]),
				'system.orders.tile_corder_out'=>$this->count(['status <>'=>$status['orders_status_comp'],'enabled'=>1,'type'=>1])
			];
		}		
	}
	
	function createCOrderFromPallet($data)
	{		
		/*$data=model('Pallet/PalletModel')->filtered(['sorder'=>$data,'corder <>'=>null,'corder <>'=>''])->first();
		if (!is_array($data))
		{
			return FALSE; 
		}
		
		if ($this->count(['reference'=>$data['corder']])>0)
		{
			return TRUE;
		}*/
		$record=[
			'reference'=>$data['corder'],
			'type'=>1,
			'owner'=>$data['customer'],
			'status'=>3,
			'location'=>$data['location'],
			'operator'=>loged_user('username'),
			'created'=>formatDate(),
			'access'=>$data['access'],
			'enabled'=>1
		];
		if($this->save($record))
		{
			model('Warehouse/MovementsModel')->addItem(MovementType::receipt_full,loged_user('username'),null,null,$data['corder'],null,formatDate(),'orders');
		}
	}
	
	
}