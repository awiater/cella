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
 
namespace CELLA\Models\Tasks;

use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;


class RuleModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='rules';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'rid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['name','rdesc','trigger','action','rorder','access','enabled'];
	
	protected $validationRules =
	 [
	 	'name'=>'required|is_unique[rules.name,rid,{rid}]',
	 ];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'rid'=>		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'name'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'rdesc'=>	['type'=>'TEXT','null'=>TRUE],
		'trigger'=>	['type'=>'TEXT','null'=>FALSE],
		'action'=>	['type'=>'TEXT','null'=>FALSE],
		'rorder'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'access'=>	['type'=>'VARCHAR','constraint'=>'80','null'=>FALSE],
		'enabled'=>	['type'=>'INT','constraint'=>'11','null'=>FALSE],	
	];
	
	function actionRuleByTrigger($trigger,$params)
	{
		$trigger=$this->filtered(['access'=>'@loged_users','enabled'=>1,'trigger'=>$trigger])->orderby('rorder')->find();
		return $this->actionRule($trigger,$params);
	}

	function actionRuleByName($name,$params)
	{
		$trigger=$this->filtered(['access'=>'@loged_users','enabled'=>1,'name'=>$name])->orderby('rorder')->find();
		return $this->actionRule($trigger,$params);
	}
	
	function actionRule(array $rule,$params)
	{
		foreach ($rule as $value) 
		{
			if (is_array($value) && Arr::KeysExists($this->allowedFields,$value))
			{
				
				$args=[];
				if (Str::startsWith($value['action'],'@'))
				{
					$value['action']=substr($value['action'], 1);
				}
				
				if (Str::contains($value['action'],'@') && count($params) > 0 && is_array($params[0]))
				{
					$act_params=explode('@',$value['action']);
					$value['action']=$act_params[0];
					$act_params=$act_params[1];
					
					foreach (explode('/',$act_params) as $key => $arg) 
					{
						if (array_key_exists($arg, $params[0]))
						{
							$args[$arg]=$params[0][$arg];
						}else
						if (Str::startsWith($arg,'"') && Str::startsWith($arg,'"'))
						{
							$args[]=str_replace('"', '', $arg);
						}else
						{
							return FALSE;
						}
					}
				}else
				{
					$args=$params;
				}
				if (!loadModule($value['action'],null,$args))
				{
					log_message('error','Rule action cannot be triggered: '.$value['action']);	
					return FALSE;
				}
			}else
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
}