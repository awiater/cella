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
 
namespace CELLA\Models\Documents;


class DocumentModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='documents';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'did';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','desc','path','text','orientation','type','dataact','enabled','access'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'did'=>			['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'type'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'desc'=>		['type'=>'TEXT','null'=>FALSE],
		'dataact'=>		['type'=>'TEXT','null'=>FALSE],
		'path'=>		['type'=>'TEXT','null'=>FALSE],
		'text'=>		['type'=>'TEXT','null'=>FALSE],
		'orientation'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
}
?>