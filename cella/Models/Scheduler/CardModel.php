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
 
namespace CELLA\Models\Scheduler;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\MovementType;

class CardModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='sch_cards';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'scid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['reference','type','duein','owner','status','location','palletsqty','operator',
							  'created','completed','ocfg','access','enabled','palletsdisp'];
	
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
		'scid'=>	 	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'reference'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'type'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'duein'=>		['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'owner'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'location'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'operator'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'created'=>		['type'=>'VARCHAR','constraint'=>'12','null'=>FALSE],
		'completed'=>	['type'=>'VARCHAR','constraint'=>'12','null'=>TRUE],
		'ocfg'=>		['type'=>'TEXT','null'=>TRUE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE]		
	];
	
	function getAllCards($dateFrom,$dateTo,$disableDates=FALSE)
	{
		$filters=[];
		$cards=[];
		$jobs=model('Warehouse/OrdersModel');
		$pallets=model('Pallet/PalletModel')->table;
		$settings=model('Settings/SettingsModel')->get('scheduler.*');
		$jobscolors=Arr::fromFlatten($settings['orders_status_types_colors']);
		
		if (!$disableDates)
		{
			$jobs=$jobs->where('duein >=',$dateFrom)
				   ->where('duein <=',$dateTo);
		}
		$jcols=[];
		$jcols[]='*';
		$jcols[]='(SELECT COUNT(pid) FROM '.$pallets.' as `pal` WHERE `pal`.sorder='.$jobs->table.'.reference OR `pal`.corder='.$jobs->table.'.reference) as pallqty';
		$jcols[]='(SELECT `csm`.`iseu` FROM customers as `csm` WHERE `csm`.`code`='.$jobs->table.'.owner) as iseu';
		$jcols[]='(SELECT count(`load`.`reference`) FROM vw_ordersinloads as `load` WHERE `load`.`reference`='.$jobs->table.'.reference) as isinload';
		$jcols[]='(SELECT GROUP_CONCAT(`cf`.`value`) FROM custom_fields as `cf` WHERE `cf`.`targetid`='.$jobs->table.'.oid AND `cf`.`type`='.$settings['linked_orders'].') as linked_id';
		$jcols[]='(SELECT GROUP_CONCAT(`cff`.`targetid`) FROM custom_fields as `cff` WHERE `cff`.`value` LIKE CONCAT("%",'.$jobs->table.'.reference,"%") AND `cff`.`type`='.$settings['linked_orders'].') as linked_ref';
		$jobs=$jobs->select(implode(',',$jcols))
				   ->groupStart()
				   	->where('status <',5)
				   	->where('status >=',2)
				   	->whereIn('oid',explode(',',$settings['avaliable_in_scheduler']))
				   ->groupEnd()
				   ->orGroupStart()
				   	->where('status <',2)
				   	->where('status >=',0)
				   ->groupEnd()
				   ->find();
				   
		$today=formatDate();
		foreach ($jobs as $key=>$value)
		{
			
			$value['flag']=array_key_exists('flag', $value) && strlen($value['flag']) > 0 ? $value['flag'] :'in';
			$value['cardtpl']='in';
			$value['ocfg']=json_decode($value['ocfg'],TRUE);
			$value['url']=url('Scheduler','predelivery',[$value['oid']],['refurl'=>current_url(FALSE,TRUE)]);
			if (strlen($value['duein']) > 8)
			{
				$value['dueinraw']=substr($value['duein'],0,8);
			}
			if (array_key_exists($value['status'], $jobscolors))
			{
				$value['color']=explode(':',$jobscolors[$value['status']]);
			}else
			{
				$value['color']=['#FFF','0,0,0'];
			}
			
			if ($value['status'] > 2 && strlen($value['linked_ref']) > 0)
			{
				$value['color']=explode(':',$jobscolors[0]);
			}
			
			if (count($value['color']) < 2)
			{
				$value['color'][1]='0,0,0';
			}	
			
			if ($value['status'] >= 1)
			{
				$value['ocfg']=['pallqty'=>$jobs[$key]['pallqty']];
				$value['url']=url('orders','order',[$value['oid']],['refurl'=>current_url(FALSE,TRUE)]);
			}
			
			if ($value['status'] > 2)
			{
				$value['flag']='dim'.(is_numeric($value['flag']) ? '.'.$value['flag']:'');
				$value['cardtpl']='dim';
			} 
			
			if ($value['status'] >= 1 && $value['status'] < 2)
			{
				$value['flag']='rec';
				$value['cardtpl']='in';
			} 
			
			if ($value['status'] == 5)
			{
				$value['flag']='dimmed';
				$value['cardtpl']='dim';
			} 
			
			if ($value['status'] < 2)
			{
				$value['flag']=$value['flag'];
				if ($today>$value['duein'] && ($settings['show_expired']==1 && $settings['show_expired']=='1'))
				{
					$value['color']=['#f54245','255,255,255'];
					$value['overdue']=$today-$value['duein'];
				}
				
			}else
			{
				//$value['flag']=$value['flag'].$value['status'];
			}
			
			
			$value['id']=$value['oid'];
			if ($value['status'] < 2 || ($value['status'] >=2 && Str::contains($settings['avaliable_in_scheduler'],$value['reference'])))
			{
				//$cards[$value['flag']][]=$value;
			}
			$cards[$value['flag']][]=$value;
		}
		
		$coll=model('Collections/LoadModel');
		$coll_it='SELECT `view`.`stackqty` from vw_loads_stacks_pallets_qty as `view` WHERE `view`.`loadref`='.$coll->table.'.reference';
		
		$coll=$coll->where($coll->table.'.status','2')
				   ->where($coll->table.'.duein >=',substr($dateFrom,0,8).'0000')
				   ->where($coll->table.'.duein <=',substr($dateTo,0,8).'0000')
				   ->select($coll->table.'.*,('.$coll_it.') as pallqty')
				   ->find();
		$coll=$this->builder('vw_schedcompleted')
					->where('duein >=',substr($dateFrom,0,8).'0000')
				   	->where('duein <=',substr($dateTo,0,8).'0000')
					->get()
					->getResultArray();
		//dump($coll);exit;
		if (array_key_exists('archived', $jobscolors))
		{
				$color=explode(':',$jobscolors['archived']);
		}else
		{
				$color=['#FFF','0,0,0'];
		}
			
		if (count($color) < 2)
		{
				$color[1]='0,0,0';
		}	
		foreach ($coll as $key=>$value) 
		{
			$value['type']='out';
			$value['cardtpl']='out';
			$value['id']=$value['lid'];
			$value['loaded']=convertDate($value['loaded'],'DB','Ymd');
			$value['duein']=convertDate($value['duein'],'DB','Ymd');
			$value['color']=$color;
			$value['cardtpl']='completed';
			$cards['done'][]=$value;
		}
		
		/*dimmed*/
		$jcols=[];
		$jcols[]='vw_schreadyorders.*';
		//$jcols[]='(SELECT `ldo`.`duein` FROM `loads` as `ldo` WHERE `ldo`.`reference`=`vw_schreadyorders`.`loadref`) as duein';
		//$jcols[]='(SELECT `ldo`.`lid` FROM `loads` as `ldo` WHERE `ldo`.`reference`=`vw_schreadyorders`.`loadref`) as loadid';
		//$jcols[]='(SELECT COUNT(`vwol`.`oid`) FROM vw_ordersinloads AS `vwol` WHERE `vwol`.loadref=`vw_schreadyorders`.`loadref`) AS `orders_qty`';
		if (!$disableDates)
		{
			//$dateFrom=formatDate('now','startOfWeek');
			//$dateTo=formatDate($dateFrom,'+ 7 days');
		}
		$coll=$this->builder('vw_schreadyorders')
				   ->select(implode(',',$jcols))
				   ->groupStart()
				   ->where('inload',0)
				   ->orwhere('invoiced',0)
				   ->groupEnd()
				   ->get()
				   ->getResultArray();
		$out=[];
		foreach ($coll as $key=>$value) 
		{
			$value['cardtpl']='out';
			
			if (strlen($value['duein']) > 8)
			{
				$value['duein']=substr($value['duein'],0,8);
			}			
			
			if (array_key_exists($value['status'], $jobscolors))
			{
				$value['color']=explode(':',$jobscolors[$value['status']]);
			}else
			{
				$value['color']=['#FFF','0,0,0'];
			}
			
				
			if (count($value['color']) < 2)
			{
				$value['color'][1]='0,0,0';
			}
			
			$cards['dimmed'][]=$value;
		}
		
		/*collection live*/
		$coll=$this->builder('vw_schedcollections')
				   ->where('duein >=',substr($dateFrom,0,8).'0000')
				   ->where('duein <=',substr($dateTo,0,8).'0000')
				   ->where('invoiced','orders_qty',FALSE)
				   ->get()
				   ->getResultArray();
		
		foreach ($coll as $key=>$value) 
		{
			$value['cardtpl']='out';
			$value['status']='out';
			
			if (strlen($value['duein']) > 8)
			{
				$value['duein']=substr($value['duein'],0,8);
			}			
			
			if (array_key_exists('collection', $jobscolors))
			{
				$value['color']=explode(':',$jobscolors['collection']);
			}else
			{
				$value['color']=['#FFF','0,0,0'];
			}
			
			$value['id']=$value['lid'];
				
			if (count($value['color']) < 2)
			{
				$value['color'][1]='0,0,0';
			}
			if ($today > $value['duein'].'0000' && ($settings['show_expired']==1 && $settings['show_expired']=='1'))
			{
				$value['overdue']=1;
				$value['color'][0]='#FF0000';
			}
			$cards['out'][]=$value;
		}
		return $cards;
	}
	/**
	 * Getting cards templates as array
	 * 
	 * @return array
	 */
	function getCardsTemplates($rawData=FALSE)
	{
		$arr=[];
		$data=model('Documents/DocumentModel')->filtered(['type'=>'SCHCARD','enabled'=>1])->find();
		if ($rawData){return $data;};
		foreach ($data as $value) 
		{
			$arr[$value['name']]=$value['text'];	
		}
		return $arr; 
	}
	
	function getCardColors($data=null)
	{
		$data=$data=null ? model('Settings/SettingsModel')->get('scheduler.orders_status_types_colors',TRUE) : $data;
		if (is_string($data))
		{
			$data=Arr::fromFlatten($data);
		}
		return is_array($data) ? $data : [];
	}
	
	function getDeliveryStatuses($type,$max=2,$start=-1)
	{
		$colors=model('Settings/SettingsModel')->get('scheduler.orders_status_types_colors',TRUE);
		$text=model('Warehouse/OrdersModel')->getOrderStatusList($type,model('Settings/SettingsModel')->get('orders.orders_status_*'));
		$arr=[];
		foreach ($text as $key=>$value) 
		{
			if ($key<$max && $key>$start)
			{
				$arr[$key]=
				[
					'icon'=>'fas fa-square-full',
					'icon_color'=>Str::before($colors[$key],':'),
					'value'=>$value,
					'color'=>$colors[$key],
				];
			}
		}
		return $arr;
	}

	function getLinkedJobs()
	{
		$arr=[];
		
		foreach (model('Settings/CustomFieldsModel')->where('type',model('Settings/SettingsModel')->get('scheduler.linked_orders'))->find() as $value) 
		{
			$arr[$value['value']][]=$value;
		}
		return $arr;
	}
	
	function getLinkedJobs1(array $ids)
	{
		$orders=model('Warehouse/OrdersModel');
		$cf=model('Settings/CustomFieldsModel')->table;
		$fieldType='4';
		$sel=[];
		$sel[]=$orders->table.'.reference';
		$sel[]=$orders->table.'.owner';
		$sel[]=$orders->table.'.status';
		$sel[]=$orders->table.'.oid';
		$sel[]='(SELECT GROUP_CONCAT(`cf`.`targetid`) FROM `'.$cf.'` as cf WHERE `cf`.`type`='.$fieldType.' AND `cf`.`value` LIKE CONCAT("%",'.$orders->table.'.`reference`,"%")) as `linked`';
		return $orders->select(implode(',',$sel))
					  ->whereIn('oid',$ids)
			          ->find();
	}
	
}