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

class PalletSizeModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='pallets_size';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'psid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','width','length','desc','type','enabled'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[pallets_size.name,psid,{psid}]',
	 	'width'=>'required|integer',
	 	'length'=>'required|integer',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'psid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'width'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'length'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'desc'=>	['type'=>'TEXT','constraint'=>'25','null'=>FALSE],
		'type'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		
		
	];
}