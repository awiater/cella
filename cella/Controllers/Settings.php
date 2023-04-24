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

class Settings extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'			=>AccessLevel::settings,
		'customfields'	=>AccessLevel::delete,
		'customfield'	=>AccessLevel::delete,
		'savesettings'	=>AccessLevel::delete,
		'backup'		=>AccessLevel::settings,
		'saveconfig'	=>AccessLevel::delete,
		'params'		=>AccessLevel::settings,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'customfield'=>'Settings/CustomFieldsTypes',
		'settings'=>'Settings/Settings'
	];
	
	function logs()
	{
		
		return $this->setTableView('Pallets/index')
			 ->setData($this->model_Settings->getLogsList(),null,FALSE,null,[])
			 ->setPageTitle('system.pallets.index_page')
			 ->addFilters('logs')
			 ->addFilterField('name %')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')])
			 ->addColumn('system.pallets.index_reference','name',TRUE)
			 
			 ->addEditButton('system.pallets.index_info','showlog',null,'btn-primary actBtn','fas fa-edit',[],AccessLevel::view)
			 
			 ->addDeleteButton(AccessLevel::view)
			
			 ->render();	
	}

	function showlog($record)
	{
		$record=base64url_decode($record);
		$this->setFormView()->setFormTitle($record)
							->setPageTitle($record)
							->addBreadcrumb('system.settings.bread_Settings',url($this))
							->addBreadcrumb('system.settings.mainmenu_logs',url($this,'logs'))
					 		->addBreadcrumb($record,'/');
		$record=parsePath('@writable/logs/'.$record,TRUE);
		if (!file_exists($record))
		{
			
		}
		$record=file_get_contents($record);
		 return $this->view
					 ->setFormCancelUrl(url($this,'logs'))
					 ->addCodeEditor('','',$record,[])
					 ->render();
		dump($record);exit;
	}
	function index()
	{
		$db=$this->model_Settings->get('system.*');
		$mailer=config('Email');
		$app=config('APP');
		//dump($app->defBarcodeType);dump($db);exit;
		
		 return $this->setFormView('Settings/form',FALSE)
					->setFormTitle('system.settings.index_title')
					->setPageTitle('system.settings.index_title')
					->setFormAction($this,'savesettings',[],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this)
					->setFormArgs([],['refurl_ok'=>url($this,'customfields')])
					//Home tab Fields
					->addInputField('system.settings.index_baseurl','cfg[app][baseURL]',$app->baseURL,[])
					->addInputField('system.settings.index_company','settings[company]',$db['company'],[])
					->addDropDownField('system.settings.index_lng','cfg[app][defaultLocale]',array_combine($app->supportedLocales,$app->supportedLocales),$app->defaultLocale,[])
					->addDropDownField('system.settings.index_timezone','cfg[app][appTimezone]',$this->model_Settings->getTimeZonesForForm(),$app->appTimezone,[])
					->addNumberField('system.settings.index_authtokenexpiry',$app->authTokenExpiry,'cfg[app][authTokenExpiry]',90000,3600,[])
					->addDropDownField('system.settings.index_barcode','cfg[app][defBarcodeType]',service('BarcodeGenerator')->getTypes(),$app->defBarcodeType,[])
					//Mailer Tab Fields
					->addInputField('system.settings.index_fromemail','cfg[email][fromEmail]',$mailer->fromEmail,['type'=>'email'])
					->addInputField('system.settings.index_fromname','cfg[email][fromName]',$mailer->fromName,[])
					->addInputField('system.settings.index_smtphost','cfg[email][SMTPHost]',$mailer->SMTPHost,[])
					->addInputField('system.settings.index_smtpuser','cfg[email][SMTPUser]',$mailer->SMTPUser,[])
					->addInputField('system.settings.index_smtppass','cfg[email][SMTPPass]',$mailer->SMTPPass,['type'=>'password'])
					->addDropDownField('system.settings.index_smptpport','cfg[email][SMTPPort]',['25'=>'25','587'=>'587','2525'=>'2525'],strval($mailer->SMTPPort),[])
					->addDropDownField('system.settings.index_smptpcrypto','cfg[email][SMTPCrypto]',['tls'=>'tls','ssl'=>'ssl'],$mailer->SMTPCrypto,[])
					->addDropDownField('system.settings.index_charset','cfg[email][charset]',array_combine(mb_list_encodings(), mb_list_encodings()),$mailer->charset,[])
					//Messages Tab
					->addEditorScript(TRUE)
					->addCodeEditorScript()
					->addColorPickerScript()
					->addTextAreaField('system.settings.index_mailtpl','settings[mailer_mailtpl]',$db['mailer_mailtpl'],['class'=>'editor'])
					//Custom Tabs
					->addData('customtabs',$this->model_Settings->getCustomSettingsTab())
					->render();
	}
	
	function params($editExtra=FALSE)
	{
		
		 $this->setFormView('Settings/params')
					->setFormTitle('')
					->setPageTitle('system.settings.mainmenu_params')
					->setFormAction($this,'savesettings',[],['refurl'=>current_url(FALSE,TRUE)])
					->setFormArgs(['class'=>'w-75'],['refurl_ok'=>url($this,'params')])
					->addData('_formview_custom',FALSE);
		$params=$this->model_Settings->orderby('paramsgroups,param')->findAll();
		$params[]=
			[
				'paramsgroups'=>'config.app',
				'param'=>'parseLngVars',
				'name'=>'cfg[app][parseLngVars]',
				'value'=>strval(config('APP')->parseLngVars),
				'fieldtype'=>'yesno',
				'tooltip'=>'Determine if labels are parsed by current language'
			];
		foreach ($params as  $value) 
		{
			$name=array_key_exists('name', $value) ? $value['name'] : 'settings['.$value['param'].'][value]';
			if ($value['fieldtype']=='access')
			{
				$this->view->addAcccessField($value['paramsgroups'].'.'.$value['param'],$value['value'],$name,[],['tooltip'=>$value['tooltip']]);
			}else
			if ($value['fieldtype']=='numeric')
			{
				$this->view->addNumberField($value['paramsgroups'].'.'.$value['param'],$value['value'],$name,$max=1000,$min=-1000,['tooltip'=>$value['tooltip']]);
			}else
			if ($value['fieldtype']=='text')
			{
				$this->view->addInputField($value['paramsgroups'].'.'.$value['param'],$name,$value['value'],['tooltip'=>$value['tooltip']]);
			}else
			if ($value['fieldtype']=='yesno')
			{
				$this->view->addYesNoField($value['paramsgroups'].'.'.$value['param'],$value['value'],$name,['tooltip'=>$value['tooltip']]);
			}else	
			{
				$this->view->addTextAreaField($value['paramsgroups'].'.'.$value['param'],$name,$value['value'],['rows'=>'3','tooltip'=>$value['tooltip']]);
			}
			
			if ($editExtra)
			{
				$this->view->addDropDownField($value['param'].'_TYPE','settings['.$value['param'].'][fieldtype]',
				[
					'access'=>'access',
					'numeric'=>'numeric',
					'textlong'=>'textlong',
					'text'=>'text',
					'yesno'=>'yesno',
				]
				,$value['fieldtype']=='' ? 'textlong' :  $value['fieldtype']);
				$this->view->addTextAreaField($value['param'].'_TOOLTIP','settings['.$value['param'].'][tooltip]',$value['tooltip'],['rows'=>'3']);
			}
		}				
					
		return $this->view->render();
	}

	function customfields() 
	{
		$this->setTableView()
			 ->setData('customfield',null,TRUE)
			 ->setPageTitle('system.settings.customfields_page')
			 ->addFilters('customfields')
			 ->addFilterField('name %')
			 ->addFilterField('|| enabled')
			 ->addColumn('system.settings.customfield_name','name',TRUE)
			 ->addColumn('system.settings.customfield_type','type',FALSE,$this->model_CustomField->getFieldTypes())
			 ->addColumn('system.settings.customfield_target','target',FALSE,$this->model_Settings->getCustomFieldsTargets())
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.pallets.stack_editbtn','customfield',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('customfield/new');
		return $this->view->render();
	}
	
	function customfield($record=null) 
	{
		if ($record==null)
		{
			return redirect()->to(url($this,'customfields'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_CustomField->allowedFields, array_fill(0, count($this->model_CustomField->allowedFields), ''));
			$record[$this->model_CustomField->primaryKey]='';
		}else
		{
			$record=$this->model_CustomField->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'customfields'))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		return $this->setFormView()
					->setFormTitle('{0}',[$record['name']])
					->setPageTitle('system.settings.customfield_page')
					->setFormAction($this,'save',['customfield'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,'customfields')
					->setFormArgs([],['cftid'=>$record['cftid'],'refurl_ok'=>url($this,'customfields')])
					
					->addInputField('system.settings.customfield_name','name',$record['name'],['required'=>'true','maxlength'=>50])
					->addDropDownField('system.settings.customfield_type','type',$this->model_CustomField->getFieldTypes(),$record['type'],['required'=>'true'])
					->addDropDownField('system.settings.customfield_target','target',$this->model_Settings->getCustomFieldsTargets(),$record['target'],['required'=>'true'])
					->addAcccessField('system.settings.customfield_access',$record['access'],'access',[],['required'=>'true'])
					->addYesNoField('system.settings.customfield_isreq',$record['required'],'required',['required'=>'true'])
					->addYesNoField('system.settings.customfield_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->render();
	}
	
	public function backup($mode='all') 
	{
		$system=TRUE;
		$db=TRUE;
		if ($mode=='system')
		{
			$db=FALSE;
		}
		if ($mode=='db')
		{
			$system=FALSE;
		}
		
		$bck=service('BackupManager')->runBackup(mb_url_title(config('APP')->APPName,'_').'_'.formatDate(),$system,$db,'*',"public_html");
		if (file_exists($bck))
		{
			return $this->response->download($bck, null);
		}
	}

	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=count($post) > 0 ? $post : $this->request->getPost();
		if (array_key_exists('id', $post) && is_array($post['id']) && count($post['id']) > 0)
		{
			if ($this->model_Settings->removeLogs($post['id']))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_delete_ok','success'));
			}
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
	}
	
	public function savesettings() 
	{
		$post=$this->request->getPost();
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null?$this->request->getPost('refurl'):$refurl;
		$refurl=$refurl==null ? previous_url():base64url_decode($refurl);
		
		if (array_key_exists('cfg', $post))
		{
			foreach ($post['cfg'] as $key => $value) 
			{
				if (!$this->saveconfig($key,$value,$key=='database'))
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.settings.error_config_save','danger')); 
				}
			}
		}
		
		$model=$this->model_Settings;
		if (array_key_exists('settings', $post)&&count($post['settings'])>0&&!$model->writeMany($post['settings']))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage($model->errors(),'danger'));
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
	}

	private function saveconfig($group,array $values,$isdb=FALSE) 
	{
		$group=APPPATH.'Config/'.ucfirst($group).'.php';
		
		if (!file_exists($group))
		{
			return FALSE;
		}
		$content=file_get_contents($group);
		foreach ($values as $key => $value) 
		{
			$pattern =$isdb?"/'".$key."'(.*?),/": '/public \$'.$key.'(.*?);/';
			
			$result = preg_match($pattern, $content, $matches);
			
			if (!is_numeric($value)&&!is_array($value))
			{
				$value="'".$value."'";
			}
			if (count($matches)>1)
			{
				$result=str_replace($matches[1],($isdb?' => ':' = ').$value, $matches[0]);
			}
			$content=str_replace($matches[0], $result, $content);
		}
		return file_put_contents($group, $content)>0;
	}
}