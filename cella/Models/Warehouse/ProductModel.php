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


class ProductModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='products';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'pid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['code','supplier','desc','barcode','height','width','length','weight','rule','enabled'];
	
	protected $validationRules =
	[
		'code'=>'required|is_unique[products.code,pid,{pid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'pid'=>	 		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'code'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'supplier'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE,'foreignkey'=>['suppliers','code','CASCADE','CASCADE']],
		'desc'=>		['type'=>'TEXT','null'=>FALSE],
		'height'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'width'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'length'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'weight'=>		['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'rule'=>		['type'=>'TEXT','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	
}