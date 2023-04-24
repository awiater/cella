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
 
namespace CELLA\Models\Tasks;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\MovementType;

class TaskModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='tasks';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'tid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['tref','type','status','action','assign','done','access','enabled'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'tid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'tref'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'type'=>	['type'=>'INT','constraint'=>'11','null'=>TRUE],
		'status'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'action'=>	['type'=>'TEXT','null'=>TRUE],
		'assign'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'done'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>TRUE],
		'access'=>	['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	/**
	 * Add new task to DB
	 * 
	 * @param  string $tref
	 * @param  Int    $type
	 * @param  string $access
	 * @param  string $assign
	 */
	 function addNew($tref,$type,$access,$action=null,$assign=null)
	 {
	 	return $this->save([
	 		'tref'=>$tref,
	 		'type'=>$type,
	 		'status'=>0,
	 		'assign'=>$assign,
	 		'done'=>null,
	 		'action'=>$action,
	 		'access'=>$access==null ? loged_user('accessgroups'): $access,
	 		'enabled'=>1
	 	]);
	 }
	 
	 /**
	 * Add new putaway task to DB
	 * 
	 * @param  string $tref
	 * @param  string $access
	 */
	 function addNewPutaway($tref,$action,$access=null)
	 {
	 	return $this->addNew($tref,MovementType::putaway,$access,$action);
	 }
	 
	 function addNewPalletMovement($tref,$action,$access=null,$type=MovementType::move)
	 {
	 	return $this->addNew($tref,$type,$access,$action);
	 }
	 
	 function createMovementTasksForOrder($order,$status)
	 {
	 	$order=model('Warehouse/OrdersModel')->filtered(['enabled'=>1,'reference'=>$order,'status'=>$status])->first();
		
		if (!is_array($order))
		{
			return FALSE;
		}
		
		$pallets=model('Pallet/PalletMolel')->filtered(['enabled'=>1,'corder'=>$order['reference'],'location <>'=>$order['location']])->find();
		if (!is_array($pallets))
		{
			return FALSE;
		}
		$tasks=[];
		foreach ($pallets as $value) 
		{
			$tasks[]=
			[
				'tref'=>$value['reference'],
				'type'=>MovementType::move,
				'action'=>$order['location'],
				'access'=>$value['access'],
				'enabled'=>1
			];
		}
		return $this->insertBatch($tasks);
	 }
	 
	 /**
	  * Create movement tasks when load is in lading state
	  * 
	  * @param  string $ref
	  * 
	  * @return bool
	  */
	 function createMovementTasksForLoad($ref)
	 {
	 	$ref=model('Collections/LoadModel')->filtered(['enabled'=>1,'reference'=>$ref,'status'=>1])->first();
		
		if (!is_array($ref))
		{
			return FALSE;
		}
		
		$pallets=model('Collections/ItemsModel')->getLoadPallets($ref);
		if (!is_array($pallets))
		{
			return FALSE;
		}
		$tasks=[];
		foreach ($pallets as $value) 
		{
			$tasks[]=
			[
				'tref'=>$value['reference'],
				'type'=>MovementType::load_loading,
				'action'=>$ref['location'],
				'access'=>$value['access'],
				'enabled'=>1
			];
		}
		
		return $this->insertBatch($tasks);
	 }
	 
	 function completeTasks($refs,$date=null)
	 {
	 	$date=$date==null ? formatDate() : $date;
		$refs=is_array($refs) ? $refs : [$refs];
		return $this->builder()
					->set('status',1)
					->set('done',$date)
					->whereIN('tref',$refs)
					->update();
	 }
}