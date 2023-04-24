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
 
namespace CELLA\Models\Auth;

use \CELLA\Helpers\Strings as Str;

class UserGroupModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='users_groups';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'ugid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['ugname','ugdesc','enabled','ugref','ugperms','ugview','ugstate','ugmodify','ugedit','ugcreate','ugdelete','ugsettings'];
	
	protected $validationRules =
	 [
	 	'ugname'=>'required|is_unique[users_groups.ugname,ugid,{ugid}]',
	 ];
	
	protected $validationMessages = [
        'ugname'        => [
            'is_unique' =>'system.auth.groups_error_unique',
        ]
    ];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'ugid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'ugname'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE,'unique'=>TRUE],
		'ugdesc'=>		['type'=>'TEXT','null'=>FALSE],
		'enabled'=>		['type'=>'TEXT','null'=>FALSE],
		'ugref'=>		['type'=>'VARCHAR','constraint'=>'36'],
		'ugperms'=>		['TEXT','null'=>FALSE],	
		'ugview'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugstate'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugmodify'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugedit'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugcreate'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugdelete'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		'ugsettings'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>0],
		
	];
	function getForProfile()
	{
		$filters=[];
		if (!Str::contains(loged_user('accessgroups'),$this->getSuperAdminsGroup()))
		{
			$filters=['ugref In'=>explode(',',loged_user('accessgroups'))];
		}
	  	return $this->getForForm(
	    'ugref',
	    null,
	    FALSE,
	    null,
	    $filters
	    );/**/
	}

	function getSuperAdminsGroup($allinfo=FALSE)
	{
		$acc='NjEyZGU3ZmNhMzk3Yg';
		return $allinfo ? $this->where('ugref',$acc)->first() : $acc;
		
	}

	/**
	 * Return Array with grups data to populate dropdown in form
	 * 
	 * @param  string $field	Value field name (saved to db)
	 * @param  string $value    Text field name (showed to end user)
	 * @param  bool   $addEmpty Determine if empty field will be added
	 * @param  string $defValue Default value field name if $value is null or not exists in allowed fields array
	 * @return Array
	 */
	function getForForm($field=null,$value=null,$addEmpty=FALSE,$defValue=null,array $filters=[])
	{
		$defValue=$defValue==null?'ugname':$defValue;
		$field=$field==null?$this->primaryKey:$field;
		$field=in_array($field, $this->allowedFields)?$field:$this->primaryKey;
		$value=$value==null?$defValue:$value;
		$value=in_array($value, $this->allowedFields)?$value:$defValue;
		$this->parseFilters($filters,$this,$this->allowedFields);
		$result=[];
		if ($addEmpty)
		{
			$result[]='';
		}
		
		foreach ($this->find() as $record) 
		{
			$result[$record[$field]]=$record[$value];
		}
		
		return $result;
	}
	
	function getAccessForForm()
	{
	  return $this->getForForm('ugref');
	}
	
	/**
	 * Install model table and instert data to storage (db)
	 * 
	 * @return bool
	 */
	public function installstorage()
	{
		if (!parent::installstorage())
		{
			return FALSE; 
		}
		return $this->insertBatch(
		[	
			['ugname'=>'Administrators','ugdesc'=>lang('cms.users.admin.user_group_desc'),'enabled'=>'1']
		]);
	}
	
	
}