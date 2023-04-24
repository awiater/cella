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

class AdminListPageDocument extends AdminPageDocument
{
    /**
	 * Filters values container
	 * @var Array
	 */
	private $_filters;
		
    public function __construct($session,$viewRenderController)
	{
		parent::__construct($session,$viewRenderController);
		$this->viewFile='Core/listview';
		$this->viewData['columns']=[];
		$this->viewData['buttons']=['toolbar'=>[]];
		$this->_filters=[];
	}
    
	/**
	 * Enable filters section
	 * 
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addFilters()
	{
	    if (!array_key_exists('filters',$this->viewData))
	    {
	        $this->addData('filters',[]);
	    }
		$this->viewData['buttons']['filter']=url(debug_backtrace()[1]['class'],debug_backtrace()[1]['function'],['filtered'=>'%filtered%']);
		$this->viewData['buttons']['clear']=url(debug_backtrace()[1]['class'],debug_backtrace()[1]['function'],['filtered'=>0]);
		return $this;
	}
	
	/**
	 * Add input field to filters section
	 * 
	 * @param  string $label Label text
	 * @param  string $id    Field id
	 * @param  mixed  $value Filter value
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addInputFilter($label,$name,$id=null,$value=null)
	{
		$data=['name'=>'filters['.$name.' %]','class'=>'form-control filter'];
		$filters=array_key_exists('filters', $_POST)?$_POST['filters']:[];
		if ($id!=null)
		{
			$data['id']=$id;
		}
		$value=array_key_exists($name, $filters)?$filters[$name]:$value;
		
		if ($value!=null)
		{
			$data['value']=$value;
		}else
		if (array_key_exists($name.' %', $this->_filters))
		{
			$data['value']=$this->_filters[$name.' %'];
		}
		
	    $this->viewData['filters'][]=
	    [
	        'label'=>lang($label),
	        'id'=>$id==null?'id_filters_'.$name:$id,
	        'field'=>form_input($data),
	    ];
		return $this;
	}
	
	public function addDropDownFilter($label,$name,array $options,$value=null,$id=null)
	{
		$args=['name'=>'filters['.$name.' %]','class'=>'form-control filter'];
		$filters=array_key_exists('filters', $_POST)?$_POST['filters']:[];
		if ($id!=null)
		{
			$args['id']=$id;
		}
		$value=array_key_exists($name, $filters)?$filters[$name]:$value;
		$args['value']=null;
		if ($value!=null)
		{
			$args['value']=$value;
		}else
		if (array_key_exists($name.' %', $this->_filters))
		{
			$args['value']=$this->_filters[$name.' %'];
		}
		 $this->viewData['filters'][]=
	    [
	        'label'=>lang($label),
	        'id'=>$id==null?'id_filters_'.$name:$id,
	        'field'=>form_dropdown('filters['.$name.' %]',$options,$args['value'],$args),
	    ];
		return $this;
	}
	
	/**
	 * Add enabled field to filters section
	 * 
	 * @param  string $label Label text
	 * @param  mixed  $value Filter value
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addEnabledFilter($label,$value=null,$fieldName='enabled')
	{
		$filters=array_key_exists('filters', $_POST)?$_POST['filters']:[];
		$value=array_key_exists($fieldName, $filters)?$filters[$fieldName]:$value;
		
		if ($value==null&&array_key_exists($fieldName.' %', $this->_filters))
		{
			$value=$this->_filters[$fieldName.' %'];
		}
		
	    $this->viewData['filters'][]=
	    [
	        'label'=>lang($label),
	        'id'=>'id_filters_'.$fieldName,
	        'field'=>form_dropdown('filters['.$fieldName.' %]',['0'=>lang('cms.general.no'),'1'=>lang('cms.general.yes'),''=>''],$value==null?1:$value,['class'=>'form-control filter']),
	    ];
		return $this;
	}
	
	/**
	 * Add access field to filters section
	 * 
	 * @param  string $label Label text
	 * @param  mixed  $value Filter value
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addAccessFilter($label,$value=null,$data=null)
	{
		$filters=array_key_exists('filters', $_POST)?$_POST['filters']:[];
		$value=array_key_exists('access', $filters)?$filters['access']:$value;
		$data=is_array($data)?$data:null;
		if ($data==null)
		{
			$this->addAccessLevels();
			$data=array_merge([''=>''],$this->viewData['access_levels']);
		}
		if ($value==null&&array_key_exists('access', $this->_filters))
		{
			$value=$this->_filters['access'];
		}			
	    $this->viewData['filters'][]=
	    [
	        'label'=>lang($label),
	        'id'=>'id_filters_access',
	        'field'=>form_dropdown('filters[access]',$data,$value==null?1:$value,['class'=>'form-control filter']),
	    ];
		return $this;
	}
	
	
	
	/**
	 * Add list view table data
	 * 
	 * @param  mixed $data
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addListData($model,$orderby=null,$pagination=TRUE,$filters=[],$logeduseraccess=null)
	{
		$MaxItemsQty=model('Settings/SettingsModel')->getMaxItemsQty();
		
		if (is_subclass_of($model,'\VCMS\Models\Core\VCMSModel'))
		{
			$data=[];
			//$pFilters=array_key_exists('filters', $_POST)?$_POST['filters']:[];
			
			$get=array_key_exists('filtered', $_GET)?$_GET['filtered']:null;
			$pFilters=[];
			if ($get!=null)
			{
				$pFilters=json_decode(base64_decode($get),TRUE);	
			}
			$pFilters=is_array($pFilters)?$pFilters:[];
			$filters=array_merge($pFilters,$filters);
			$this->_filters=$pFilters;
			
			if ($pagination)
			{
				$pagination=$MaxItemsQty;
			}else
			{
				$pagination=null;
			}
			$pagination=array_key_exists('pagination', $_GET)?$_GET['pagination']:$pagination;
			$pagination=$pagination==0?FALSE:$pagination;
			
			$data=$model->filtered($filters==null?[]:$filters,$orderby,$pagination,$logeduseraccess);
			
			if (is_subclass_of($data,'\VCMS\Models\Core\VCMSModel'))
			{
				$data=$data->find();
			}
			
			if ($pagination!=FALSE)
			{
				$this->addPagination($model);
			}
		}else
		{
			$data=$model;
		}
		
		$max=(is_array($model)?count($model):$model->count());
		$this->addData('_pagination',
		[
			'url'=>url(debug_backtrace()[1]['class'],debug_backtrace()[1]['function'],['pagination'=>'%pagination%']),
			'min'=>$MaxItemsQty,
			'max'=>$max<($MaxItemsQty+15)?null:($MaxItemsQty+15),
			'cur'=>$pagination
		]);
		
		$this->addData('items',$data);
		$this->addData('items_raw',base64_encode(json_encode($data)));
		
		return $this;
	}
	
	/**
	 * Add new button url to view
	 * 
	 * @param  string $url
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addNewButton($controller,$action='edit',$refurl=null,$items=[],$args=[],$model=null)
	{		
		$refurl=$refurl==null?current_url(FALSE,TRUE):$refurl;
		$this->viewData['buttons']['toolbar']['new']=[];
		$urlArg=['id'=>'new','refurl'=>$refurl];
		if ($model!=null)
		{
			$urlArg['model']=$model;
		}
		$this->viewData['buttons']['toolbar']['new']['href']=url($controller,$action,$urlArg);
		$args['class']='btn btn-info text-white';
		$args['id']='btn-new';
		
		if (count($items)>0)
		{
			$this->viewData['buttons']['toolbar']['new']['items']=$items;
			$this->viewData['buttons']['toolbar']['new']['href']=url($controller,$action,['id'=>'new','model'=>'%item%','refurl'=>$refurl]);
		}
		$this->viewData['buttons']['toolbar']['new']['args']=$args;
		if (!array_key_exists('dropdown', $args))
		{
			$this->viewData['buttons']['toolbar']['new']['args']['dropdown']='dropdown';
		} 
		$this->viewData['buttons']['toolbar']['new']['text']=html_fontawesome('plus-circle').'&nbsp;'.lang('cms.buttons.new');
		$this->viewData['buttons']['toolbar']['new']['urltag']=url_tag($this->viewData['buttons']['toolbar']['new']['href'],$this->viewData['buttons']['toolbar']['new']['text'],$args);
		
		return $this;
	}
	
	/**
	 * Add remove button url to view
	 * 
	 * @param  string $url
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addDeleteButton($controller,$model,$fieldName,$refurl=null,$action='delete',array $disabledItems=[],$args=[])
	{
		$refurl=$refurl==null?current_url(FALSE,TRUE):$refurl;
		$args['class']='btn btn-danger text-white ml-2';
		$args['id']='btn-remove';
		$this->viewData['buttons']['toolbar']['delete']=
		[
			'href'=>url($controller,$action,['refurl'=>$refurl,'model'=>$model]),
			'field'=>$fieldName,
			'disabled'=>$disabledItems,
			'urltag'=>url_tag(null,html_fontawesome('trash-o').'&nbsp;'.lang('cms.buttons.delete'),$args)
		];
		return $this;
	}
	
	/**
	 * Add edit button url to view
	 * 
	 * @param  string $url     Url
	 * @param  string $text    Button text
	 * @param  Array  $icon    Button Icon (font awesome)
	 * @param  Array  $args    Button properties
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addCustomButton($url,$text,$icon='edit',$args=[])
	{
		if (!array_key_exists('class', $args))
		{
			$args['class']='btn btn-primary text-white mr-2';
		}	
		
		$this->viewData['buttons']['toolbar'][]=
		[
			'href'=>$url,
			'urltag'=>url_tag($url,($icon!=null?html_fontawesome($icon).'&nbsp;':'').lang($text),$args)
		];
		return $this;
	}
	
	
	
	/**
	 * Add edit button url to view
	 * 
	 * @param  string $controller
	 * @param  string $refaction
	 * @param  string $fieldName
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addEditButton($controller,$refaction,$fieldName,$disabledItems=[],$editAction='edit')
	{
		$refaction=base64_encode(url($controller,$refaction));	
		$refaction=current_url(FALSE,TRUE);
		$this->viewData['buttons']['edit']=
		[
			'field'=>$fieldName,
			'url'=>url($controller,$editAction,['id'=>'%field%','refurl'=>$refaction,'model'=>$refaction]),
			'disabled'=>$disabledItems
		];
		return $this;
	}
	
	/**
	 * Add edit button url to view
	 * 
	 * @param  string $controller    Controller class name (without namespace)
	 * @param  string $fieldName     List item data field name
	 * @param  Array  $disabledItems Items for which edit button will be not shown
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addCustomEditButton($url,$fieldName,$disabledItems=[],$icon='fa fa-edit',$btnType='primary',$args=[])
	{	
	    if (!array_key_exists('edit_custom',$this->viewData['buttons']))
	    {
            $this->viewData['buttons']['edit_custom']=[];
	    }
		$this->viewData['buttons']['edit_custom'][]=
		[
			'field'=>is_array($fieldName)?$fieldName:[$fieldName],
			'url'=>$url,
			'disabled'=>$disabledItems,
			'icon'=>$icon,
			'type'=>$btnType,
			'args'=>$args
		];
		return $this;
	}
	
	/**
	 * Add edit button url to view
	 * 
	 * @param  string $controller
	 * @param  string $action
	 * @param  string $fieldName
	 * @param  Array  $disabledItems Items for which edit button will be not shown
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addEnableButton($controller,$action,$fieldName,$disabledItems=[])
	{
		$refurl=base64_encode(url($controller,$action));	
		$this->viewData['buttons']['enable']=
		[
			'enable'=>url($controller,'enable',['id'=>'%field%','refurl'=>$refurl,'enabled'=>1,'model'=>$action]),
			'disable'=>url($controller,'enable',['id'=>'%field%','refurl'=>$refurl,'enabled'=>0,'model'=>$action]),
			'field'=>$fieldName,
			'disabled'=>$disabledItems
		];
		return $this;
	}
	
	/**
	 * Add table column deatils to view data
	 * 
	 * @param  string $label
	 * @param  string $fieldName
	 * @param  string $size
	 * @param  mixed  $value
	 * @param  bool   $format
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addListColumn($label,$fieldName,$size=null,$value=null,$format=null)
	{
		$this->viewData['columns'][]=
		[
			'label'=>lang($label),
			'field'=>$fieldName,
			'size'=>$size,
			'yesno'=>FALSE,
			'value'=>$value,
			'format'=>$format
		];
		return $this;
	}
	
	/**
	 * Add table yes/no column deatils to view data
	 * 
	 * @param  string $label
	 * @param  string $fieldName
	 * @param  string $size
	 * @return VCMS\Libraries\Pages\AdminListPageDocument
	 */
	public function addListYesNoColumn($label,$fieldName,$size=null)
	{
		$this->viewData['columns'][]=
		[
			'label'=>lang($label),
			'field'=>$fieldName,
			'size'=>$size,
			'yesno'=>TRUE
		];
		return $this;
	}
}