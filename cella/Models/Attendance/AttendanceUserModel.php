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
 
namespace CELLA\Models\Attendance;

use \CELLA\Helpers\MovementType;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\Arrays as Arr;

class AttendanceUserModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='attedance_users';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'attid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['att_user','att_empid','att_name','att_start','att_finish','enabled'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'attid'=>       ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'att_user'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
		'att_empid'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'att_name'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'att_start'=>	['type'=>'VARCHAR','constraint'=>'4','null'=>FALSE],
                'att_finish'=>	['type'=>'VARCHAR','constraint'=>'4','null'=>FALSE],
                'enabled'=>	['type'=>'INT','constraint'=>'1','default'=>1,'null'=>FALSE],
	];
        
}


