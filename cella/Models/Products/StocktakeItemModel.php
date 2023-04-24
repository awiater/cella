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
 
namespace CELLA\Models\Products;

use \CELLA\Helpers\MovementType;

class StocktakeItemModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='stocktake_items';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'siid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['stocktake','operator','status','created','completed','started','location','product',
							   'pallet','new_pallet','qty','new_qty','match','status','access','enabled'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'siid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'stocktake'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'foreignkey'=>['stocktakes','reference','CASCADE','CASCADE']],
		'operator'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE,'foreignkey'=>['users','username','CASCADE','SET NULL']],
		'status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'location'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE,'foreignkey'=>['locations','code','CASCADE','SET NULL']],
		'created'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'completed'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'started'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'product'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'pallet'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'new_pallet'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'qty'=>			['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'new_qty'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'match'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	function getItemsFromStockTake($ref,$enabled=1)
	{
		$arr=[];
		$filters=['access'=>'@loged_user','stocktake'=>$ref];
		if (is_numeric($enabled))
		{
			$filters['enabled']=$enabled;
		}
		foreach ($this->filtered($filters)->find() as $value) 
		{
			
			$arr[]=$value;
		}
		return $arr;
	}
	
	function isMatch($old,$new,$empty=null)
	{
		$empty=$empty==null ? model('Settings/SettingsModel')->get('stocktakes.empty_pallet_id') : $empty;
		$new=str_replace($empty, '', $new);
		$old=strcmp($old,$new);
		if ($old!==0)
		{
			if (model('Pallet/PalletModel')->count(['reference'=>$new])<1)
			{
				$old=0;
			}else
			{
				$old=2;
			}
		}else
		{
			$old=1;
		}
		return $old;
	}
	
	function changeActiveMultiple($ref,$enabled)
	{
		$this->where('stocktake',$ref)->set('enabled',$enabled)->update();
	}
}