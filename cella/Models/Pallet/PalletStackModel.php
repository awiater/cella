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

class PalletStackModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='pallets_stack';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'ptid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['code','height','desc','enabled'];
	
	protected $validationRules =
	 [
	 	'code'=>'required|is_unique[pallets_stack.code,ptid,{ptid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'ptid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'code'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'height'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'desc'=>	['type'=>'TEXT','constraint'=>'25','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		
		
	];
}