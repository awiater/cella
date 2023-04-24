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


class CustomFieldsModel extends \CELLA\Models\BaseModel  
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='custom_fields';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'cfid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['type','target','value','targetid'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'cfid'=>	 ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'type'=>	 ['type'=>'INT','constraint'=>'36','null'=>FALSE],
		'target'=>	 ['type'=>'TEXT','null'=>FALSE],
		'value'=>	 ['type'=>'TEXT','null'=>FALSE],
		'targetid'=> ['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	function getFields($target,$targetid,$fieldName=null)
	{
		$targetid=$targetid==null ? 1 : $targetid;
		$tbl_types=model('Settings/CustomFieldsTypesModel');
		$select='custom_fields.value,custom_fields.targetid';
		foreach ($tbl_types->allowedFields as $value) 
		{
			if ($value=='type')
			{
				$select.=','.$tbl_types->table.'.cftid as type';
			}else
			{
				$select.=','.$tbl_types->table.'.'.$value;
			}
		}
		$select.=','.$tbl_types->table.'.cftid as typeid,'.$this->table.'.'.$this->primaryKey;
		$sql= $this->db()
					->table($tbl_types->table)
					->select($select)
					->join($this->table,$tbl_types->table.'.cftid=custom_fields.type'.($targetid !=null ? ' AND '.$this->table.'.targetid'.'='.$targetid : null ),'LEFT')
					->where($tbl_types->table.'.target',$target)
					->where($tbl_types->table.'.enabled',1);
			
		$sql=$sql->get()->getResultArray();/**/	
		if ($fieldName!=null)
		{
			foreach ($sql as $value) 
			{
				if ($value['name']==$fieldName)
				{
					return [$value];
				}
			}
			end_loop:
		}
		return $sql;	
	}
	
	
}