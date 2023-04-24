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

class FormView extends View
{
	private $_field_accessA=[];
	
	private $_namePrefix=null; 
	
	/**
	 * Add form action url
	 * 
 	 * @param  string $controller
 	 * @param  string $action
 	 * @param  array  $params
 	 * @param  array  $get
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setFormAction($controller,$action=null,array $params=[],array $get=[])
	{
		$this->setFormArgs(['id'=>'edit-form']);
		$this->addScript('jquery.inputmask.min','@vendor/jquery/jquery.mask.min.js');
		$this->setFormSaveUrl(null);	
		return $this->addData('_formview_action',url($controller,$action,$params,$get));
	}
	
	/**
	 * Sets form arguments (ie. class)
	 * 
	 * @param  array $args
	 * @param  array $hiddenFields
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setFormArgs(array $args,array $hiddenFields=[])
	{
		$cargs=$this->getViewData('_formview_action_attr');
		if (!array_key_exists('class', $args) && !$this->isMobile())
		{
			//$args['class']='w-50';
		}
		$args=array_merge(is_array($cargs) ? $cargs : [],$args);
		$hiddenFields['movements_logger']=base64_encode(json_encode([]));
		$this->addCustomScript('movement_function',view('System/Elements/movement_function'),FALSE);
		return $this->addData('_formview_action_attr',$args)->addData('_formview_action_hidden',$hiddenFields);
	}
	
	/**
	 * Determine if custom view is used
	 * 
	 * @param  bool $enabled
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setCustomViewEnable($enabled=TRUE)
	{
		return $this->addData('_formview_custom',$enabled);
	}
	
	/**
	 * Sets cancel url
	 * 
 	 * @param  string $controller
 	 * @param  string $action
 	 * @param  array  $params
 	 * @param  array  $get
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setFormCancelUrl($controller,$action=null,$params=[],$get=[])
	{
		return $this->addData('_formview_urlcancel',url($controller,$action,$params,$get));
	}
	
	/**
	 * Sets save button
	 * 
	 * @param  mixed $data
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setFormSaveUrl($data)
	{
		$data=$data==null ? [] : $data;
		if (is_array($data))
		{
			if (!array_key_exists('text', $data))
			{
				$data['text']='system.buttons.save';
			}
			if (!array_key_exists('type', $data))
			{
				$data['type']='submit';
			}
			if (!array_key_exists('icon', $data))
			{
				$data['icon']='far fa-save';
			}
			if (!array_key_exists('class', $data))
			{
				$data['class']='btn btn-success';
			}
			if (!array_key_exists('id', $data))
			{
				$data['id']='id_formview_submit';
			}
		}
		return $this->addData('_formview_savebtn',$data);
	}
	
	/**
	 * Sets form (card header) title
	 * 
	 * @param  string $title
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setFormTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		return $this->addData('_formview_title',lang($title,$tags));
	} 
	
	/**
	 * Sets custom save button
	 * 
	 * @param  string $text
	 * @param  string $icon
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function setCustomSaveButton($text,$icon=null)
	{
		$icon=$icon==null ? null : $icon.' mr-1';
		return $this->addData('_formview_custom_save',[$icon,lang($text)]);
	}
	/**
	 * Add custom field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Field arguments
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addCustomField($label,$name,$value,$dataField,array $args=[])
	{
		if (is_array($this->_field_accessA) && count($this->_field_accessA) > 0)
		{
			$_namePrefix=$this->_namePrefix.$name;
			if (array_key_exists($_namePrefix, $this->_field_accessA))
			{
				$_namePrefix=$this->_field_accessA[$_namePrefix];
				if (!$this->controller->auth->hasAccess($_namePrefix))
				{
					$args['type']='hidden';
					
				}
			}
			
		}
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
	
	function addCustomFields(array $fieldsData=[],$readonly=FALSE,$patern="customfields[@cfid][value]")
	{
		foreach ($fieldsData as $key => $field) 
		{
			$this->addCustomFieldFromData($field,$readonly,$patern);
		}
		return $this;
	}

	function addCustomFieldFromData($field,$readonly=FALSE,$patern="customfields[@cfid][value]")
	{
		if (is_string($field))
		{
			for ($i=0; $i < 4-substr_count($field,'|') ; $i++) 
			{ 
				$field.='|';
			} 
			$field=array_combine(['name','value','type','label','options'], explode('|', $field));
			$field['cfid']=$field['name'];
		}	
		if (array_key_exists('type', $field))
		{
				if (!array_key_exists('cfid', $field) || (array_key_exists('cfid', $field) && $field['cfid']==null))
				{
					$field['cfid']=$field['name'];
				}
				
				if (!array_key_exists('label', $field))
				{
					$field['label']=$field['name'];
				}
				foreach ($field as $key => $value) 
				{
					if (is_string($key) && is_string($value))
					{
						$patern=str_replace('@'.$key, $value, $patern);
					}
					
				}
				$field['name']=$patern;
				$field['required']=[];
				if ($field['required'])
				{
					$field['required']['required']='TRUE';
				}
				if ($readonly)
				{
					$field['required'][$field['type']=='YesNo' || $field['type']=='AcccessField' ? 'disabled' : 'readonly']=TRUE;
					$field['required']['class']='bg-light';
				}
				if (array_key_exists('typeid', $field) && array_key_exists('cfid', $field))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][type]',$field['typeid']);
				}
				if (array_key_exists('target', $field) && array_key_exists('cfid', $field))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][target]',$field['target']);
				}
				if (array_key_exists('cfid', $field))
				{
					$this->addHiddenField('customfields['.$field['cfid'].'][cfid]',is_numeric($field['cfid']) ? $field['cfid'] : null);
				}
				
				if (!array_key_exists('value', $field))
				{
					$field['value']=null;
				}
				
				if (!array_key_exists('cfid', $field))
				{
					$field['cfid']=$key;
				}
				
				
				if ($field['type']=='InputField')
				{
					$this->addInputField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='TextArea')
				{
					$this->addTextAreaField(ucwords($field['label']),$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='YesNo')
				{
					$this->addYesNoField(ucwords($field['label']),$field['value'],$field['name'],$field['required']);
				}else
				if ($field['type']=='AcccessField')
				{
					$this->addAcccessField(ucwords($field['label']),$field['value'],$field['name'],[],$field['required']);
				}else
				if ($field['type']=='CustomersField')
				{
					$this->addInputListField(ucwords($field['label']),$field['name'],$field['value'],model('Owners/CustomerModel')->getForForm('code','code'),$field['required']);
				}else
				if ($field['type']=='UserField')
				{
					$this->addInputListField(ucwords($field['label']),$field['name'],$field['value'],model('Auth/UserModel')->getForForm('username','username'),$field['required']);
				}else
				if ($field['type']=='DateField')
				{
					if (Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);
						$field['required']=$field['required']+$field['options'];
										
					}
						
					$this->addDatePicker($field['label'],$field['name'],$field['value'],$field['required']);
				}else
				if ($field['type']=='DropDown' && strlen($field['options']) > 0 && (Str::contains($field['options'],'::') || Str::isJson($field['options'])))
				{
					if (Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);					
					}else
					{
						$field['options']=loadModuleFromString($field['options']);
					}
					$this->addDropDownField(ucwords($field['label']),$field['name'],$field['options'],$field['value'],$field['required']);
				}else
				if ($field['type']=='CheckList' && strlen($field['options']) > 0 && (Str::contains($field['options'],'::') || Str::isJson($field['options'])))
				{
					if (Str::isJson($field['options']))
					{
						$field['options']=json_decode($field['options'],TRUE);					
					}else
					{
						$field['options']=loadModuleFromString($field['options']);
					}
					$this->addCheckList(ucwords($field['label']),$field['name'],$field['value'],$field['options'],$field['required']);
				}
							
				
			}
			return $this;
	}
	
	/**
	 * Add enabled field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $value      Field html body
	 * @param  Array   $args       Field arguments
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addYesNoField($label,$value,$name='enabled',array $args=[])
	{
		$options=[lang('system.general.no'),lang('system.general.yes')];
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
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addNumberField($label,$value,$name,$max=100,$min=0,array $args=[])
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
			$args['max']=$max;
		}
		if (!array_key_exists('min', $args))
		{
			$args['min']=$min;
		}
		
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['type']='number';
		return $this->addInputField($label,$name,$value,$args);
	}
	
	/**
	 * Add hidden field to view
	 * 
	 * @param  String  $name       Field name
	 * @param  String  $value      Field value
	 * @param  Array   $args       Field arguments
	 * @return \CELLA\Libraries\Pages\FormView
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
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addDropDownField1($label,$name,array $options,$value,array $args=[])
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
		$value=is_string($value) ? strval($value) : $value;	
		return $this->addCustomField(
		$label,
		$name,
		form_dropdown($name,$options,$value,$args),
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
	 * @return \VLMS\Libraries\Pages\FormView
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
		$args['name']=$name;
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$value=is_string($value) ? strval($value) : $value;	
		return $this->addCustomField(
		$label,
		$name,
		view('System/Elements/select',['args'=>$args,'value'=>$value,'options'=>$options]),
		$name,
		$args);
	}
	
	function addColorPicker($label,$name,$value,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$this->addColorPickerScript($args);
		$this->addInputField($label,$name,null,null,$args);
		return $this;
	}
	
	function addColorPickerScript(array $args=[])
	{
		$this->addScript('bootstrap-colorpicker','@vendor/bootstrap/js/bootstrap-colorpicker.min.js');
		$this->addCss('bootstrap-colorpicker','@vendor/bootstrap/css/bootstrap-colorpicker.min.css');
		if (array_key_exists('id', $args))
		{
			$this->addCustomScript('bootstrap-colorpicker-init','$("#'.$args['id'].'").colorpicker();',TRUE);
		}
		if (array_key_exists('init', $args))
		{
			$this->addCustomScript('bootstrap-colorpicker-init','$("'.$args['init'].'").colorpicker();',TRUE);
		}
		
		return $this;
	}
	
	function addDatePicker($label,$name,$value,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.str_replace(' ', '_', preg_replace('/[^a-z\d ]/i', '', $name)).rand(0,150);
		}
		
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=str_replace(' ', '_', preg_replace('/[^a-z\d ]/i', '', $name)).'_field';
		if (array_key_exists('class', $args))
		{
			$args['class']='form-control '.$args['class'];
		}else
		{
			$args['class']='form-control';
		}
		$args['value']=$value;
		
		if (!array_key_exists('dateFormat', $args))
		{
			$args['dateFormat']='dd M yy';
		}
		
		$args['autocomplete']='off';
		$args['value']='';
		$args['value']=form_input($args).form_input(['name'=>$name,'id'=>$args['id'].'_value','type'=>'hidden']);
		if (!array_key_exists('readonly', $args) || (array_key_exists('readonly', $args) && (!$args['readonly']) || $args['readonly']=='false'))
		{
			$script='$("#'.$args['id'].'").datepicker({ dateFormat: "'.$args['dateFormat'].'" });';
			$script.='$("#'.$args['id'].'_value").datepicker({ dateFormat: "yymmdd0000" });';
			$script.='$("#'.$args['id'].'_value").datepicker("setDate","'.$value.'");';
			$script.='$("#'.$args['id'].'").datepicker("setDate",$("#'.$args['id'].'_value").datepicker("getDate"));';
			if (array_key_exists('minDate', $args))
			{
				$script.="$('#".$args['id']."').datepicker('option', 'minDate',new Date('".$args['minDate']."'));";
			}
		
			$script.='$("#'.$args['id'].'").on("change",function(){';
			$script.='$("#'.$args['id'].'_value").datepicker("setDate",$(this).datepicker("getDate"));});';
			if (array_key_exists('readonly', $args) && $args['readonly'])
			{
				$script.='$("#'.$args['id'].'").datepicker("destroy");';
			}
			$this->addCustomScript('bootstrap-datepicker-init-'.rand(0,150),$script,TRUE);
		}
		return $this->addCustomField(
			$label,
			$name,
			$args['value'],//'<input '.$this->parseTagAttr($args).'>',
			null,
			$args);
	}
	
	/**
	 * Add texarea field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  String  $dataField  Data field name
	 * @param  Array   $args       Custom field attributes
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addTextAreaField($label,$name,$value=null,array $args=[])
	{
		if (!array_key_exists('value', $args))
		{
			$args['value']=$value;
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
			'',
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
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	function addAcccessField($label,$value,$name='access',array $options=[],array $args=[])
	{
		$options=count($options)>0?$options:model('CELLA\Models\Auth\UserGroupModel')->getForForm('ugref');
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
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	public function addInputField($label,$name,$value=null,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=$name;
		if (array_key_exists('class', $args))
		{
			$args['class']='form-control '.$args['class'];
		}else
		{
			$args['class']='form-control';
		}
		$args['value']=$value;
		if (array_key_exists('mask', $args))
		{
			$this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
			$this->addCustomScript('jquery_mask_scr','$.applyDataMask();',TRUE);
			$args['data-mask']=$args['mask'];
			unset($args['mask']);			
		}
		
		
		
		return $this->addCustomField(
			$label,
			$name,
			form_input($args),//'<input '.$this->parseTagAttr($args).'>',
			null,
			$args);
	}
	
	/**
	 * Add input field with button to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	public function addInputButtonField($label,$name,$value=null,$action=null,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=$name;
		if (array_key_exists('class', $args))
		{
			$args['class']='form-control '.$args['class'];
		}else
		{
			$args['class']='form-control';
		}
		$args['value']=$value;
		if (array_key_exists('mask', $args))
		{
			$this->addScript('jquery_mask','@vendor/jquery/jquery.mask.min.js');
			$this->addCustomScript('jquery_mask_scr','$.applyDataMask();',TRUE);
			$args['data-mask']=$args['mask'];
			unset($args['mask']);			
		}
		$bargs=[];
		$data = ['name'=> $args['name'].'_button','id'=>$args['id'].'_button','type'=>'button'];
		if (!array_key_exists('icon', $args))
		{
			$bargs['icon']='...';
		}else
		{
			$data['content']='<i class="'.$args['icon'].'"></i>';
			unset($args['icon']);
		}
		
		if (!array_key_exists('btn_class', $args))
		{
			$data['class']='btn';
			
		}else
		{
			$data['class']=$args['btn_class'];
			unset($args['btn_class']);
		}
		if ($action!=null)
		{
			$data['onClick']=$action;
		}

		$html='<div class="input-group mb-3">';
		$html.=form_input($args);
		$html.='<div class="input-group-append">';
		$html.=form_button($data).'<div></div>';
		
		return $this->addCustomField(
			$label,
			$name,
			$html,
			null,
			$args);
	}
	
	/**
	 * Return field from view data
	 * 
	 * @param  int $id Id of field (position in field list, null for last)
	 * 
	 * @return string 
	 */
	function getField($id=null)
	{
		$count=is_array($this->viewData['fields']) ? count($this->viewData['fields'])-1 : 0;
		$id=$id==null ? $count : $id;
		$id=dot_array_search('fields.'.$id,$this->viewData);
		
		if ($id!=null)
		{
			return view('System/form_fields',['fields'=>[$id]]);
		}
		return null;
	}
	
	function checkFieldAccess($name,$field_access=null)
	{
		if ($field_access==null)
		{
			$field_accessA=$this->model_Settings->get('fieldsaccess.*');
			if (!array_key_exists($name, $field_accessA))
			{
				return FALSE;
			}
			$field_access=$field_accessA[$field_access];
		}	
		return $this->controller->auth->hasAccess($field_accessA);
		
	}
	
	function setFieldAccessRule(string $fields_access)//$namePrefix=null
	{
		$this->_namePrefix=str_replace('__', '', $fields_access.'_');
		$fields_access=$this->controller->model_Settings->get('fieldsaccess.'.$fields_access.'*',FALSE,'values',FALSE);
		$this->_field_accessA=is_array($fields_access) ? $fields_access : [$fields_access];
		
		return $this;
	}
	
	/**
	 * Add checkbox list field
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  mixed   $value 	   Field value
	 * @param  Array   $items	   Array with list items (keys as list values, values as list text)
	 * @param  Array   $args       Custom field attributes
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	public function addCheckList($label,$name,$value=null,array $items=[],array $args=[])
	{
		$label=lang($label);
	  	if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$value=is_array($value) ? implode(',',$value) : $value;
		$value=is_string($value) ? $value : null;
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=$name;
		$div_class=dot_array_search('class.div',$args);
		$div_class=is_array($div_class) ? implode(' ',$div_class): $div_class;
		$div_class='form-check '.$div_class;
		
		$chb_label=dot_array_search('class.label',$args);
		$chb_label=is_array($chb_label) ? implode(' ',$chb_label): $chb_label;
		$chb_label='form-check-label '.$chb_label;
		
		$chb=dot_array_search('class.checkbox',$args);
		$chb=is_array($chb) ? implode(' ',$chb): $chb;
		$chb='form-check-input '.$chb;
		$html='';
		$keyid=0;
		
		foreach ($items as $key => $listvalue) 
		{
			$id=$args['id'].'_option_'.$keyid;
			$html.='<div class="'.$div_class.'">';
			$html.='<input class="'.$chb.'" type="checkbox" value="'.$key.'" id="'.$id.'" name="'.$name.'[]"';
			$html.=Str::contains($value,strval($key)) ? ' checked':null;
			$html.='><label class="'.$chb_label.'" for="'.$id.'">'.$listvalue.'</label>';
			$html.='</div>';
			$keyid++;
		}
		
		
	  	return $this->addCustomField(
			$label,
			$name,
			$html,
			null,
			$args);
	}
	
	/**
	 * Add input field to view
	 * 
	 * @param  String  $label      Field label
	 * @param  String  $name       Field name
	 * @param  Array   $args       Custom field attributes
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	public function addInputListField($label,$name,$value=null,array $items=[],array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='id_'.$name;
		}
		$args['id']=str_replace(['[',']'], ['_',null], $args['id']);
		$args['name']=$name;
		if (array_key_exists('class', $args))
		{
			$args['class']='form-control '.$args['class'];
		}else
		{
			$args['class']='form-control';
		}
		$args['value']=$value;
		$args['list']=$name.'_list';
		$field=form_input($args);
		$field.='<datalist id="'.$name.'_list">';
		foreach ($items as $value) 
		{
			$field.='<option value="'.$value.'"><i class="'.$value.'"></i></option>';
		}
		$field.='</datalist>';
		if (!array_key_exists('validation', $args))
		{
			$args['validation']=TRUE;
		}
		if ($args['validation']==TRUE)
		{
			$field.='<script>';		
			$field.='$("#'.$args['id'].'").on("change",function(){';
			$field.='var loaction=$("#'.$args['list'].'").find("option[value='."'".'" + $(this).val() + "'."'".']");';
			$field.='$(this).removeClass("border-danger");';
			$field.='if($("#'.$args['list'].'").html().length>0 && $(this).val().length>0)';
			$field.='{if(loaction != null && loaction.length > 0){}else{';
			$field.='$(this).addClass("border-danger");$(this).val("");}}});';
			$field.='</script>';/**/
		}
		return $this->addCustomField(
			$label,
			$name,
			$field,
			null,
			$args);
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
			$this->addCustomScript('tinymce_init',view('System/tinymce',$data));
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
			$args=['script'=>$this->controller->view('Core/tinymce',$data)];
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
	 * @return \CELLA\Libraries\Pages\FormView
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
		$args['currentView']=$this;
		return $this->addCustomField(
			$label,
			$name,
			view('Media/filepicker',$args),
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
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
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
	 * Adds code editor (codemirror) to view
	 * 
	 * @param  string $editoID     Editor tag id
	 * @param  string $editorTheme Editor theme name
	 * @param  string $mode        Editor mode name
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCodeEditor($label,$name,$value=null,array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='formtpl_editor';
		}
		if (!array_key_exists('theme', $args))
		{
			$args['theme']='blackboard';
		}
		if (!array_key_exists('mode', $args))
		{
			$args['mode']='xml';
		}

		return	$this->addScript('codemirror_js','@vendor/codemirror/lib/codemirror.js')
				 	->addCss('codemirror_css','@vendor/codemirror/lib/codemirror.css')
				 	->addCss('codemirror_theme_css','@vendor/codemirror/theme/'.$args['theme'].'.css')
				 	->addScript('codemirror_mode_js','@vendor/codemirror/mode/'.$args['mode'].'/'.$args['mode'].'.js')
				 	->addCustomScript('_codeeditor_script','CodeMirror.fromTextArea(document.getElementById("'.$args['id'].'"), {lineNumbers: true,mode : "xml",htmlMode: true,theme:"'.$args['theme'].'"});',TRUE)
					->addTextAreaField($label,$name,$value,$args);
	}
	
	/**
	 * Adds code editor (codemirror) script to view
	 * 
	 * @param  arrays $args Editor tag id
	 * 
	 * @return \VCMS\Controllers\Core\Pages\AdminSettingsPageDocument;
	 */
	function addCodeEditorScript(array $args=[])
	{
		if (!array_key_exists('id', $args))
		{
			$args['id']='formtpl_editor';
		}
		if (!array_key_exists('theme', $args))
		{
			$args['theme']='blackboard';
		}
		if (!array_key_exists('mode', $args))
		{
			$args['mode']='xml';
		}

		return	$this->addScript('codemirror_js','@vendor/codemirror/lib/codemirror.js')
				 	->addCss('codemirror_css','@vendor/codemirror/lib/codemirror.css')
				 	->addCss('codemirror_theme_css','@vendor/codemirror/theme/'.$args['theme'].'.css')
				 	->addScript('codemirror_mode_js','@vendor/codemirror/mode/'.$args['mode'].'/'.$args['mode'].'.js')
					->addScript('codemirror_mode_js','@vendor/codemirror/starter.js');
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
		$this->addCustomScript('fileeditor_tiny',$this->controller->view('Media/editortiny',$args));
		return $this;
	}
	
}