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
use VCMS\Helpers\Arrays as Arr;

class PageDocument
{
	/**
	 * Holds all view data
	 * @var array 
	 */
	protected $viewData;
	
	/**
	 * Path to view file
	 * @var string
	 */
	protected $viewFile;
	
	/**
	 * View render controller
	 * @var VCMS\Core\Controller
	 */
	public $viewRenderController;
	
	/**
	 * Determine if breadcrumb is added to view data
	 * @var mixed
	 */
	private $is_breadcrumb;
	
	/**
	 * Breadcrumb tag name used in view file
	 * @var mixed
	 */
	private $breadcrumbTag;
	
	/**
	 * Session shared instance
	 * @var
	 */
	private $session;
	
	/**
	 * Determine if page using main template
	 * @var mixed
	 */
	private $usetemplate;
	
	/**
	 * VCMS Page helper library
	 */
	public function __construct($session,$viewRenderController)
	{
		helper('form');
		$this->usetemplate=TRUE;
		$baseURL=config('App')->baseURL;
		$this->session=$session;
		/*Add Script*/
		$this->addScript('jquery',$baseURL.'templates/vendor/jquery/jquery.min.js');
		$this->addScript('jqueryui',$baseURL.'templates/vendor/jquery/jquery-ui.min.js');
		$this->addScript('popper',$baseURL.'templates/vendor/jquery/popper.js');
		$this->addScript('bootstrap',$baseURL.'templates/vendor/bootstrap/js/bootstrap.bundle.min.js');
		$this->addScript('boostrapswitch',$baseURL.'templates/vendor/bootstrap/js/bootstrap-switch-button.js');
		/*Add CSS*/
		$this->addCss('boostrap',$baseURL.'templates/vendor/bootstrap/css/bootstrap.min.css');
		$this->addCss('boostrapswitch',$baseURL.'templates/vendor/bootstrap/css/bootstrap-switch-button.min.css');
		$this->addCss('fontawesome',$baseURL.'templates/vendor/fontawesome/css/all.min.css');
		$this->addCss('jquery-ui',$baseURL.'templates/vendor/jquery/jquery-ui.min.css');
		$this->addCss('admincss',$baseURL.'templates/admin/default/default.css');
		/*Add base data*/
		$this->addData('site',['baseurl'=>$baseURL]);
		$this->addData('metadata',[]);
		$this->addData('buttons',[]);
		$this->addData('fields',[]);
		$this->viewRenderController=$viewRenderController;
		if ($this->viewRenderController!=null && property_exists($this->viewRenderController, 'currentTemplate'))
		{
			$this->addData('_template',$this->viewRenderController->currentTemplate);
		}
		$this->is_breadcrumb=FALSE;
		$this->breadcrumbTag='breadcrumbs';
		
	}	
	
	/**
	 * Determine if given object is subclass or class of Page
	 * 
	 * @param  mixed $object
	 * @return bool
	 */
	public function isPageObject($object)
	{
		return is_subclass_of($object,'\VCMS\Controllers\Core\Pages\PageDocument')||$object instanceof \VCMS\Controllers\Core\Pages\PageDocument;
	}
	
	/**
	 * Add editing toolbar to page (works only on front end controller, you must add {{ toolbar }} token to view)
	 * 
	 * @param string $pageid     	 Page name or unique id
	 * @param mixed  $showemail  	 Determine if recommend to friend icon will be visible
	 * @param int    $showpdf    	 Determine if pdf download icon will be visible
	 * @param string $dataTag        Optional data tag name
	 * @param int    $customAccLevel Optional access level to check against loged user instead of model modaccviewadmin access level
	 * @param array  $customButtons  Optional array with custom buttons (class,id,tooltip,icon,access,order,mobilevisible)
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function enableEditing($pageid,$showemail=null,$showpdf=0,$dataTag='toolbar',$customAccLevel=null,$customButtons=[])
	{
		$editinfront=model('Settings/SettingsModel')->get('general.editinfront')=='1';
		if ($showemail==null&&!is_bool($showemail)&&!is_numeric($showemail))
		{
			$set_showemail=model('Settings/SettingsModel')->get('general.show_refferricon');
			$set_showemail=$set_showemail=null?0:$set_showemail;
			$showemail=$set_showemail;
		}else
		{
			$showemail=$showemail?'1':'0';
		}
		
		$data=[];
		$data['AccessLevel']=$this->viewRenderController->auth->getLogedUserInfo('access');
		
		if (!is_array($customButtons))
		{
			$customButtons=[];
		}
		
		foreach ($customButtons as $key => $value) 
		{
			if (!array_key_exists('mobilevisible', $value))
			{
				$customButtons[$key]['mobilevisible']=1;
			}
			if (!array_key_exists('order', $value))
			{
				$customButtons[$key]['order']=$key+3;
			}
		}
		
		if ($showemail)
		{
			$customButtons[]=
			[
				'class'=>'btn-secondary text-white',
				'id'=>'btn-mail',
				'tooltip'=>lang('cms.menu.article.mail_tooltip'),
				'icon'=>'fa fa fa-envelope',
				'access'=>'0',
				'order'=>1,
				'mobilevisible'=>1
			];
		}
		
		if (is_array($showpdf) && $editinfront && array_key_exists('showpdf', $showpdf) && $showpdf['showpdf'])
		{
			$customButtons[]=
			[
				'class'=>'btn-secondary text-white ml-2',
				'id'=>'btn-pdf',
				'tooltip'=>lang('cms.menu.article.pdf_tooltip'),
				'icon'=>'fa fa fa-download',
				'access'=>'0',
				'order'=>2,
				'mobilevisible'=>1
			];
		}
		
		$data['url_email']=url('Content/ContentController','refertofriend',['refurl'=>current_url(FALSE,TRUE)]);
		if (($customAccLevel==null && $this->viewRenderController->auth->hasAccess(get_class($this->viewRenderController),'modaccviewadmin')) ||
			($customAccLevel!=null && $this->viewRenderController->auth->getLogedUserInfo('access')>=$customAccLevel))
		{
			$data['modaccviewadmin']=TRUE;
			if ($pageid!=null && $editinfront)
			{
		 		$data['url']=url('Menu/MenuAdminController','editpage',['id'=>$pageid,'model'=>'listpages','_template'=>'Core/offline']);
			
				$customButtons['editbtn']=
				[
					'class'=>'btn-danger text-white mr-4',
					'id'=>'btn-edit',
					'tooltip'=>lang('cms.general.editpage_tooltip'),
					'icon'=>'fa fa fa-edit',
					'access'=>'module',
					'order'=>0,
					'mobilevisible'=>0,
					'url'=>url('Menu/MenuController','editpage',['id'=>$pageid,'refurl'=>current_url(FALSE,TRUE)])
				];
			}
		}else
		{
			$data['modaccviewadmin']=FALSE;
		}
		
		
		
		array_sort_by_multiple_keys($customButtons,['order'=>SORT_ASC]);
		$data['customButtons']=$customButtons;
		
		if (is_array($showpdf))
		{
			$data['pdfurl']=url('Content/ContentController','createArticlePDF',['source'=>base64url_encode(json_encode($showpdf)),'refurl'=>current_url(FALSE,TRUE)]);
		}
		
		if (count($customButtons)>0)
		{
			$this->addData($dataTag,$this->viewRenderController->view('Menu/front_pageedit',$data));
		}
		
		return $this;
	}
	
	/**
	 * Add custom field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCustomField($label,$name,$value,$dataField,array $args=[])
	{
		$required=FALSE;
		if (array_key_exists('required', $args)&&(strtolower($args['required'])=='true'||$args['required']==TRUE))
		{
			$required=TRUE;
		}
		if (array_key_exists('id', $args))
		{
			$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		}
		$this->viewData['fields'][]=
		[
			'label'=>$label,
			'name'=>$name,
			'value'=>$value,
			'args'=>$args,
			'field'=>$dataField,
			'required'=>$required
		];
		return $this;
	}
	
	/**
	 * Add enabled field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addYesNoField($label,$value,$name='enabled',array $args=[])
	{
		$options=[lang('cms.general.no'),lang('cms.general.yes')];
		if (array_key_exists('readonly', $args)&&($args['readonly']==TRUE||$args['readonly']==1))
		{
			return $this->addInputField($label,$name,null,array_key_exists($value, $options)?$options[$value]:null,['readonly'=>'true']);
		}
		return $this->addDropDownField(
			$label,
			$name,
			$options,
			$value,
			$args);
	}
	
	/**
	 * Add number field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addNumberField($label,$value,$name,array $args=[])
	{
		
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		if (!array_key_exists('class', $args))
		{
			$args['class']='form-control';
		}else
		{
			$args['class'].=' form-control';
		}
		if (!array_key_exists('max', $args))
		{
			$args['max']='100';
		}
		if (!array_key_exists('min', $args))
		{
			$args['min']='1';
		}
		if (array_key_exists('readonly', $args))
		{
			$args['readonly']=' readonly';
		}else
		{
			$args['readonly']=null;
		}
		
		if (array_key_exists('required', $args))
		{
			$args['required']=' required';
		}else
		{
			$args['required']=null;
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		return $this->addCustomField(
			$label,
			$name,
			'<input type="number" max="'.$args['max'].'" min="'.$args['min'].'" class="'.$args['class'].'" name="'.$name.'" value="'.$value.'" id="'.$args['id'].'" '.$args['required'].''.$args['readonly'].'>',
			$name,
			$args);
	}
	
	/**
	 * Add hidden field to view
	 * 
	 * @param  String  $name       Field name
	 * @param  String  $value      Field value
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addHiddenField($name,$value,array $args=[])
	{
		if (!array_key_exists('name', $args))
		{
			$args['name']=$name;
		}
		$args['value']=$value;
		if (!array_key_exists('type', $args))
		{
			$args['type']='hidden';
		}
		
		$html=form_input($args);
		return $this->addCustomField(
			'@hidden',
			$name,
			$html,
			$name,
			$args);
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
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}

		if (array_key_exists('class', $args))
		{
			$args['class'].=' form-control';
		}else
		{
			$args['class']='form-control';
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);	
		return $this->addCustomField(
		$label,
		$name,
		form_dropdown($name,$options,$value,$args),
		$name,
		$args);
	}
	
	function addColorPicker($label,$name,$value,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$this->addScript('bootstrap-colorpicker','@templates/vendor/bootstrap/js/bootstrap-colorpicker.min.js');
		$this->addCss('bootstrap-colorpicker','@templates/vendor/bootstrap/css/bootstrap-colorpicker.min.css');
		$this->addCustomScript('bootstrap-colorpicker-init','$("#'.$args['id'].'").colorpicker();',TRUE);
		$this->addInputField($label,$name,null,$value=null,$args);
		return $this;
	}
	
	function addDatePicker($label,$name,$value,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$this->addCustomScript('bootstrap-datepicker-init','$("#'.$args['id'].'").datepicker();',TRUE);
		$this->addInputField($label,$name,null,$value=null,$args);
		return $this;
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
	function addTextAreaField($label,$name,$dataField,$value=1,array $args=[])
	{
		if (!array_key_exists('value', $args))
		{
			$args['value']=$value==null?'%value%':$value;
		}
		
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$args['name']=$name;
		if (array_key_exists('class', $args))
		{
			$args['class'].=' form-control';
		}else
		{
			$args['class']='form-control';
		}
		
		return $this->addCustomField(
			$label,
			$name,
			form_textarea($args),
			$dataField,
			$args);
	}
	
	/**
	 * Add access dropdown field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field value
	 * @param  Array   $options    Access levels array
	 * @param  Array   $args       Field arguments
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addAcccessField($label,$value,$name='access',array $options=[],array $args=[])
	{
		$options=count($options)>0?$options:model('VCMS\Models\Users\LevelsModel')->getLevelsForForm();
		if (array_key_exists('readonly', $args)&&($args['readonly']==TRUE||$args['readonly']==1))
		{
			return $this->addInputField($label,$name,null,array_key_exists($value, $options)?$options[$value]:null,['readonly'=>'true']);
		}else
		{
		return $this->addDropDownField(
			$label,
			$name,
			$options,
			$value,
			$args);
		}
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
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=$name;
		if (array_key_exists('class', $args))
		{
			$args['class'].=' form-control';
		}else
		{
			$args['class']='form-control';
		}
		
		if (array_key_exists('mask', $args))
		{
			$this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
			$this->addCustomScript('jquery_mask_scr','$.applyDataMask();',TRUE);
			$args['data-mask']=$args['mask'];
			unset($args['mask']);			
		}
		
		$args['value']=$value==null?'%value%':$value;
		
		return $this->addCustomField(
			$label,
			$name,
			form_input($args),//'<input '.$this->parseTagAttr($args).'>',
			$dataField,
			$args);
	}
	
	/**
	 * Add save button and form url to view data
	 * 
	 * @param  $url
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addSaveButton($url)
	{
		$this->viewData['buttons']['save']=$url;
		return $this;
	}
	
	/**
	 * Add CSS link and Script link for full calendar
	 * 
	 * @param  Bool $addTimePicker Determines if time picker will be added also
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addCalendarScript($addTimePicker=FALSE)
	{
		if ($addTimePicker)
		{
			$this->addTimePickerScript();
		}
		
		$this->addScript('fullcalendar','@vendor/fullcalendar/main.min.js');
		
		$locale=config('APP')->defaultLocale;
		if ($locale!='en')
		{
			$this->addScript('fullcalendar_lang','@vendor/fullcalendar/lang/locales-all.min.js');
			
		}else
		{
			$locale='en';
		}
		$this->addData('_fullcalendar_locale',$locale);
		return $this->addCss('calendar','@vendor/fullcalendar/main.min.css')
					->addScript('fullcalendar_init','@vendor/fullcalendar/init.js');
	}
	
	/**
	 * Add CSS link and Script link for boostrap time picker
	 * 
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addTimePickerScript()
	{
		return $this->addScript('bootstrap-datetimepicker1','@templates/vendor/bootstrap/js/moment.min.js')
				    ->addScript('bootstrap-datetimepicker','@templates/vendor/bootstrap/js/bootstrap-datetimepicker.min.js')
				    ->addCss('bootstrap-datetimepicker','@templates/vendor/bootstrap/css/bootstrap-datetimepicker.min.css');
	}
	
	/**
	 * Add wyswig editor script tag to scripts section
	 * 
	 * @param bool   $autoInit    Determine if tinymce init script will be added to page
	 * @param string $editortType Type of editor toolbar (simple,email,emailext,full)
	 * @param string $editorTag   Editor tag name
	 * 
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addEditorScript($autoInit=FALSE,$editortType='simple',$editorTag='.editor')
	{
		$this->addScript('tinymce','@vendor/tinymce/tinymce.min.js');
		$this->addScript('elfinder_js','@vendor/elfinder/js/elfinder.min.js');
		$this->addScript('tinymceElfinder','@vendor/elfinder/tinymceElfinder.js');
		$this->addCss('Elfinder_base','@vendor/elfinder/css/elfinder.min.css');
		$this->addCss('Elfinder_theme','@vendor/elfinder/css/theme.css');
		if ($autoInit)
		{
			$data=
			[
				'id'=>$editorTag,
				'tinytoolbar'=>$editortType,
				'height'=>200,
			];
			if ($editortType!='simple')
			{
				$data['connector']=url('Media/MediaAdminController','api');
				$data['editorid']='tinymce_editor_'.$editortType;
				$data['toolbar']=base64_encode(json_encode([['back','upload']]));
			}
			$data['language']=config('APP')->defaultLocale;	
			$this->addCustomScript('tinymce_init',$this->viewRenderController->view('Core/tinymce',$data));
		}
		return $this;
	}
	
	/**
	 * Add wyswig editor tag (must be put in script section of view)
	 *
	 * @param  String $label   Field label text 
	 * @param  String $name   Field name 
	 * @param  String $value  Field value 
	 * @param  String $mode   Editor mode (simple,full)
	 * @param  Int    $height Editor height
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addEditor($label,$name,$value,$mode='simple',$height='200',$id=null,$includeScript=TRUE)
	{
		$fid=$id==null?'id_'.$name:$id;
		$data=
		[
			'id'=>$id==null?'.editor':'#'.$id,
			'tinytoolbar'=>$mode,
			'height'=>$height,
			'connector'=>url('Media/MediaAdminController','api'),
			'editorid'=>str_replace(['[',']'], ['_',''], $fid)
		];
		$data['toolbar']=base64_encode(json_encode([['back','upload']]));
		$data['language']=config('APP')->defaultLocale;	
		if ($includeScript)
		{
			$this->addEditorScript();
			$args=['script'=>$this->viewRenderController->view('Core/tinymce',$data)];
		}else
		{
			$args=[];
		}
		
		$this->addFileManagerLib();
		$this->addCustomField(
					$label,
					$name,
					form_textarea(['name'=>$name,'value'=>$value,'id'=>$id==null?'id_'.$name:$id,'class'=>'form-control editor']),
					$name,
					$args
					);
		return $this;
	}
	
	/**
	 * Add file picker input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Default value of field
	 * @param  String  $dir        File dir name
	 * @param  String  $isPath     Determine if breadcumb is visible in file manager
	 * @param  String  $isUpload   Determine if upload button is visible in file manager
	 * @param  Array   $args       Custom field attributes
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addFilePicker($label,$name,$value,$dir='.',$isPath=TRUE,$isUpload=TRUE,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.str_replace(['][','[',']'], ['_',''], $name);
		}
		
		if (array_key_exists('class', $args))
		{
			$args['class'].=' form-control';
		}else
		{
			$args['class']='form-control';
		}
		
		$args['name']=$name;
		$args['value']=$value;
		$args['showpath']=$isPath;
		
		if (array_key_exists('onlyMimes', $args))
		{
			$args['onlyMimes']=base64_encode(json_encode($args['onlyMimes']));
		}
		if (!array_key_exists('picker_type', $args))
		{
			$args['picker_type']='files';
		}
		
		$args['toolbar']=[['back']];
		if ($isUpload)
		{
			$args['toolbar'][0][]='upload';
		}
		$args['toolbar']=base64_encode(json_encode($args['toolbar']));
		$args['baseURL']=config('App')->baseURL;
		$args['language']=config('APP')->defaultLocale;
		
		if ($dir!=null&&$dir!='.')
		{
			$args['dir']='l1_'.base64url_encode($dir);
		}
		
		$this->addEditorScript();
		$args['connecturl']=url('Media/MediaAdminController','api');
		return $this->addCustomField(
			$label,
			$name,
			call_user_func_array([$this->viewRenderController,'view'], ['Media/filepicker',$args]),
			$name,
			$args);
	}

	/**
	 * Add image picker input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Default value of field
	 * @param  String  $dir        File dir name
	 * @param  String  $isPath     Determine if path field is visible
	 * @param  String  $isUpload   Determine if upload button is visible
	 * @param  Array   $args       Custom field attributes
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addImagePicker($label,$name,$value,$dir='.',$isPath=FALSE,$isUpload=FALSE,array $args=[])
	{
		$args['onlyMimes']=['image'];
		$args['picker_type']='images';
		if (!array_key_exists('previewimage', $args))
		{
			$args['previewimage']='1';
		}
		
		return $this->addFilePicker($label,$name,$value,$dir,$isPath,$isUpload,$args);
	}
	
	
	/**
	 * Add cancel button to view data
	 * 
	 * @param  $url
	 * @return \VCMS\Controllers\Core\Pages\PageDocument
	 */
	function addCancelButton($url)
	{
		$this->viewData['buttons']['cancel']=$url;
		return $this;
	}

	
	/**
	 * Insert access level to view data container
	 * 
	 * @param  Array $data
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addAccessLevels(array $data=[])
	{
		if (count($data)<1&&!array_key_exists('access_levels', $this->viewData))
		{
			$this->addData('access_levels',count($data)>0?$data:model('Users/LevelsModel')->getLevelsForForm());
		}else
		{
			$this->addData('access_levels',$data);
		}
		return $this;
	}

	/**
	 * Insert path to CSS file into view data container
	 * 
	 * @param  string $tag  Name of CSS file (or token used later in view file)
	 * @param  string $path Path to CSS file
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addCss($tag,$path)
	{
		$path=$this->parsePath($path);
		$this->viewData['css'][$tag]=link_tag($path);
		return $this;
	}
	
	/**
	 * Add custom data to view data container
	 * 
	 * @param  string $tag  Name token used later in view file
	 * @param  mixed  $value Value of data
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addData($tag,$value)
	{
		if ($tag=='[]')
		{
			$this->viewData=$value;
		}else
		if ($tag==null)
		{
			$this->viewData[]=$value;
		}else
		{
			$this->viewData[$tag]=$value;
		}
		return $this;
	}
	
	/**
	 * Insert path to script file into view data container
	 * 
	 * @param  string $tag  Name of script file (or token used later in view file)
	 * @param  string $path Path to script file
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addScript($tag,$path,array $args=[])
	{
		$path=$this->parsePath($path);
		foreach ($args as $key => $value) 
		{
			$args[$key]=$this->parsePath($value);
		}
		$args['src']=$path;
		$this->viewData['scripts'][$tag]=script_tag($args);
		return $this;
	}
	
	/**
	 * Insert custom script into view data container
	 * 
	 * @param  string $tag      Name of script file (or token used later in view file)
	 * @param  string $body     Script body
	 * @param  bool   $docReady Determine if script body will be enclosed in document ready function
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function addCustomScript($tag,$body,$docReady=FALSE)
	{
		if ($docReady)
		{
			$body='$(document).ready(function(){'.$body.'})';
		}
		$this->viewData['scripts'][$tag]='<script>'.$body.'</script>';
		return $this;
	}
	
	/**
	 * Disable main template (mark to not use template)
	 * 
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	 public function templateDisable()
	 {
	 	$this->usetemplate=FALSE;
		return $this;
	 }
	 
	 /**
	 * Enable main template (mark to use template)
	 * 
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	 public function templateEnable()
	 {
	 	$this->usetemplate=TRUE;
		return $this;
	 }
	 
	 /**
	 * Determine if template is mark as disabled (not to use)
	 * 
	 * @return Bool
	 */
	 public function isTemplateEnabled()
	 {
	 	return $this->usetemplate;
	 }
	 
	 
	 public function addFlashData($key)
	 {
	 	if (is_array($this->session->getFlashdata())&&array_key_exists($key, $this->session->getFlashdata()))
		{
			$data=$this->session->getFlashdata($key);
			$this->addData($key,is_string($data)?lang($data):$data);
		}
		return $this;
	 }
	 
	 /**
	  * Add pagination to view data
	  * 
	  * @param  mixed  $type      Model instance
	  * @param  mixed  $type      Type of pagination links
	  * @param  string $groupName Links group name
	  * @return VCMS\Libraries\Pages\PageDocument
	  */
	 public function addPagination($model,$type='default',$groupName='default',$viewTag='pagination')
	 {
	 	$type=$type==null?'default':$type;
		$groupName=$groupName==null?'default':$groupName;
	 	if ($model->pager!=null)
		{
	 		if (is_array($type)&&Arr::KeysExists(['page','perPage','total'],$type))
			{
				$this->viewData[$viewTag]=$model->pager->makeLinks($type['page'], $type['perPage'], $type['total']);
			}else
			if(is_string($type)&&$type=='simple')
			{
				$this->viewData[$viewTag]=$model->pager->simpleLinks($groupName);
			}else
			{
				$this->viewData[$viewTag]=$model->pager->links($groupName);
			}
		}
		return $this;
	 }
	 
	 public function addBreadcrumbsFromPage(array $page)
	 {
	 	$crumbs=[];
	 	if (array_key_exists('pageparent', $page)&&array_key_exists('pagename', $page))
		{
			$crumbs['@home']=config('App')->baseURL;
			$page['pageparent']=model('Menu/PageGroupModel')->find($page['pageparent']);
			if ($page['pageparent']!=null)
			{
				$crumbs[ucwords($page['pageparent']['name'])]=url('Menu/MenuController','groupdashboard',['id'=>base64url_encode($page['pageparent']['name'])]);
			}
			$crumbs[ucwords($page['pagename'])]=url('/'.$page['pagename'],null);
		}
		
		return $this->addBreadcrumbs($crumbs);
	 }
	 
	/**
	 * Add breadcrumb variable to view data
	 * 
	 * @param  array $crumbs Crumbs array (if empty crumbs will be generate automaticly)
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	 public function addBreadcrumbs(array $crumbs=[],$tag=null,$global=FALSE)
	 {
	 	if ($global)
		{
			$this->addData('_breadcrumbs',$crumbs);		
		}else
		{
			if (count($crumbs)>0)
			{
				if ($this->is_breadcrumb==FALSE)
				{
					$this->is_breadcrumb=new \VCMS\Libraries\Breadcrumb();
				}
				
				foreach ($crumbs as $key => $value) 
				{
					if ($key=='@home')
					{
						$key='<i class="fa fa-home"></i>';
					}
					$this->is_breadcrumb->add($key,$value);
				}
			}else
			{
				$this->is_breadcrumb=TRUE;
			}
		
			if ($tag!=null)
			{
				$this->breadcrumbTag=$tag;
			}
		}		
		return $this;

	 }
	
	/**
	 * Setting view path
	 * 
	 * @param  string $fileName Path to view file
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function setView($fileName)
	{
		$this->viewFile=$fileName;
		return $this;
	}
	
	/**
	 * Sets page meta title value
	 * 
	 * @param  string $value
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function setTitle($value,$join=FALSE)
	{
		if ($join!=FALSE)
		{
			$join=is_string($join)?$join:' ';
			if (strlen($this->viewData['metadata']['title'])<1)
			{
				$this->viewData['metadata']['title']='';
			}
			$this->viewData['metadata']['title'].=$join.$value;
		}else
		{
			$this->viewData['metadata']['title']=$value;
		}
		
		return $this;
	}
	
	/**
	 * Sets page meta description value
	 * 
	 * @param  string $value
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function setDescription($value,$join=FALSE)
	{
		if (!$join)
		{
			$this->viewData['metadata']['description']=$value;
		}else
		{
			if (strlen($this->viewData['metadata']['description'])<1)
			{
				$this->viewData['metadata']['description']='';
			}
			$this->viewData['metadata']['description'].=' '.$value;
		}
		return $this;
	}
	
	function addFileManagerLib($dir='.',$isPath=FALSE,$isUpload=FALSE,array $allowedMimes=[])
	{
		$args=[];
		$args['showpath']=$isPath;
		if (count($allowedMimes)>0)
		{
			$args['onlyMimes']=base64_encode(json_encode($allowedMimes));
		}
		
		$args['toolbar']=[['back']];
		if ($isUpload)
		{
			$args['toolbar'][0][]='upload';
		}
		$args['toolbar']=base64_encode(json_encode($args['toolbar']));
		$args['baseURL']=config('App')->baseURL;
		if ($dir!=null&&$dir!='.')
		{
			$args['dir']='l1_'.base64url_encode($dir);
		}
		
		$this->addEditorScript();
		$args['connecturl']=url('Media/MediaAdminController','api');
		$this->addCustomScript('fileeditor_tiny',$this->viewRenderController->view('Media/editortiny',$args));
		return $this;
	}
	
	/**
	 * Sets page meta keywords value
	 * 
	 * @param  mixed $value
	 * @return VCMS\Libraries\Pages\PageDocument
	 */
	public function setKeywords($value,$join=FALSE)
	{
		$value=is_array($value)?implode(',',$value):$value;
		if ($join)
		{
			if (strlen($this->viewData['metadata']['keywords'])<1)
			{
				$this->viewData['metadata']['keywords']='';
			}
			$this->viewData['metadata']['keywords'].=','.$value;
		}else
		{
			$this->viewData['metadata']['keywords']=$value;
		}
		return $this;
	}
	
	/**
	 * Merge view data
	 * 
	 * @param array $data Array to merge wit meta data
	 */
	public function mergeData($data,$strict=FALSE)
	{
		if ($this->isPageObject($data))
		{
			$data=$data->getViewData();
		}else
		if (!is_array($data))
		{
			$data=[];
		}
		$this->viewData=$this->mergeViewData($this->viewData,$data);
		
	}
	
	private function mergeViewData($data1,$data2)
	{
		foreach ($data2 as $key => $value) 
		{
			if (array_key_exists($key, $data1)&&$key!='metadata')
			{
				if (is_array($data1[$key]))
				{
					if (is_array($data2[$key]))
					{
						$data1[$key]=array_merge($data1[$key],$data2[$key]);
					}else
					{
						$data1[$key][]=$data2[$key];
					}
					
				}else
				{
					$data1[$key]=$data2[$key];
				}	
			}else
			if ($key!='metadata')
			{
				$data1[$key]=$data2[$key];
			}
		}
		return $data1;
	}
	/**
	 * Returns linked to page view path
	 * 
	 * @return string
	 */
	public function getView()
	{
		return $this->viewFile;
	}
	
	/**
	 * Return view data as array
	 * 
	 * @return Array
	 */
	public function getViewData($key=null)
	{		
		if ($this->is_breadcrumb!=FALSE)
		{
			
			if ($this->is_breadcrumb instanceof \VCMS\Libraries\Breadcrumb)
			{
				$this->viewData[$this->breadcrumbTag]=$this->is_breadcrumb->render();
			}else
			{
				$this->is_breadcrumb=new \VCMS\Libraries\Breadcrumb();
				$this->viewData[$this->breadcrumbTag]=$this->is_breadcrumb->buildAuto();
			}
		}
		if (array_key_exists('scripts', $this->viewData))
		{
			$this->addData('_scripts',implode(PHP_EOL,$this->viewData['scripts']));
		}
		
		if (array_key_exists('css', $this->viewData))
		{
			$this->addData('_css',implode(PHP_EOL,$this->viewData['css']));
		}
		if ($key==null)
		{
			return $this->viewData;
		}else
		{
			helper('array');
			return dot_array_search($key);
		}
	}
	
	/**
	 * Parse given path to full website or server path
	 * 
	 * @param  bool $direct If true server path will be used instead of website url
	 * 
	 * @return string 
	 */
	private function parsePath($path,$direct=FALSE)
	{
		$baseURL=$direct ? FCPATH : config('App')->baseURL;
		$repl=
		[
			'@vendor'=>$baseURL.'templates/vendor/',
			'@templates'=>$baseURL.'templates/',
			'@storage'=>$baseURL.'writable/',
			'@media'=>$baseURL.'media/',
			'@base'=>$baseURL,
			'://'=>':#',
			'//'=>'/',
			':#'=>'://'
		];
		return str_replace(array_keys($repl),array_values($repl), $path);
	}
	
	private function parseTagAttr(array $attr)
	{
		$result='';
		foreach ($attr as $key => $value) 
		{
			$result.=$key.'="'.$value.'" ';
		}
		return $result;
	}
}