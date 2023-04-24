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

class AdminSettingsPageDocument extends AdminPageDocument
{
    public function __construct($session,$viewRenderController)
	{
		parent::__construct($session,$viewRenderController);
		$this->setView('Core/settings');
	}
	
	/**
	 * Add save button and form url to view data
	 * 
	 * @param  $url
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addSaveButton($refurl,$url=null)
	{
		$url=$url==null?url('Settings/AdminSettingsController','save',['refurl'=>Str::base64url_encode($refurl)]):$url;
		$this->viewData['buttons']['save']=$url;
		return $this;
	}
	
	/**
	 * Determine if module access fields will be shown in view
	 * 
	 * @param  String $patern Patern used in fields name
	 * @param  Array  $data   Access level data
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addModuleAccessFields($data=null)
	{
	    if ($data==null)
		{
			$this->addAccessLevels();
			$data=$this->viewData['access_levels'];
		}
		$this->addData('modules',1);
	    $this->viewData['module_access']=
	    [
	    	'patern'=>'modules[%field%]',
	        'fields'=>model('Settings/ModulesModel')->getAccessFieldsNames()
	    ];
		return $this;
	}
	
	/**
	 * Add module data (name, id etc) to view data container
	 * 
	 * @param  mixed $name $data of module
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addModuleData($data)
	{
		if (is_array($data))
		{
			$this->addData('item',$data);
		}else
		{
			$this->addData('item',model('Settings/ModulesModel')->where('modname',$data)->first());
		}
		
		return $this;
	}
	
	/**
	 * Add custom module data to view data container
	 * 
	 * @param  String $tag   Name of key
	 * @param  String $value Key value
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCustomModuleData($tag,$value)
	{
		if (!is_array($this->viewData['item']))
		{
			$this->viewData['item']=[];
		}
		$this->viewData['item'][$tag]=$value;
		return $this;
	}
	
	/**
	 * Add tinymce editor field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Boolean $isSettings Determine if its settings field
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addEditorField($label,$name,$isSettings=FALSE)
	{
		$value='%value%';
		
		if (array_key_exists($name, $this->viewData['item']))
		{
			$value=$this->viewData['item'][$name];
			
		}
		return $this->addEditor($label,$isSettings?$this->parseName($name):'modules['.$name.']',$value,'emailext','200',$id='id_'.$name,TRUE);
	}
	
	/**
	 * Add enabled field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Boolean $isSettings Determine if its settings field
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addEnabledField($label,$name,$isSettings=FALSE)
	{
		return $this->addCustomField($label,($isSettings?$this->parseName($name):'modules['.$name.']'),'@enabled',$name,['id'=>'id_'.$name]);
	}
	

	/**
	 * Add texarea field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Custom field attributes
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addTextAreaField($label,$name,$dataField,$value=null,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}

		if (!array_key_exists('value', $args))
		{
			$args['value']=$value==null?'%value%':$value;
		}
		return parent::addTextAreaField($label,$this->parseName($name),$dataField,null,$args);
	}
	
	/**
	 * Add input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Custom field attributes
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	public function addInputField($label,$name,$dataField,$value=null,array $args=[])
	{
		return parent::addInputField($label,$this->parseName($name),$dataField,$value,$args);
	}
	
	/**
	 * Add dropdown field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $options    Dropdown field options
	 * @param  String  $value      Field value
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addDropDownField($label,$name,array $options,$value,array $args=[])
	{
		return parent::addDropDownField($label,$this->parseName($name),$options,$value,$args);
	}
	
	private function parseName($name)
	{
		return !Str::startsWith($name,'cfg[')&&!Str::startsWith($name,'modules[')?'settings['.$name.']':$name;
	}
	
}