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


class MovementsModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='movements_history';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'mhid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['mhtype','mhdate','mhuser','mhfrom','mhto','mhref','mhinfo','type'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'mhid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'mhtype'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'mhdate'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'mhuser'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'foreignkey'=>['users','username','CASCADE','NO ACTION']],
		'mhfrom'=>	['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'mhto'=>	['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
		'mhref'=>	['type'=>'VARCHAR','constraint'=>'300','null'=>FALSE],
		'mhinfo'=>	['type'=>'TEXT','null'=>TRUE],
		'type'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
	];
	
	function addItem($mhtype,$mhuser,$mhfrom,$mhto,$mhref,$mhinfo=null,$mhdate=null,$type=null)
	{
		$mhdate=$mhdate==null ? formatDate() : $mhdate;
		$mhref=is_array($mhref) ? $mhref : [$mhref];
		$arr=[];
		foreach ($mhref as $value) 
		{
			$arr[]=[
				'mhtype'=>$mhtype,
				'mhdate'=>$mhdate,
				'mhuser'=>$mhuser,
				'mhfrom'=>$mhfrom,
				'mhto'=>$mhto,
				'mhref'=>$value,
				'mhinfo'=>$mhinfo,
				'type'=>$type	
			];
		}
		return $this->builder()->insertBatch($arr);
	}
	
	function getByRefernce($ref)
	{
		return $this->filtered(['mhref'=>$ref],'mhdate DESC',config('Pager')->perPage);
	}
	
	function getKPIData($from,$to,$user,$limit=5)
	{
		$data= $this->db()
			        ->table('vw_kpiusersactivity')
					->where('mhuser',$user)
					->where('mhdate >=',$from)
					->where('mhdate <=',$to)
					->where('mhtype <>',null);
		if ($limit> 0)
		{
			$data=$data->limit($limit);
		}
		return $data->get()
					->getResultArray();
	}

	function getMovementsTypesForKPI($data,$movementType=null)
	{
		$arr=[];
		$movements=model('Settings/SettingsModel')->get('movement_types.*');
		foreach ($data as $key => $value) 
		{
			if ($value['mhtype']!=null)
			{
				$value['mhtype']=lang($value['mhtype']);
			
				if (!array_key_exists($value['mhtype'], $arr))
				{
					$arr[$value['mhtype']]=1;
				}else
				{
					$arr[$value['mhtype']]=$arr[$value['mhtype']]+1;
				}
			}
		}
		
		if (is_numeric($movementType))
		{
			$movementTypes=array_keys($arr);
			if (array_key_exists($movementType, $movementTypes))
			{
				$movementType=$movementTypes[$movementType];
			}		
		}
		if ($movementType!=null)
		{
			if (array_key_exists($movementType, $arr))
			{
				$arr=$arr[$movementType];
			}else
			{
				$arr=0;
			}
		}
		return $arr;
	}
	
	function getUserDataByType($user,$from,$to,$filter=[],$status=[])
	{
		$filter=is_array($filter) ? $filter : [];
		$status=is_array($status) ? $status : [];
		$from=strlen($from)>8 ? substr($from,0,8) : $from;
		$to=strlen($to)>8 ? substr($to,0,8) : $to;
		$user=str_replace(['"'], [''],$user);
		$user=strlen($user) < 1 ? loged_user('username') : $user;
		$sel=[];
		$sel[]=$this->table.'.mhuser as operator';
		$sql=$this->where($this->table.'.mhdate >= ','%from')
				  ->where($this->table.'.mhdate <=','%to');
		if (strlen($user) > 0 && $user!='0')
		{
			$sql=$sql->where($this->table.'.mhuser',$user)->groupBy([$this->table.'.mhuser']);
		}else
		{
			$sql=$sql->groupBy([$this->table.'.mhuser'])->groupBy([$this->table.'.mhdate']);
		}
		$ssql='SUM((SELECT count(`mh1`.`mhtype`) FROM `movements_history` as `mh1` WHERE `mh1`.`mhuser`=`mh`.`mhuser` AND `mh1`.`mhtype`=%mht% AND `mh1`.`mhdate` >=%from AND `mh1`.`mhdate` <= %to)) as `%value%`';
		$ssql='SUM((SELECT count(`mh1`.`mhtype`) FROM `movements_history` as `mh1` WHERE `mh1`.`mhuser`=`mh`.`mhuser` AND `mh1`.`mhtype`=%mht% AND `mh1`.`mhdate` =%from)) as `%value%`';
		$ssql='SUM((IF (`mh`.`mhtype`=%mht%,1,0))) as %value%';
		$status=count($status) >0 ? $status :model('Settings/SettingsModel')->getMovementTypes();
		if (count($filter) > 0)
		{
			$filter_tmp=[];
			foreach ($filter as $key => $value)
			{
				if (array_key_exists($value, $status))
				{
					$filter_tmp[$value]=$status[$value];
				}
			}
			if (count($filter_tmp)>0)
			{
				$filters=$filter_tmp;
			}else
			{
				$filter=$status;
			}
		}else
		{
			$filter=$status;
		}
		
		foreach ($filter as $key => $value) 
		{
			$value=str_replace(' ', '_', $value);
			$tbl='`fld'.$key.'`';
			$sel[]=str_replace(['`mh1`','%mht%','`mh`','%value%','%from'], [$tbl,$key,'`'.$this->table.'`',$value,'`movements_history`.`mhdate`'],$ssql);
		}
		
		//$sel[]='(SELECT COUNT(`ord`.`oid`) FROM `orders` as `ord` WHERE `ord`.`created` >=%from AND `ord`.`created` <=%to ) as maxcustomer';
		//$sel[]='(SELECT COUNT(`vw`.`created`) FROM `vw_kpipalletsmoreinfo` as `vw` WHERE `vw`.`created` >=%from AND `vw`.`created` <=%to ) as maxpallets';
		$sql=$sql->select(implode(',',$sel));
		$sql=$sql->getCompiledSelect();
		
		$arr=[];//['labels'=>[],'data'=>[]];
		for ($i=0; $i < formatDate([$to,$from],'diff','Ymd')->getMonths(); $i++) 
		{ 
			$start=formatDate($from,'+ '.$i.' months','Ymd');
			$end=formatDate($start,'+ 1 months','Ymd');
			$data=str_replace(['%from','%to'],[$start,$end],$sql);
			$key=convertDate($start,'Ymd','M Y');
			$data=$this->db->query($data)->getResultArray();
			//$arr['labels'][]=$key;
			
			if (is_array($data) && array_key_exists(0, $data))
			{
				if (count($data)==1)
				{
					$data=$data[0];
					$arr[$key]=$data;
					$arr[$key]['month']=$key;
				}else
				{
					foreach ($data as $kkey=>$value) 
					{
						$data[$kkey]['month']=$key;
					}
					$arr=$data;
				}
				
			}
		}
		return $arr;
	}
	
	public function installstorage($install=FALSE)
	{
		if ($install)
		{
			parent::installstorage();
		}
	}
}