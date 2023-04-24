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
use \CELLA\Helpers\UserInterface;

class Users extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'		=>AccessLevel::delete,
		'groups' 	=>AccessLevel::delete,
		'group'		=>AccessLevel::delete, 
		'profile'	=>AccessLevel::modify,
		'mode'		=>AccessLevel::view
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'profile'=>'Auth/User',
		'user'=>'Auth/User',
		'usergroup'=>'Auth/UserGroup',
	];
	
	function mode()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$user=loged_user();
		if ($user['interface']==0)
		{
			$this->model_User->save(['userid'=>$user['userid'],'interface'=>1]);
			$refurl=site_url();
		}else
		{
			$this->model_User->save(['userid'=>$user['userid'],'interface'=>0]);
		}
		return redirect()->to($refurl);
	}
	
	function groups()
	{
		$this->setTableView()
			 ->setData('usergroup','ugname',TRUE)
			 ->setPageTitle('system.auth.groups_page')
			 ->addColumn('system.auth.groups_ugname','ugname',TRUE)
			 ->addColumn('system.auth.groups_ugdesc','ugdesc')
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.auth.groups_editbtn','group',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('group/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}

	function group($group=null) 
	{
		if ($group==null)
		{
			return redirect()->to(url($this,'groups'));
		}
		if ($group=='new')
		{
			$group=array_combine($this->model_UserGroup->allowedFields, array_fill(0, count($this->model_Auth_UserGroup->allowedFields), ''));
			$group[$this->model_UserGroup->primaryKey]='';
		}else
		{
			$group=$this->model_UserGroup->find($group);
		}
		
		$group=$this->getFlashData('_postdata',$group);
		
		if (!is_array($group) || (is_array($group) && count($group)<1))
		{
			return redirect()->to(url($this,'groups'))->with('error',$this->createMessage(lang('system.errors.invalid_id',[lang('system.auth.groups_user')]),'danger'));
		}
		return $this->setFormView()
					->setFormTitle('system.auth.group_edit',[$group['ugname']])
					->setPageTitle('system.auth.group_page')
					->setFormAction($this,'save',['group'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,'groups')
					->setFormArgs(['autocomplete'=>'off'],['ugid'=>$group['ugid']])
					
					->addBreadcrumb('system.auth.profile_indexbread',url($this))
					->addBreadcrumb('system.auth.groups_indexbread',url($this,'groups'))
					->addBreadcrumb($group['ugid'],'/')
					
					->addInputField('system.auth.groups_ugname','ugname',$group['ugname'],['required'=>'true'])
					//->addNumberField('system.auth.groups_uglevel',$group['uglevel'],'uglevel',1000,1,['required'=>'true'])
					
					->addYesNoField('system.auth.groups_enabled',$group['enabled'],'enabled',['required'=>'true'])
					->addTextAreaField('system.auth.groups_ugdesc','ugdesc',$group['ugdesc'],[])
					->addYesNoField('system.auth.groups_ugview',$group['ugview'],'ugview',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugstate',$group['ugstate'],'ugstate',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugmodify',$group['ugmodify'],'ugmodify',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugedit',$group['ugedit'],'ugedit',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugcreate',$group['ugcreate'],'ugcreate',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugdelete',$group['ugdelete'],'ugdelete',['required'=>'true'])
					->addYesNoField('system.auth.groups_ugsettings',$group['ugsettings'],'ugsettings',['required'=>'true'])
					->render();
	}

	function index() 
	{
		$this->setTableView()
			 ->setData('profile',null,TRUE,null,['access'=>'@loged_user'])//'access'=>'@loged_user'
			 ->setPageTitle('system.auth.profiles.page')
			 ->addFilters('index')
			 ->addFilterField('name %')
			 ->addFilterField('|| username %')
			 ->addFilterField('|| email')
			 ->addColumn('system.auth.profile_name','name',TRUE)
			 ->addColumn('system.auth.profile_username','username')
			 ->addColumn('system.auth.profile_inter','interface',FALSE,UserInterface::getNames())
			 ->addColumn('system.auth.profile_email','email')
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.auth.profiles.editbtn','profile',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('profile/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function profile($user=null) 
	{
		
		if ($user==null)
		{
			return redirect()->to(url($this));
		}
		if ($user=='new')
		{
			$user=array_combine($this->model_Profile->allowedFields, array_fill(0, count($this->model_Auth_User->allowedFields), ''));
			$user[$this->model_Profile->primaryKey]='';
			$user['menuaccess']=$this->model_Profile->getLogedUserMenuAccess();
			$user['menuaccess']=is_array($user['menuaccess']) ? json_encode($user['menuaccess']) : null;
			$user['dashboardaccess']=$this->model_Profile->getLogedUserDashAccess();
			$user['dashboardaccess']=is_array($user['dashboardaccess']) ? json_encode($user['dashboardaccess']) : null;
		}else
		{
			$user=$this->model_Profile->getUserData($user);
		}
		$user=$this->getFlashData('_postdata',$user);
		
		if (!is_array($user) || (is_array($user) && count($user)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage(lang('system.errors.invalid_id',[lang('system.auth.profile_user')]),'danger'));
		}
		
		//if ()
		
		$menuaccess=$this->auth->hasAccess($this->model_Settings->get('users.modifymenuaccess'));
		return $this->setFormView('Users/profile')
					->setFormTitle('system.auth.profile_edit',[$user['name']])
					->setPageTitle('system.auth.profile_page')
					->setFormAction($this,'save',['profile'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this)
					->setFormArgs(['autocomplete'=>'off'],['userid'=>$user['userid']])
					
					->addBreadcrumb('system.auth.profile_indexbread',url($this))
					->addBreadcrumb($user['userid'],'/')
					
					->addData('curtab',$this->request->getGet('tab'))
					->addData('menuaccess_list',$menuaccess ? $this->model_Menu_MenuItems->getForProfileForm() : [])
					->addData('menuaccess',json_decode($user['menuaccess'],TRUE))
					
					->addInputField('system.auth.profile_name','name',$user['name'],['autocomplete'=>'false'])
					->addInputField('system.auth.profile_username','username',$user['username'],$user['username']=='sadmin' ? ['readonly'=>1] : ['required'=>'true'])
					->addInputField('system.auth.profile_email','email',$user['email'],['type'=>'email','required'=>'true'])
					->addYesNoField('system.auth.profile_enabled',$user['enabled'],'enabled',[])
					
					->addDropDownField('system.auth.profile_inter','interface',UserInterface::getNames(),$user['interface'])
					->addYesNoField('system.auth.profile_autologoff',$user['autologoff'],'autologoff',[])
					->addCheckList('system.auth.profile_ugname','accessgroups',$user['accessgroups'],$this->model_UserGroup->getForProfile(),[])
					->addInputField('system.auth.profile_password','pass',null,['type'=>'password',is_numeric($user['userid']) ? 'data-field':'required'=>'true','autocomplete'=>'off'])
					->addInputField('system.auth.profile_password_confirm','password',null,['type'=>'password',is_numeric($user['userid']) ? 'data-field':'required'=>'true','autocomplete'=>'off'])
					
					->addCheckList('','dashboardaccess',$user['dashboardaccess'],$this->model_Settings_Dashboard->getForForm('did','text'),[])
					->render();
	}

	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=count($post) > 0 ? $post : $this->request->getPost();
		
		if (array_key_exists('model', $post) && $post['model']=='usergroup' && array_key_exists('ugid', $post) && is_array($post['ugid']))
		{
			$group=$this->model_usergroup->getSuperAdminsGroup(TRUE);
			if (!is_array($group))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
			}
			$group=array_search($group['ugid'],$post['ugid'],TRUE);
			if (!is_bool($group) && is_numeric($group))
			{
				unset($post['ugid'][$group]);
				if (count($post['ugid'])<1)
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.auth.groups_sadmin_error','warning'));
				}
			}
		}
		return parent::delete($post);
	}

	function save($type,$post=null)
	{
		$post=$this->request->getPost();
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$refurl_ok=$refurl;
		
		$model=$this->model_Profile;
		
		if ($type=='profile')
		{
			if (array_key_exists('pass', $post) && strlen($post['pass'])>0 && array_key_exists('password', $post) && $post['password']!=$post['pass'])
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.user_pass_no','danger'))->with('_postdata',$post);
			}
			
			if (array_key_exists('password', $post) && strlen($post['password'])<1)
			{
				unset($post['password']);
			}

			if (array_key_exists('pass', $post) && strlen($post['pass'])<1)
			{
				unset($post['pass']);
			}

			if (!is_numeric($post['userid']))
			{
				unset($post['userid']);
			}
			
			if (array_key_exists('accessgroups', $post) && is_array($post['accessgroups']))
			{
				$post['accessgroups']=implode(',', $post['accessgroups']);
			}
			
			if (array_key_exists('menuaccess', $post) && is_array($post['menuaccess']))
			{
				$post['menuaccess']=json_encode($post['menuaccess']);
			}
			
			if (array_key_exists('dashboardaccess', $post) && is_array($post['dashboardaccess']))
			{
				$post['dashboardaccess']=json_encode($post['dashboardaccess']);
			}
			
			$refurl_ok=url($this);
		}else
		if($type=='group')	
		{
			$refurl_ok=url($this,'groups');
			$level=0;
			$post['ugperms']='';
			if (array_key_exists('ugview', $post) && $post['ugview']==1)
			{
				$post['ugperms'].='.'.AccessLevel::view;
			}
			if (array_key_exists('ugstate', $post) && $post['ugstate']==1)
			{
				$post['ugperms'].='.'.AccessLevel::state;
			}
			if (array_key_exists('ugmodify', $post) && $post['ugmodify']==1)
			{
				$post['ugperms'].='.'.AccessLevel::modify;
			}
			if (array_key_exists('ugedit', $post) && $post['ugedit']==1)
			{
				$post['ugperms'].='.'.AccessLevel::edit;
			}
			if (array_key_exists('ugcreate', $post) && $post['ugcreate']==1)
			{
				$post['ugperms'].='.'.AccessLevel::create;
			}
			if (array_key_exists('ugdelete', $post) && $post['ugdelete']==1)
			{
				$post['ugperms'].='.'.AccessLevel::delete;
			}
			if (array_key_exists('ugsettings', $post) && $post['ugsettings']==1)
			{
				$post['ugperms'].='.'.AccessLevel::settings;
			}
			if (!is_numeric($post['ugid']))
			{
				$post['ugref']=Str::createUID(25);
			}
			$model=$this->model_UserGroup;
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_save_no','danger'))->with('_postdata',$post);
		}
		
		
		if ($model->save($post))
		{
			return redirect()->to($refurl_ok)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage($model->errors(),'danger'))->with('_postdata',$post);
		}
	}
	
}