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

class StocktakeModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='stocktakes';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'stid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['reference','operator','status','created','completed','started','type','type_cfg','progress','access','enabled'];
	
	protected $validationRules =
	 [
	 	'reference'=>'required|is_unique[stocktakes.reference,stid,{stid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'stid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'reference'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE],
		'operator'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE,'foreignkey'=>['users','username','CASCADE','SET NULL']],
		'status'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'created'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'completed'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'started'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'type'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'type_cfg'=>	['type'=>'TEXT','null'=>TRUE],
		'progress'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];	
	
	/**
	 * Generate and returns next stocktake reference number
	 * 
	 * @return string
	 */
	function generateReference($patern=null,$saveNew=TRUE)
	{
		$ref=$patern==null ? model('Settings/SettingsModel')->get('stocktakes.next_id'):$patern;
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
			model('Settings/SettingsModel')->write('next_id',$ref);
		}
		
		return $ref;
	}
	
	function changeStockTakeStatus($ref,$status)
	{
		$ref=$this->where('reference',$ref)->first();
		if (!is_array($ref) || (is_array($ref) && count($ref) <1))
		{
			return FALSE;
		}
		
		if (!in_array($status, [0,1,2]))
		{
			return FALSE;
		}
		$model=$this->where('reference',$ref['reference'])->set('status',$status);
		if ($status==2 || $status=='2')
		{
			$model->set('completed',formatDate());
		}else
		if ($status==1 || $status=='1')
		{
			$model->set('started',formatDate());
		}
		if ($model->update())
		{
			$ref['status']=$status;
			model('Tasks/RuleModel')->actionRuleByTrigger('StockTake_save',[$ref]);
		}
	}

	function updateProgress($ref,$beforeSave=FALSE)
	{
		$items=model('Products/StocktakeItemModel');
		$all=$items->count(['stocktake'=>$ref,'enabled'=>1]);
		$items=$items->count(['stocktake'=>$ref,'enabled'=>1,'status'=>2]);
		if ($beforeSave)
		{
			$items++;
		}
		$items=$items*100;
		$items=$items/$all;
		$this->where('reference',$ref)->set('progress',$items)->update();
		if ($items>=100)
		{
			$this->changeStockTakeStatus($ref,2);
			return TRUE;
		}
		return FALSE;
	}
}