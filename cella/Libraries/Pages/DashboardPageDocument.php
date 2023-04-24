<?php
/*

 *  This file is part of VCMS  
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2020 All Rights Reserved				
 *
 *  @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 
namespace VCMS\Controllers\Core\Pages;

use VCMS\Helpers\Strings as Str;

class DashboardPageDocument extends AdminPageDocument
{
	public function __construct($session,$viewRenderController)
	{
		parent::__construct($session,$viewRenderController);
		$this->setView('Core/dashboard');
		$this->addData('_tiles',[]);
		$this->addTitle(null);
	}
	
	/**
	 * Add title to view data
	 * 
	 * @param  string $title
	 * @return VCMS\Controllers\Core\Pages\DashboardPageDocument
	 */
	function addTitle($title)
	{
		if ($title==null)
		{
			$title=Str::afterLast(get_class($this->viewRenderController), '\\');
			$title=str_replace(['Controller','Admin'], '', $title);
			$title.=' '.lang('cms.general.dashboard');
		}
		
		$this->addData('title',$title);
		return $this;
	}
	
	/**
	 * Add tile data to view
	 * 
	 * @param  string  $label
	 * @param  string  $tooltip
	 * @param  string  $url
	 * @param  string  $link_label
	 * @param  string  $image
	 * @param  string  $name
	 * @return VCMS\Controllers\Core\Pages\DashboardPageDocument
	 */
	function addTile($label,$tooltip,$url,$link_label=null,$image=null,$name=null)
	{
		$arr=['label'=>lang($label),'tooltip'=>lang($tooltip),'url'=>$url,'image'=>$image,'link_label'=>$link_label];
		if ($name==null)
		{
			$this->viewData['_tiles'][]=$arr;
		}else
		{
			$this->viewData['_tiles'][$name]=$arr;
		}
		return $this;
	}
	
	/**
	 * Create tiles from admin menu items
	 * 
	 * @param  array $filters
	 * @return VCMS\Controllers\Core\Pages\DashboardPageDocument 
	 */
	function createTilesFromMenuItems($filters,$name=null)
	{
		foreach (model('Menu/MenuModel')->filtered($filters)->find() as $key=>$value) 
		{
			$value['text']=str_replace(['@lng{','}'], '', $value['text']);
			$route=json_decode($value['route'],TRUE);
			if ($name!=null)
			{
				$key.='_'.$key;
			}else
			{
				$key=$name;
			}
			$this->addTile($value['text'],$value['text'].'_tooltip',is_array($route)?url_from_array($route):null,null,$value['image'],$key);
		}
		return $this;
	}
}
