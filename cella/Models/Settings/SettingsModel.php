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

use CELLA\Helpers\Strings as Str;
use CELLA\Helpers\Arrays as Arr;
use CELLA\Helpers\MovementType;

class SettingsModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Settings table name
	 * 
	 * @var string
	 */
	protected $table='settings';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'id';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['id','paramsgroups','param','value','fieldtype','tooltip'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'id'=>				['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'paramsgroups'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'param'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE,'unique'=>TRUE],
		'value'=>			['type'=>'TEXT','null'=>FALSE],
		'fieldtype'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>TRUE],
		'tooltip'=>			['type'=>'TEXT','null'=>TRUE],
		
	];
	
	/**
	 *  Return all records from Settings table categorized by paramsgroup
	 *  
	 * @param  array $filter
	 * @return array
	 */
	public function getCategorized(array $filters=[])
	{
		return $this->count(['paramsgroups'=>'modules']);
		$data=[];
		foreach($this->arrWhere($filters)->findAll() as $item)
		{
			$data[$item['paramsgroups']][$item['param']]=['value'=>$item['value'],'param'=>$item['param'],'id'=>$item['id']];	
		}
		
		return $data;
	}
	
	/**
	 *  Return all records from Settings table categorized by paramsgroup
	 *  
	 * @param  int   $limit  Determine how many records will be shown
	 * @param  array $filter Array with filters (key is field, value is field value)
	 * @return array
	 */
	public function getAll($limit=null,array $filters=[])
	{
		return parrent::getAll($limit,$filters,'paramsgroups');
	}
	
	/**
	 * Return setting param value
	 * 
	 * @param String $key
	 * @param bool   $parseValue
	 * @param String $keyToGet
	 * 
	 * @return mixed
	 */
	public function get($key,$parseValue=FALSE,$keyToGet='value',$showError=TRUE)
	{
		$showError=ENVIRONMENT=='development' ? $showError : false;
		if (Str::contains($key,'.'))
		{
			$key=explode('.', $key);
			if (count($key)!=2)
			{
				throw new \Exception('Invalid param name');
			}
			if (Str::contains($key[0],'*'))
			{
				$this->Like('paramsgroups',str_replace('*', '%', $key[0]));
			}else
			{
				$this->where('paramsgroups',$key[0]);
			}
			$key=$key[1];
		}
		
		if (Str::contains($key,'*'))
		{
			$this->Like('param',str_replace('*', '%', $key));
		}else
		{
			$this->where('param',$key);
		}
		
		$result=[];
		$data=[];
		$data=$this->find();
		
		if (count($data)==0)
		{
			if ($showError)
			{
				throw new \Exception('Invalid param name');
			}
			return null;
		}
		$multi=FALSE;
		if ($keyToGet=='values')
		{
			$multi=TRUE;
			$keyToGet='value';
		}
		
		foreach ($data as $value) 
		{
			if (is_array($value)&&array_key_exists($keyToGet, $value))
			{
				$result[$value['param']]=$value[$keyToGet];
			}else
			{
				$result[$value['param']]=$value;
			}
		}
		if (count($result)==1)
		{
			if ($keyToGet=='value' && !$multi)
			{
				$result=array_values($result)[0];
			}
		}
		if ($parseValue && Str::isJson($result))
		{
			$result=json_decode($result,TRUE);
		}else
		if ($parseValue && Str::contains($result,'|'))
		{
			$arr=[];
			foreach (explode('|',$result) as $value) 
			{
				if (Str::contains($value,'='))
				{
					$value=explode('=', $value);
					$arr[$value[0]]=$value[1];
				}else
				{
					$arr[]=$value;
				}
			}
			$result=$arr;
		}
		
		return $result;
	}
	
	
	public function write($key,$value,$valueParser='JSON')
	{
		return $this->builder()->set('value',$this->parseValue($value))->where('param',$key)->update();
	}

	public function add($groupname,$param,$value)
	{
		$record=['paramsgroups'=>$groupname,'param'=>$param,'value'=>$this->parseValue($value)];
		$id=$this->where('paramsgroups',$groupname)->where('param',$param)->first();
		if ($id!=null)
		{
			$record[$this->primaryKey]=$id[$this->primaryKey];
		}	
		return $this->save($record);
	}
	
	
	public function writeMany($data)
	{
		
		$result=FALSE;
		foreach ($data as $key => $value) 
		{
			if (is_array($value))
			{
				$builder=$this->builder();
				if (array_key_exists('value', $value))
				{
					$builder=$builder->set('value',$value['value']);
				}
				if (array_key_exists('fieldtype', $value))
				{
					$builder=$builder->set('fieldtype',$value['fieldtype']);
				}
				if (array_key_exists('tooltip', $value))
				{
					$builder=$builder->set('tooltip',$value['tooltip']);
				}
				$result=$builder->where('param',$key)->update();
				
			}else
			{
				$result=$this->write($key,$value);
			}
			
		}
		return $result;
	}
	
	
	private function parseValue($value)
	{
		if (is_array($value) || is_object($value))
		{
			if ($valueParser=='FLAT')
			{
				$value=Str::Flatten($value);
			}else
			{
				$value=json_decode($value);	
			}	
		}
		return $value;	
	}
	
	/**
	 * Retturns field access value
	 * 
	 * @param  string $field
	 * 
	 * @return mixed
	 */
	function getFieldAccess($field)
	{
		return $this->get('fieldsaccess.'.$field,FALSE,'value',FALSE);
	}
	
	/**
	 * Return array with avaliable time zones
	 * 
	 * @return Array
	 */
	public function getTimeZonesForForm()
	{
		$times=\DateTimeZone::listIdentifiers(\DateTimeZone::ALL, null);
	   	return array_combine(array_values($times),array_values($times));
	}
	
	/**
	 * Return Array with avalibale languages in system
	 * 
	 * @return Array
	 */
	public function getAvalLocales()
	{
		$arr=[];
		foreach (config('APP')->supportedLocales as $value) 
		{
			$dir=APPPATH.'Language/'.$value;
			if (file_exists($dir))
			{
				$cms=Arr::FetchArrayFromFile($dir.'/system.php',null);
				if (is_array($cms) && array_key_exists('vcms_lang_id', $cms))
				{
					$arr[$value]=$cms['vcms_lang_id'];
				}
			}
		}
		return $arr;
	}
	
	
	/**
	 * Add custom route to system settings
	 * 
	 * @param  mixed   $controller Controller object
	 * @param  string  $action     Action which will be fired if route match (controller and action ie /settings/add)
	 * @param  string  $route      Controller section of route to be match (first segment after webiste url)
	 */
	public function addCustomRoute($controller,string $action,string $route)
	{
		
		if (is_subclass_of($controller,'\VCMS\Controllers\Core\VCMSController'))
		{
			$controller=get_class($controller);
		}
		
		if (is_string($controller) && (Str::contains($controller,'/') || Str::contains($controller,'\\'))  && Str::endsWith(strtolower($controller),'controller'))
		{
			$this->add('routes',$route,json_encode(['controller'=>$controller,'action'=>$action]));
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * Returns array with pallet status codes
	 * 
	 * @param  int $includeFrom Optional Key from which codes will be included
	 * @param  int $includeTo   Optional Key to which codes will be included
	 * 
	 * @return Array
	 */
	public function getPalletStatusForForm($includeFrom=-99,$includeTo=100)
	{
		$arr=[];
		foreach ($this->get('pallets.pallet_types',TRUE) as $key => $value) 
		{
			if ($key>$includeFrom && $key<$includeTo)
			{
				$arr[$key]=$value;
			}
		}
		return $arr;
	}
	
	function getTheme($name=null)
	{
		$name=$name==null?$this->get('system.theme') : $name;
		$path=parsePath('@template/'.$name.'/config.php',TRUE);
		if (file_exists($path))
		{
			return include($path);
		}
		return null;
	}
	
	public function getCustomFieldsTargets()
	{
		$targets=$this->get('general.customfields_targets',TRUE);
		$targets=is_array($targets) ? $targets :[];
		foreach ($targets as $key => $value) 
		{
			$targets[$key]=lang($value);
		}
		return $targets;
	}

	/**
	 * Returns array with avaliable movement types
	 * 
	 * @return Array
	 */
	function getMovementTypes($returnField='value')
	{
		$arr=[];
		$data=$this->get('movement_types.*',FALSE,$returnField,FALSE);
		foreach ($data as $key=> $value) 
		{
			if (Str::contains($key,'_'))
			{
				$arr[Str::afterLast($key,'_')]=$value;
			}
			
		}
		return $arr;
	}
	
	/**
	 * Returns list with log files
	 * 
	 * @return Array
	 */
	function getLogsList()
	{
		$arr=[];
		foreach (get_filenames(parsePath('@writable/logs',TRUE),TRUE) as $file) 
		{
			if (Str::endsWith(strtolower($file),'log'))
			{
				$file=new \CodeIgniter\Files\File($file);
				$arr[]=['id'=>base64url_encode($file->getBasename()),'path'=>$file->getRealPath(),'name'=>$file->getBasename(),'modified'=>$file->getCTime(),'file'=>$file];
			}
			
		}
		return $arr;
	}
	
	/**
	 * Remove Log(s) file(s)
	 * 
	 * @param  Array
	 * 
	 * @return bool
	 */
	function removeLogs($data=null)
	{
		if ($data=null)
		{
			return delete_files(parsePath('@writable/logs/',TRUE));
		}
		if (is_array($data))
		{
			foreach ($data as $value) 
			{
				$file=parsePath('@writable/logs/'.$value,TRUE);
				if (!file_exists($file))
				{
					$file=parsePath('@writable/logs/'.base64url_decode($value),TRUE);
				}
				unlink($file);
			}
			return TRUE;
		}
		$data=parsePath('@writable/logs/'.$data,TRUE);
		if (file_exists($data))
		{
			unlink($data);
		}
	}
		
	public function installstorage($install=FALSE)
	{
		if ($install)
		{
			parent::installstorage();
		}
	}
	
	/**
	 * Returns custom settings tabs data
	 * 
	 * @return array
	 */
	public function getCustomSettingsTab()
	{
		$set=$this->get('system.settings_moretabs',TRUE);
		$arr=[];
		if (is_array($set))
		{
			foreach ($set as $key => $value) 
			{
				if ($key!=null && strlen($key) > 0 && Str::contains($value,':'))
				{
					$value=explode(':', $value);
					$arr[lang($key)]=loadModule($value[0],$value[1]);
				}
			}
		}
		return $arr;
	}
	
	
}
?>