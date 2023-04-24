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
 
namespace CELLA\Models\Scheduler;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\MovementType;

class BoardModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='sch_boards';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'sbid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','view','view_file','weekdays','perdaycol','rowqty','cardheight','colcfg','usedate','access','enabled'];
	
	protected $validationRules =
	[
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'sbid'=>	 	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>		['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'view'=>		['type'=>'TEXT','null'=>TRUE],
		'view_file'=>	['type'=>'TEXT','null'=>TRUE],
		'weekdays'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'perdaycol'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE,'default'=>1],
		'rowqty'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'cardheight'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'colcfg'=>		['type'=>'TEXT','null'=>TRUE],
		'usedate'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE]		
	];
	
	function getBoardsViewNames()
	{
		$arr=[];
		
		foreach (directory_map(parsePath('@views/Scheduler/boards',TRUE)) as $value) 
		{
			if (Str::startsWith($value,'board_'))
			{
				$value=str_replace('.php', '', $value);
				$arr['Scheduler/boards/'.$value]=ucwords(substr($value,6));
			}
		}
		return $arr;
	}

	function getBoardsNamesForForm()
	{
		$arr=[];
		foreach ($this->filtered(['access'=>'@logeduser','enabled'=>'1'])->find() as $value) 
		{
			$arr[$value['sbid']]=$value['name'];
		}
		return $arr;
	}
	
	function getBoardsViewNames_old()
	{
		$arr=[];
		
		foreach ($this->filtered(['access'=>'@logeduser','enabled'=>'1'])->find() as $value) 
		{
			$arr[$value['sbid']]=$value['view'];
		}
		return $arr;
	}
	
	function getBoardsNamesForForm_old()
	{
		$arr=[];
		$param=model('Settings/SettingsModel')->get('scheduler.boardsnames',TRUE);
		foreach (is_array($param) ? $param : [$param] as $key => $value) 
		{
			if (Str::contains($value,':'))
			{
				$value=explode(':', $value);
				$value=$value[0];
			}
			$arr[$key]=$value;
		}
		return $arr;
	}
	
	
	function getDefault()
	{
		$arr=[];//array_combine($this->allowedFields, array_fill(0, count($this->allowedFields), ''));
		$arr['name']='';
		$arr['sbid']='new';
		$arr['weekdays']=7;
		$arr['weekdays']=7;
		$arr['perdaycol']=1;
		$arr['rowqty']=50;
		$arr['cardheight']=80;
		$arr['usedate']=1;
		$arr['enabled']=1;
		$arr['access']=explode(',',loged_user('accessgroups'));
		$arr['access']=count($arr['access'])>0 ? $arr['access'][0] : '';
		$arr['colcfg']=base64_decode('ewoiaGVhZGVycyI6CglbCgkKCV0sCiJjYXJkc3R5cGUiOgoJWwoJCgldLAoiYnV0dG9ucyI6CglbCiAgICAgICAgIm5ldyIKICAgICAgIAldLAoiaGVhZGVyX2hlaWdodCI6IjgwIgp9');
		$arr['view']='eyJsYWJlbCI6IldlZWsgJGRhdGVUbyIsImZvcm1hdCI6IlcifQ==';
		return $arr;
	}
	
}