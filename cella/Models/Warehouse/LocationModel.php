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

class LocationModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='locations';
	
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
	protected $allowedFields=['zone','code','row','column','width','length','height','enabled','access'];
	
	protected $validationRules =
	[
		'code'=>'required|is_unique[locations.code,lid,{lid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'lid'=>	 	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'zone'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'code'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'row'=>	 	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'column'=> 	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'width'=>   ['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'length'=>  ['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'height'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		'access'=> 	['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
	];
	
	/**
	 * Returns all loactions from goods out zone
	 * 
	 * @param string $field
	 * @param string $value
	 * 
	 * @return Array
	 */
	function getGoodsOutLocations($field='code',$value='code')
	{
		return $this->getForForm($field=null,$value=null,$addEmpty=FALSE,$defValue=null,['zone'=>model('Settings/SettingsModel')->get('locations.goods_out_zone')]);
	}
	
	/**
	 * Returns array with zone names
	 * 
	 * @return Array
	 */
	function getZones(array $filters=[])
	{
		$arr=[];
		$this->filtered($filters);
		foreach ($this->builder()->select('zone')->distinct('zone')->get()->getResultArray() as $value) 
		{
			$arr[$value['zone']]=$value['zone'];
		}
		return $arr;
	}
	
	/**
	 * Returns array with zone names
	 * 
	 * @return Array
	 */
	function getRows(array $filters=[])
	{
		$arr=[];
		$this->filtered($filters);
		foreach ($this->builder()->select('row')->distinct('row')->get()->getResultArray() as $value) 
		{
			$arr[$value['row']]=$value['row'];
		}
		return $arr;
	}

	/**
	 * Returns array with zone names
	 * 
	 * @return Array
	 */
	function getColumns(array $filters=[])
	{
		$arr=[];
		$this->filtered($filters);
		foreach ($this->builder()->select('column')->distinct('column')->get()->getResultArray() as $value) 
		{
			$arr[$value['column']]=$value['column'];
		}
		return $arr;
	}

	function getLocationsForStockTake($mode,$value)
	{
		$pall=model('Pallet/PalletModel')->table;
		$select=[$this->table.'.*,'];
		$select[]=$pall.'.customer';
		$select[]=$pall.'.reference';
		$select[]=$pall.'.size';
		$select[]=$pall.'.corder';
		
		$result=$this->select(implode(',',$select));
		//'size','occ'=>,'corder'=>'','all'
		if (in_array($mode, ['zone','row','column']))
		{
			$result=$result->join($pall,$pall.'.location='.$this->table.'.code','Left')
						   ->where($mode,$value);
		}else
		if (in_array($mode, ['size','corder']))
		{
			$result=$result->join($pall,$pall.'.location='.$this->table.'.code','Left')
						   ->where($pall.'.'.$mode,$value);
		}else
		if($mode=='occ')
		{
			$result=$result->join($pall,$pall.'.location='.$this->table.'.code','INNER');
		}else
		{
			$result=$result->join($pall,$pall.'.location='.$this->table.'.code','Left');
		}			 
		return $result->find();
	}
}