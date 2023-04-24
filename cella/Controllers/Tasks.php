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

  
namespace CELLA\Controllers;

use \CELLA\Helpers\AccessLevel;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\Arrays as Arr;

class Tasks extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'			=>AccessLevel::view,
		'rules'			=>AccessLevel::create,
		'rule'			=>AccessLevel::create
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'rules'=>'Tasks/Rule',
		'tasks'=>'Tasks/Task'
	];
	
	function rules()
	{
		$this->setTableView()
			 ->setData('rules',['trigger','rorder'],TRUE)
			 ->setPageTitle('system.tasks.rules_page')
			 ->addColumn('system.tasks.rules_name','name',TRUE)
			 ->addColumn('system.tasks.rules_rdesc','rdesc',TRUE)
			 ->addColumn('system.tasks.rules_trigger','trigger',FALSE,$this->model_Settings->get('system.rules_triggers',TRUE))
			 ->addColumn('system.tasks.rules_order','rorder')
			 ->addColumn('system.tasks.rules_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addColumn('system.tasks.rules_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 ->addEditButton('system.tasks.rules_editbtn','rule',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('rule/new');
		return $this->view->render();
	}
	
	function rule($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl);
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Rules->allowedFields, array_fill(0, count($this->model_Rules->allowedFields), ''));
			$record[$this->model_Rules->primaryKey]='';
		}else
		{
			$record=$this->model_Rules->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		if (!array_key_exists($this->model_Rules->primaryKey, $record))
		{
			$record[$this->model_Rules->primaryKey]='';
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.owners.supplier_id_error','danger'));
		}
		$settings=$this->model_Settings->get('system.rules_*');
		return $this->setFormView('Tasks/rule_edit')
					->setFormTitle('')
					->setPageTitle('system.tasks.rule_page')
					->setFormAction($this,'save',['rules'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['rid'=>$record['rid'],'refurl_ok'=>$refurl])
					->setCustomViewEnable(FALSE)
					
					->addInputField('system.tasks.rules_name','name',$record['name'],['required'=>'true','maxlength'=>50])
					->addTextAreaField('system.tasks.rules_rdesc','rdesc',$record['rdesc'],[])
					->addDropDownField('system.tasks.rules_trigger','trigger',Arr::fromFlatten($settings['rules_triggers']),$record['trigger'],[])
					->addDropDownField('system.tasks.rules_action','action_list',Arr::fromFlatten($settings['rules_actions']),$record['action'],[])
					->addTextAreaField('system.tasks.rules_actioncustom','action_custom',$record['action'],['id'=>'id_action_custom','data-mode'=>'hidden'])
					->addNumberField('system.tasks.rules_rorder',$record['rorder'],'rorder',$max=100,$min=0,[])
					->addYesNoField('system.tasks.rules_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addAcccessField('system.tasks.rules_access',$record['access'])
					->addHiddenField('action',$record['action'],['id'=>'id_action'])
					->render();
	}
	
	
}