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
 
namespace CELLA\Models\Owners;

class CustomerModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='customers';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'cid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['code','name','ordref','address','iseu','enabled'];
	
	protected $validationRules =
	 [
	 	'code'=>'required|is_unique[suppliers.code,sid,{sid}]',
	 	'name'=>'required|is_unique[suppliers.name,sid,{sid}]',
	 	'enabled'=>'required',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cid'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'code'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'ordref'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'address'=>		['type'=>'TEXT','constraint'=>'25','null'=>FALSE],
		'iseu'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	function getNextReference($code,$timestamp=null)
	{
		//$palletid=model('Pallet/PalletModel')->filtered(['enabled'=>1,'status <'=>100,'status >'=>1,'customer'=>$code])->first();
		$palletid=model('Warehouse/OrdersModel')->filtered(['enabled'=>1,'status <'=>5,'status >'=>0,'owner'=>$code])->first();
		
		if (is_array($palletid) && array_key_exists('reference', $palletid) && strlen($palletid['reference'])>0)
		{
			return $palletid['reference'];
		}
		$timestamp=$timestamp==null ? time() : $timestamp;
		$ref=$this->filtered(['code'=>$code,'|| name'=>$code])->first();
		
		if ($ref==null)
		{
			return null;
		}
		$ref=$ref['ordref'];
		$int_val=preg_replace('/[^0-9]/', '', $ref);
		
		if (is_numeric($int_val))
		{
			$int=ltrim($int_val,'0');
			$int++; 
			$ref= substr($ref,0,strlen($ref)-strlen($int_val)).str_pad($int++, strlen($int_val), '0', STR_PAD_LEFT);
		}
		$ref=strftime($ref,$timestamp);
		if (model('Warehouse/OrdersModel')->count(['reference'=>$ref])>0)
		{
			$ref.=formatDate('now',FALSE,'Hi');
		}
		return $ref;
	}

	function setNewReference($code,$ref=null,$type=0)
	{
		return true;
		$type=$type=='1' ? 'ordref' : 'pallref';
		$ref=$ref==null ? $this->getNextReference($code) : $ref;
		return $this->set($type,$ref)->where('code',$code)->update();
	}
}