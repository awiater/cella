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


class AlocationModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products_alocation';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'paid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['code','qty','corder','sorder','pallet'];
	
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
		'paid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'code'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'qty'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		'corder'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'sorder'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'pallet'=>	['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE,'foreignkey'=>['pallets','reference','CASCADE','CASCADE']],	
	];
	
	
}