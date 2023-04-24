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
 
namespace CELLA\Models\Menu;

use CELLA\Helpers\Strings as Str;

class MenuItemsModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Menu table name
	 * 
	 * @var string
	 */
	protected $table='menu_items';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'mid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['mgroup','mtext','mimage','mroute','morder','mkeywords','mtarget','enabled','access'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'mid'=>	['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE,'null'=>FALSE],
		'mgroup'=>		['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'mtext'=>		['type'=>'VARCHAR','constraint'=>'150','null'=>FALSE],
		'mimage'=>		['type'=>'TEXT','null'=>FALSE],
		'mroute'=>		['type'=>'TEXT','null'=>FALSE],
		'morder'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'mkeywords'=>	['type'=>'TEXT','null'=>FALSE],
		'mtarget'=>		['type'=>'VARCHAR','constraint'=>'10','null'=>FALSE],
		'access'=>		['type'=>'VARCHAR','constraint'=>'36','null'=>FALSE],
		'enabled'=>		['type'=>'INT','constraint'=>'11','null'=>FALSE],
	];
	
	/**
	 *  Return all records for given menu
	 *  
	 * @param  array $filter Array with filters (key is field, value is field value)
	 * @return array
	 */
	public function getItems($menu,$access)
	{
		$arr=model('Auth/UserModel')->getLogedUserMenuAccess();
		$filters=['mgroup'=>$menu,'enabled'=>1,'access'=>$access];
		if (is_array($arr))
		{
			$filters['mid In']=$arr;
		}
		return $this->filtered($filters)->orderby('morder')->find();
	}
	
	public function getItemGroups()
	{
		$arr=[];
		foreach ($this->select('mgroup')->groupBy('mgroup')->find() as $value) 
		{
			$arr[]=$value['mgroup'];
		}
		return $arr;
	}

	public function getForProfileForm()
	{
		$data=$this->filtered(['access'=>'@loged_user'])->find();
		$arr=[];
		foreach ($data as $value) 
		{
			if (!array_key_exists($value['mgroup'], $arr))
			{
				$arr[$value['mgroup']]=[];
			}
			$arr[$value['mgroup']][$value['mid']]=$value['mtext'];
		}
		return $arr;
	}
	
	/**
	 * Get route methods for all controllers
	 * 
	 * @param  bool $encrypt Optional if TRUE values will be flatten (json) and base64encode
	 * 
	 * @return array 
	 */
	function getControllersMethods($encrypt=FALSE)
	{
		$arr=[];
		foreach (directory_map(parsePath('@app/Controllers',TRUE)) as  $value) 
		{
			if (is_string($value) && Str::endsWith($value,'.php'))
			{
				$value=Str::before($value,'.');
				$arr[$value]=loadModule($value)->getAvaliableRoutes();
				if ($encrypt && is_array($arr[$value]))
				{
					$arr[$value]=base64_encode(json_encode($arr[$value]));
				}
			}
		}
		return $arr;
	}
}
?>