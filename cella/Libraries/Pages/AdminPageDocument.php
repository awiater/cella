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

class AdminPageDocument extends PageDocument
{
	/**
	 * Determine if page is json object
	 * @var Bool
	 */
	private $isJson;
	
    public function __construct($session,$viewRenderController)
	{
		parent::__construct($session,$viewRenderController);
		$this->viewData['buttons']=[];
		$this->isJson=FALSE;
	}
	
	/**
	 * Set page as JSON object
	 * 
	 * @param  Bool $deleteViewData Determine if default view data is removed
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	public function setAsJson($deleteViewData=TRUE)
	{
		if ($deleteViewData)
		{
			$this->viewData=[];
		}
		$this->isJson=TRUE;
		return $this;
	}
	
	/**
	 * Check if page is JSON enabled
	 * 
	 * @return Bool
	 */
	public function isJson()
	{
		return $this->isJson;
	}
	
	
	/**
	 * Add data to json response
	 * 
	 * @param  mixed $data Data to be added
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	public function addJsonData($data)
	{
		if (!array_key_exists('json', $this->viewData))
		{
			$this->addData('json',[]);
		}
		$this->viewData['json']=$data;
		return $this;
	}
	
	/**
	 * Add editing toolbar to page (works only on front end controller, you must add {{ toolbar }} token to view)
	 * 
	 * @param string $pageid     	 Page name or unique id
	 * @param mixed  $showemail  	 Determine if recommend to friend icon will be visible
	 * @param int    $showpdf    	 Determine if pdf download icon will be visible
	 * @param string $dataTag        Optional data tag name
	 * @param int    $customAccLevel Optional access level to check against loged user instead of model modaccviewadmin access level
	 * @param array  $customButtons  Optional array with custom buttons
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function enableEditing($pageid,$showemail=null,$showpdf=0,$dataTag='toolbar',$customAccLevel=null,$customButtons=[])
	{
		throw new \Exception('Only works in front end controllers');
	}
	
	/**
	 * Add field seperator (line) to field view data
	 * 
	 * @param  string $value Optional hml body for sepeartor
	 */
	function addFieldSeparator($value=null)
	{	
		return $this->addCustomField(
			'@hidden',
			'hr',
			$value==null?'<hr/>':$value,
			'',
			[]);
	}
	
	/**
	 * Adds code editor (codemirror) to view
	 * 
	 * @param  string $editoID     Editor tag id
	 * @param  string $editorTheme Editor theme name
	 * @param  string $mode        Editor mode name
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCodeEditor($editoID='formtpl_editor',$editorTheme='blackboard',$mode='xml')
	{
		return	$this->addScript('codemirror_js','@vendor/codemirror/lib/codemirror.js')
				 	->addCss('codemirror_css','@vendor/codemirror/lib/codemirror.css')
				 	->addCss('codemirror_theme_css','@vendor/codemirror/theme/'.$editorTheme.'.css')
				 	->addScript('codemirror_mode_js','@vendor/codemirror/mode/'.$mode.'/'.$mode.'.js')
				 	->addData('_codeeditor_script','CodeMirror.fromTextArea(document.getElementById("'.$editoID.'"), {lineNumbers: true,mode : "xml",htmlMode: true,theme:"'.$editorTheme.'",});')
					->addData('_codeeditor_tag','<textarea class="form-control" name="%name%" id="formtpl_editor">%value%</textarea>');
	}
	
}