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
 
namespace CELLA\Models\Settings;

class CustomFieldsTypesModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='custom_fields_types';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'cftid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','type','target','enabled','access','required'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[custom_fields_types.name,cftid,{cftid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cftid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'type'=>	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'target'=>	['type'=>'TEXT','null'=>FALSE],
		'required'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	/**
	 * Custom fields types
	 * @var Array
	 */
	 private $_fieldsTypes=['InputField'=>'Input Field','TextArea'=>'Multi Lines Input Field','AcccessField'=>'Acccess Field','YesNo'=>'YesNo'];
	
	function getFieldTypes($type=null)
	{
		if ($type==null)
		{
			return $this->_fieldsTypes;
		}
		if (array_key_exists($type, $this->_fieldsTypes))
		{
			return $this->_fieldsTypes[$type];
		}else
		{
			return null;
		}
	}
}