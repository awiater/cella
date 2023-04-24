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

use CELLA\Helpers\Strings as Str;
use CELLA\Helpers\Arrays as Arr;

class ReportModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='reports';
	
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
	protected $allowedFields=['rname','rdesc','rtype','rtables','rcolumns','rfilters','rconfig','rsql','access','enabled'];
	
	protected $validationRules =
	[
		'rname'=>'required|is_unique[reports.rname,rid,{rid}]',
	];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'rid'=>	 		['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'rname'=>	 	['type'=>'VARCHAR','constraint'=>'120','null'=>FALSE],
		'rdesc'=>	 	['type'=>'TEXT','null'=>TRUE],
		'rtype'=>	 	['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'rtables'=> 	['type'=>'TEXT','null'=>FALSE],
		'rcolumns'=> 	['type'=>'TEXT','null'=>TRUE],
		'rfilters'=> 	['type'=>'TEXT','null'=>TRUE],
		'rconfig'=> 	['type'=>'TEXT','null'=>TRUE],
		'rsql'=> 		['type'=>'TEXT','null'=>TRUE],
		'access'=> 		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=> 	['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];	
	
	function getTablesForForm()
	{
		$arr=$this->db()->listTables();
		return array_combine(array_values($arr), array_values($arr));
	}
	
	function getDataForReport($sql,$filtersPost=[],$legacy=FALSE)
	{	
		if ($legacy)
		{
			$sql=str_replace(Arr::ParsePatern(array_keys($filtersPost),'@value'), array_values($filtersPost), $sql);
			return $this->db()->query($sql)->getResultArray();
		}
		
		if (array_key_exists('data_fetch', $sql) && array_key_exists('sql', $sql['data_fetch']))
		{
			$sql=$sql['data_fetch']['sql'];
			$sql=str_replace(array_keys($filtersPost), array_values($filtersPost), $sql);
			return $this->db()->query($sql)->getResultArray();
		}
		
		if (array_key_exists('data_fetch', $sql) && Arr::KeysExists(['controller','action'], $sql['data_fetch']))
		{
			if (array_key_exists('args', $sql['data_fetch']))
			{
				
				$sql['data_fetch']['args']=str_replace(array_keys($filtersPost), array_values($filtersPost), $sql['data_fetch']['args']);
				if (Str::startsWith($sql['data_fetch']['args'],'@'))
				{
					$sql['data_fetch']['args']=substr($sql['data_fetch']['args'], 1);
					if (array_key_exists($sql['data_fetch']['args'], $sql))
					{
						$sql['data_fetch']['args']=$sql[$sql['data_fetch']['args']];
					}else
					{
						$sql['data_fetch']['args']=[];
					}
				}else
				{
					$sql['data_fetch']['args']=explode(',',$sql['data_fetch']['args']);
				}
				
				foreach ($sql['data_fetch']['args'] as $key => $value) 
				{
					if (array_key_exists(substr($value, 1), $filtersPost))
					{
						$sql['data_fetch']['args'][$key]=$filtersPost[substr($value, 1)];
					}
					
					if (array_key_exists(substr($value, 1), $sql))
					{
						$sql['data_fetch']['args'][$key]=$sql[substr($value, 1)];
					}
					
					if (Str::isJSON($value))
					{
						$sql['data_fetch']['args'][$key]=json_decode($value,TRUE);
					}
				}
			}else
			{
				$sql['data_fetch']['args']=[];
			}
			
			$sql=loadModule($sql['data_fetch']['controller'],$sql['data_fetch']['action'],$sql['data_fetch']['args']);
			return is_array($sql) ? array_values($sql) : [];
		}	
		return [];	
		
		if (Str::startsWith($sql,'#'))
		{
			if (!Str::contains($sql,'::'))
			{
				return [];
			}
			$sql=explode('::',$sql);
			$sql[0]=substr($sql[0],1);
			if (Str::contains($sql[1],'@'))
			{
				$sql[2]=explode('@',$sql[1]);
				$sql[1]=$sql[2][0];
				$sql[2]=explode(',',$sql[2][1]);
				foreach ($sql[2] as $key=>$value) 
				{
					if (Str::contains($value,'|'))
					{
						$sql[2][$key]=Arr::fromFlatten($value);
					}
				}
			}
			$sql=loadModule($sql[0],$sql[1],count($sql)>2 ? $sql[2] : null);
			return is_array($sql) ? array_values($sql) : [];
		}
		return $this->db()->query($sql)->getResultArray();
	}
	
	function runReport1($record,$filtersPost=[])
	{
		if (!is_array($record) || (is_array($record) && !array_key_exists('rsql', $record)))
		{
			return FALSE;
		}
		$record['rcolumns']=str_replace([','.PHP_EOL,', ,'], ',', $record['rcolumns']);
		$filters=[];
		$fields=[];
		foreach (explode(PHP_EOL,$record['rfilters']) as $value) 
		{
			if (!Str::contains($value,'|'))
			{
				continue;
			}
			$value=explode('|', $value);
			
			if (array_key_exists($value[0], $filtersPost))
			{
				$value[1]=$filtersPost[$value[0]]['value'];
			}

			if (Str::contains($value[1],','))
			{
				$value[1]=explode(',', $value[1]);
			} 
			if ($value[1]!=null || (is_string($value[1]) && strlen($value[1]) > 0) )
			{
				$filters[$value[0]]=$value[1];
			}
			
			if (count($value) > 2)
			{
				$fields[]=['name'=>$value[0],'cfid'=>$value[0],'type'=>$value[2],'label'=>count($value) > 3 ? $value[3] : $value[0]];
			}
		}
		//dump($filters);exit;
		$model=model('BaseModel');
		$model->table=$record['rtables'];
		if (count($filters) > 0)
		{
			$model=$model->parseFilters($filters,$model,[],FALSE);
		}
		
		if (Str::contains($record['rcolumns'],'groupBy'))
		{
			$group=Str::after($record['rcolumns'],'groupBy');
			$record['rcolumns']=Str::before($record['rcolumns'],'groupBy');
			$group=explode(' ', $group);
			$group=$group[1];
			$group=explode(',', $group);
			$model->groupBy($group);
		}
		
		$model->select($record['rcolumns']);
		//dump($filtersPost);exit;
		return ['data'=>$model,'fields'=>$fields];
	}
}