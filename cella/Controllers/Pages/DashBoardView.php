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

  
namespace CELLA\Controllers\Pages;

use CELLA\Helpers\Strings as Str;
use CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\AccessLevel;

class DashBoardView extends View
{

	private $_colors=[];
	
	public function __construct($controller,$iscached)
	{
		parent::__construct($controller,$iscached);
		$this->addData('_tiles',[]);
		$this->addScript('chart.js','@vendor/chartjs/Chart.min.js');
		$this->addScript('chart.plugin.js','@vendor/chartjs/Chart.plugin.labels.min.js');
		$this->_colors=json_decode(file_get_contents(parsePath('@vendor/chartjs/colorcodes.json',TRUE)),TRUE);
		$this->addData('_dashboardview_colors',[['hex'=>'#f56954'], ['hex'=>'#00a65a'], ['hex'=>'#f39c12'], ['hex'=>'#00c0ef'], ['hex'=>'#3c8dbc'], ['hex'=>'#d2d6de']]);					
	}
	
	/**
	 * Add big box tile to view
	 * 
	 * @param  string $header
	 * @param  string $text
	 * @param  mixed  $url
	 * @param  string $background
	 * @param  string $name
	 * 
	 * @return \CELLA\Helpers\Pages\DashBoardView
	 * 
	 */
	function addTile($header,$text,$url,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$arr=$this->getViewData('_tiles');
		$name=$name==null ? 'tile_'.count($arr) : $name;
		if (is_array($url))
		{
			$url=url_from_array($url);
		}
		
		if (!Str::startsWith($url,site_url()))
		{
			$url=url($url);
		}
		$arr[$name]=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'url'=>$url,
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'type'=>0
			
		];
		return $this->addData('_tiles',$arr);
	}
	
	/**
	 * Add small box tile to view (no url)
	 * 
	 * @param  string $header
	 * @param  string $text
	 * @param  string $background
	 * @param  string $name
	 * 
	 * @return \CELLA\Helpers\Pages\DashBoardView
	 * 
	 */
	function addBadge($header,$text,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$arr=$this->getViewData('_tiles');
		$name=$name==null ? 'badge_'.count($arr) : $name;
		$arr[$name]=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'type'=>1
			
		];
		return $this->addData('_tiles',$arr);
	}
	
	function addList($header,$text,array $options,$background='info',$icon='fas fa-tachometer-alt',$name=null)
	{
		$arr=$this->getViewData('_tiles');
		$name=$name==null ? 'list_'.count($arr) : $name;
		$arr[$name]=
		[
			'header'=>lang($header),
			'text'=>lang($text),
			'background'=>$background,
			'icon'=>$icon,
			'name'=>$name,
			'options'=>$options,
			'type'=>''
			
		];
		return $this->addData('_tiles',$arr);
	}
	
	/**
	 * 
	 */
	function addFromDB($dashboard)
	{
		$arr=
		[
			'Pallets_Info'=>'Pallet/PalletModel::getQtyOfPalletsWithStatus@1,25,50,75',
			'Suppliers_Jobs_Info'=>'Warehouse/OrdersModel::getOutsandingReceiptsForDash',
			'Pallets_in_Warehouse'=>'Pallet/PalletModel::count@enabled:1|status >:-1',
			'Pallets_in_Location'=>'Pallet/PalletModel::count@enabled:1|status >:-1'
		];
		//dump(json_encode($arr));exit;
		$arr=$this->getViewData('_tiles');
		$filters=['board'=>$dashboard];
		$access=model('Auth/UserModel')->getLogedUserDashAccess();
		$commands=model('Settings/SettingsModel')->get('dashboard.commands',TRUE,'value',FALSE);
		
		$commands=is_array($commands) ? $commands : [];
		if (is_array($access))
		{
			$filters['did In']=$access;
		}
		foreach (model('Settings/DashboardModel')->filtered($filters)->orderby('dorder')->find() as $record) 
		{
			$record['url']=null;
			$record['rsql']=$record['sql'];
			$record['sql']=str_replace(Arr::ParsePatern(array_keys($commands),'#value'), array_values($commands), $record['sql']);
			
			if (Str::contains($record['sql'],'::'))
			{
				$record['sql']=explode('#', $record['sql']);
				
				if (count($record['sql']) > 1 && $record['sql'][1]!=null && strlen($record['sql'][1])>0)
				{
					$record['url']=url($record['sql'][1]);
				}
				$record['sql']=explode('@',$record['sql'][0]);
				$record['sql'][0]=explode('::', $record['sql'][0]);
				$params=[];
				if (count($record['sql']) > 1)
				{
					$params=explode('/', $record['sql'][1]);
					if (is_array($params) && count($params) > 0)
					{
						foreach ($params as $key => $value) 
						{
							if (Str::contains($value,'|'))
							{
								$params[$key]=Arr::fromFlatten($value,':');
							}
						}
						
					}
					
				}
				
				if ($record['sql'][0][1]=='count')
				{
					$a=TRUE;
				}
				
				$record['sql']=loadModule($record['sql'][0][0],$record['sql'][0][1],$params);
				
				
			}else
			if (Str::startsWith($record['sql'],'/'))
			{
				$record['url']=url($record['sql']);
				$record['sql']='';
			}else
			if ($record['type']==5)	
			{
				$record['url']=$record['sql'];
			}
			
			$name=$record['name']==null ? 'list_'.count($arr) : $record['name'];
			$arr[$name]=
			[
				'text'=>lang($record['text']!=null ? $record['text'] : ''),
				'header'=>!is_array($record['sql']) ? lang($record['sql']!=null ? $record['sql'] : '') : '',
				'background'=>$record['back'],
				'icon'=>$record['icon'],
				'name'=>$name,
				'options'=>is_array($record['sql']) ? $record['sql'] : [],
				'type'=>$record['type'],
				'url'=>$record['url'],
				'id'=>$record['did'],
				'sql'=>$record['rsql'],
				'dorder'=>$record['dorder']
			];
		}
		
		return $this->addData('_tiles',$arr)->addData('_tiles_commands',$commands);
	}
	
	/**
	 * Add tiles delete button
	 * 
	 * @param  string $model Optional model name
	 * 
	 * @return \CELLA\Helpers\Pages\DashBoardView
	 */
	function addDeleteButton($model='dash')
	{
		$this->addData('_form_action',url($this->controller,'delete',[],['refurl'=>current_url(FALSE,TRUE)]));
		$this->addData('_delete_btn',1);
		return $this->addData('_form_action_model',$model);
	}
	
}