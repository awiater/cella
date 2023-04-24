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
 
namespace CELLA\Models;

use CodeIgniter\Model;
use CELLA\Helpers\Strings as Str;

class BaseModel extends Model
{
	
	protected $useAutoIncrement = true;
	
	
	protected $returnType     = 'array';
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=[];
	
	
	/**
	 *  Return all records from table
	 *  
	 * @param  array   $filters  		Array with filters (key is field, value is field value)
	 * @param  string  $orderby  		Order by field name
	 * @param  string  $paginate 		Pagination settings
	 * @param  integer $logeduseraccess Loged user access level
	 * @return array
	 */
	public function filtered(array $filters=[],$orderby=null,$paginate=null,$logeduseraccess=null,$Validation=TRUE)
	{
	    $result=$this->parseFilters($filters,$this,[],$Validation);
		
		if ($orderby!=null)
		{
			if (is_string($orderby)&&Str::startsWith($orderby,'groupby:'))
			{
				$orderby=substr($orderby,strlen('groupby:'));
				$result=$result->groupBy($orderby);
			}else
			{
				$orderby=is_array($orderby)?$orderby:[$orderby];
				foreach ($orderby as $orderbyValue) 
				{
					$result=$result->orderBy($orderbyValue);
				}
			}		
		}
		if ($paginate!=null&&$paginate!=FALSE)
		{
			if ($paginate==0)
			{
				return $result->find();
			}
			$result= $result->paginate($paginate);
		}
		
		
		return $result;
	}	
	
	protected function parseFilters(array $filters,$model,$allowedfields=[],$Validation=TRUE)
	{
		
		$allowedfields=count($allowedfields)<1?$model->allowedFields:$allowedfields;
		if (!in_array($this->primaryKey, $allowedfields))
		{
			$allowedfields[]=$this->primaryKey;
		}
		foreach($filters as $key=>$value)
		{
			$prefix=null;
			if (Str::startsWith($key,'( '))
			{
				$key=str_replace('( ', '', $key);
				$model=$model->groupStart();
			}
			if (Str::startsWith($key,'||( '))
			{
				$key=str_replace('||( ', '', $key);
				$model=$model->orGroupStart();
			}
			$groupend=FALSE;
			if (Str::endsWith($key,' )'))
			{
				$key=str_replace(' )', '', $key);
				$groupend=TRUE;
			}
			
			
			if (Str::contains($key,'.'))
			{
				$prefix=explode('.', $key);
				$key=$prefix[1];
				$prefix=$prefix[0].'.';
			}
			$option='';
			if ($key=='access' && in_array('access',$allowedfields))
			{
				$accessgroups=loged_user('accessgroups');
				$accessgroups=is_array($accessgroups) ? null :$accessgroups;
			  	$value=str_replace(['@loged_user','@logeduser'],$accessgroups,$value);
			  	$model=$model->Where("FIND_IN_SET(".$prefix.$key.",'".$value."')>0",null,FALSE);
				goto endforloop;
			}else
			if (Str::startsWith($key,'|| '))
			{
				$option='or';
				$key=str_replace('|| ', '', $key);
			}
			
			if (Str::endsWith($key,' %'))
			{
				$option.='Like';
				$key=str_replace(' %', '', $key);
			}else
			if (Str::endsWith($key,' In')&&is_array($value))
			{
				$option.='whereIn';
				$key=str_replace(' In', '', $key);
			}else
			if (Str::endsWith($key,' notIn')&&is_array($value))
			{
				$option.='whereNotIn';
				$key=str_replace(' notIn', '', $key);
			}else	
			{
				$option.='Where';
			}
			
			$keyA=explode(' ', $key);
			
			if (Str::contains($keyA[0],'.'))
			{
				$keyA[0]=explode('.', $keyA[0]);
				$keyA[0]=$keyA[0][1];
			}
						
			if ($Validation && in_array($keyA[0],$allowedfields))
			{
				$model=$model->{$option}($prefix.$key,$value);
			}else
			if (!$Validation)
			{
				$model=$model->{$option}($prefix.$key,$value);
			}
			
			if ($groupend)
			{
				$model=$model->groupEnd();
			}
		endforloop:
		}
		return $model;
	}
	
	/**
	 * Count records in table. Could be restricted by filters
	 * 
	 * @param  array $filters Array with filters (key is field, value is field value)
	 * @return int
	 */
	public function count(array $filters=[])
	{
		$query=$this->parseFilters($filters,$this->builder(),$this->allowedFields);
		//log_message('error','COUNT_SQL: '.$query->getCompiledSelect());
		return $query->countAllResults();
	}
	
	/**
	 * Get next record primary key value
	 * 
	 * @return Int
	 */
	function getNextID()
	{
		$arr= $this->db->query(str_replace(['#','%table%'], ['"',$this->table], "SELECT AUTO_INCREMENT FROM information_schema.tables where TABLE_NAME='%table%'"))->getResult();
		if (count($arr)>0)
		{
			return $arr[0]->AUTO_INCREMENT;
		}
		return null;
	}
	
	/**
	 * Get last record primary key value
	 * 
	 * @return Int
	 */
	function getLastID()
	{
		$res=$this->select($this->primaryKey)->orderby($this->primaryKey.' DESC')->limit(1)->find();
		if (is_array($res) && count($res) > 0)
		{
			return $res[count($res)-1][$this->primaryKey];
		}
		return null;
	}
	
	/**
	 * Enable item by give item id
	 * 
	 * @param  int  $id      Item id
	 * @param  bool $enable  Determine if item is enabled (TRUE,1) or disabled (FALSE,0)
	 * @return bool
	 */
	public function enableItem($id,$enable)
	{
		if (is_bool($enable))
		{
			$enable=$enable?1:0;
		}
		return $this->save([$this->primaryKey=>$id,'enabled'=>$enable]);
	}
	
	public function updateData($data)
	{
		if (array_key_exists($this->primaryKey, $data))
		{
			return parent::update($data[$this->primaryKey],$data);
		}else
		{
			return parent::insert($data);
		}
	}
	
	public function updateMany(array $data,$whKey=null)
	{
		$whKey=$whKey==null ? $this->primaryKey :  $whKey;
		$sql=$this->builder()->set('%key%','%value%')->where('%field%','%fval%')->getCompiledUpdate();
		$arr=[];
		$this->db->transStart();
		foreach ($data as  $row) 
		{
			if (array_key_exists($whKey, $row))
			{
				foreach ($row as $key => $value) 
				{
					if ($key!=$whKey)
					{
						$this->db->query(str_replace(['%key%','%value%','%field%','%fval%'], [$key,$value,$whKey,$row[$whKey]], $sql));
						$arr[]=str_replace(['%key%','%value%','%field%','%fval%'], [$key,$value,$whKey,$row[$whKey]], $sql);
					}
				}
				
			}
		}
		$this->db->transComplete();
		return $arr;
	}
	
	/**
	 * Return Array with data to populate dropdown in form
	 * 
	 * @param  string $field	Value field name (saved to db)
	 * @param  string $value    Text field name (showed to end user)
	 * @param  bool   $addEmpty Determine if empty field will be added
	 * @param  string $defValue Default value field name if $value is null or not exists in allowed fields array
	 * @return Array
	 */
	function getForForm($field=null,$value=null,$addEmpty=FALSE,$defValue=null,array $filters=[])
	{
		$defValue=$defValue==null?$this->allowedFields[0]:$defValue;
		$field=$field==null?$this->primaryKey:$field;
		$field=in_array($field, $this->allowedFields)?$field:$this->primaryKey;
		$value=$value==null?$defValue:$value;
		$value=in_array($value, $this->allowedFields)?$value:($value=='*' ? $value :$defValue);
		
		$result=[];
		if ($addEmpty!=FALSE)
		{
			$result['']= is_bool($addEmpty) ? '' : $addEmpty;
		}
		
		$sql=$this;
		if (in_array('enabled', $this->allowedFields) && !array_key_exists('enabled', $filters))
		{
			$filters['enabled']=1;
		}
		
		if (in_array('access', $this->allowedFields))
		{
			$filters['access']=service('auth')->getLogedUserInfo('accessgroups');
		}
		
		$sql=$sql->filtered($filters,$field,FALSE);
		foreach ($sql->find() as $record) 
		{
                    if ($value=='*')
                    {
                        $result[$record[$field]]=$record;
                    }else
                    if (array_key_exists($value, $record) && array_key_exists($field, $record))
                    {
                        $result[$record[$field]]=$record[$value];
                    }
			
		}
		return $result;
	}

	/**
	 * Install model table in db
	 * 
	 * @return bool
	 */
	public function installstorage()
	{
		$this->initForge();
		if (is_array($this->fieldsTypes)&&count($this->fieldsTypes))
		{
			$this->db->disableForeignKeyChecks();
			$this->forge->dropTable($this->table, true);
			$this->forge->addField($this->fieldsTypes);
			foreach ($this->fieldsTypes as $key => $value) 
			{
				if (is_array($value)&&array_key_exists('foreignkey', $value)&&count($value['foreignkey'])>1)
				{
					$value=$value['foreignkey'];
					$this->forge->addForeignKey($key,$value[0],$value[1],count($value)>2?$value[2]:'',count($value)>3?$value[3]:'');
				}	
			}
			$this->forge->addKey($this->primaryKey,TRUE);
			$result= $this->forge->createTable($this->table, TRUE);
			$this->db->enableForeignKeyChecks();
			return $result;
		}
		return FALSE;
	}
	
	/**
	 * Uninstall (remove) model table from db
	 * 
	 * @return bool
	 */
	public function removestorage()
	{
		$this->initForge();
		return $this->forge->dropTable($this->table, false, true);
	}
	
	/**
	 * Init forge if not set before
	 */
	function initForge()
	{
		if ($this->forge==null)
		{
			$this->forge=\Config\Database::forge();
		}
	}
	
	/**
         * Returns model for given view
         * 
         * @param string $name
         * 
         * @return \AGORA\Models\BaseModel
         */
        function getView($name)
        {
            $model=new BaseModel();
            $model->table=$name;
            $model->allowedFields=$model->db()->getFieldNames($name);
            return $model;
        }
        
          /**
         * Returns model
         * 
         * @param type $name
         * 
         * @return type
         */
        function getModel($name)
        {
            if (strtolower($name)=='settings')
            {
                return model('Settings/SettingsModel');
            }
            $name=ucwords($name);
            if (!Str::endsWith($name,'Model'))
            {
                $name.='Model';
            }
            if (Str::contains($name, '/'))
            {
                return model($name);
            }
            $namespace=new \ReflectionClass(get_class($this));
            $namespace=$namespace->getNamespaceName();
            $namespace=Str::afterLast($namespace, '\\');
            return model($namespace.'/'.$name);
        }
	
}
?>