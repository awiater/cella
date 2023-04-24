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

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CELLA\Helpers\UserInterface;
use CELLA\Helpers\Strings as Str;
use CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\AccessLevel;
use \CELLA\Helpers\MovementType;
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 * 
 * Front Access Levels : modaccview,modaccedit,modaccamend,modaccdel
 * Admin Access Levels : modaccviewadmin,modacceditadmin,modaccdeladmin
 */

class BaseController extends Controller
{
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * @var Array
	 */
	protected $access=[];
	
	/**
	 * Access module class name if different that current controller
	 * @var String
	 */
	protected $access_controller;
	
	/**
	 * Array with function names which can be accessed only on POST
	 * @var Array
	 */
	protected $postactions=[];

	/**
	 * Array with function names which are exluded from routes actions
	 * @var Array
	 */
	protected $routerexlude=[];
	
	/**
	 * Array with function names which are enabled for api call
	 * @var Array
	 */
	public $apienabled=[];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=[];
	
	/**
	 * Array with controller method remaps ($key is fake function name and $value is actuall function name)
	 */
	public $remaps=[];
	
	/**
	 * Array with access levels
	 * @var Array
	 */
	private $_access_levels;
	

	/**
	 * Constructor.
	 *
	 * @param RequestInterface  $request
	 * @param ResponseInterface $response
	 * @param LoggerInterface   $logger
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		if (method_exists($this, 'beforeInit'))
		{
			$this->{'beforeInit'}($request,$response,$logger);
		}
		$this->helpers=array_merge($this->helpers,['html','date','filesystem','text']);
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		$this->session = \Config\Services::session();
		
		//$this->auth=$this->load(config('App')->authMethodControllerClass);
		
		$this->cookies=service('CookieManager',TRUE);
		
		
		if (!array_key_exists('delete', $this->access))
		{
			$this->access['delete']=AccessLevel::delete;
		}
		
		if (!array_key_exists('enable', $this->access))
		{
			$this->access['enable']=AccessLevel::state;
		}
		
		if (!array_key_exists('save', $this->access))
		{
			$this->access['save']=AccessLevel::create;
		}
		
		if (method_exists($this, 'afterInit'))
		{
			$this->{'afterInit'}();
		}
		
		$this->_access_levels=$this->model_Auth_UserGroup->getForForm('ugref','name');
		$this->view=new Pages\View($this);
		
		$user=$this->auth->getLogedUserInfo();
		if (array_key_exists('enabled', $user) && $user['enabled']==0)
		{
			$this->view->addData('msg',lang('system.errors.no_acces'))
					   ->addData('type','danger');
			$this->View('errors/html/exception');exit;
		}
		
		$this->assocModels['settings']='Settings/Settings';
		$this->assocModels['movements']='Warehouse/Movements';
	}
	
	protected function beforeInit(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		$this->auth=service('auth',FALSE);
		
		if ($this->auth->isLoged()==FALSE)
		{
			return $this->auth->authenticate($request);
		}
	}
	
	/**
	 * Set current view as FormView
	 * 
	 * @param  string $viewName Name of view (default System/form)
	 * @param  bool   $iscached Determines if view is cached (TRUE as default)
	 * 
	 * @return \CELLA\Libraries\Pages\FormView
	 */
	public function setFormView($viewName='System/form',$iscached=TRUE)
	{
		$this->view= new Pages\FormView($this,$iscached);
		return $this->view->setFile($viewName)->addData('_formview_custom',$viewName!='System/form');
	}
	
	/**
	 * Set current view as FormView
	 * 
	 * @param  string $viewName Name of view (default System/table)
	 * @param  bool   $iscached Determines if view is cached (TRUE as default)
	 * 
	 * @return \CELLA\Libraries\Pages\TableView
	 */
	public function setTableView($viewName='System/table',$iscached=TRUE)
	{
		$this->view=new Pages\TableView($this,$iscached);
		return $this->view->setFile($viewName)->addData('_tableview_custom',$viewName!='System/table');
	}

	/**
	 * Set current view as DashBoardView
	 * 
	 * @param  string $viewName Name of view (default System/dashboard)
	 * @param  bool   $iscached Determines if view is cached (TRUE as default)
	 * 
	 * @return \CELLA\Libraries\Pages\DashBoardView
	 */
	public function setDashBoardView($viewName='System/dashboard',$iscached=TRUE)
	{
		$this->view=new Pages\DashBoardView($this,$iscached);
		return $this->view->setFile($viewName)->addData('_dashview_custom',$viewName!='System/dashboard');
	}
	
	/**
	 * Set current view as error message
	 * 
	 * @param  string $errorMSG
	 * @param  bool   $render
	 * 
	 * @return \CELLA\Libraries\Pages\View
	 */
	public function setErrorView($errorMSG,$render=FALSE)
	{
		$this->view->addData('type','danger')
				   ->addData('msg',lang($errorMSG))
				   ->setFile('errors/html/exception');
		return $render ? $this->view->render() : $this->view;
	}
	
	/**
	 * Returns array with avaliable routes (method names)
	 * 
	 * @return Array
	 */
	function getAvaliableRoutes()
	{
		$arr=[];
		$routerexlude=$this->routerexlude;
		$routerexlude[]='enable';
		$routerexlude[]='save';
		$routerexlude[]='delete';
		foreach ($this->access as $key =>$value) 
		{
			if (!in_array($key, $routerexlude))
			{
				$arr[]=$key;
			}
		}		
		return $arr;
	}
	
	
	/**
	 * Enable or disable item in DB
	 */
	public function enable()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=$this->request->getPost();
		
		if (array_key_exists('model', $post))
		{
			$model=$post['model'];
			unset($post['model']);
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_enbale_no','danger'));
		}
		
		if (!array_key_exists($model, $this->assocModels))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_enbale_no','danger'));
		}
		
		
		if (is_array($post) && count($post)>0)
		{
			$tkey=array_keys($post);
			$post=array_values($post);
			$enable=$this->request->getGet('enable');
			
			if (is_array($post[0]) && count($post[0])>0 && is_numeric($enable))
			{
				$model='model_'.$model;
				$model=$this->{$model};

				foreach ($post[0] as $key => $value) 
				{
					$model=$model->orWhere($tkey[0],$value);	
				}
				
				$model=$model->builder->set('enabled',$enable==1 ? 1 : 0);
				
				if ($model->update())
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_enbale_ok','success'));
				}else
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_enbale_no','danger'));
				}	
			}
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_enbale_no','danger'));
	}
	
	/**
	 * Delete Item from database
	 */
	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=count($post) > 0 ? $post : $this->request->getPost();
		
		if (array_key_exists('model', $post))
		{
			$model=$post['model'];
			unset($post['model']);
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no343','danger'));
		}
		
		if (!array_key_exists($model, $this->assocModels))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no348','danger'));
		}
		
		
		if (is_array($post) && count($post)>0)
		{
			$tkey=array_keys($post);
			//$post=array_values($post);
			
			$model='model_'.$model;
			$model=$this->{$model};
			
			if (array_key_exists($model->primaryKey, $post) && is_array($post[$model->primaryKey]) && count($post[$model->primaryKey])>0)
			{	
				foreach ($post[$model->primaryKey] as $key => $value) 
				{
					$model=$model->orWhere($model->primaryKey,$value);	
				}
				
				if ($model->delete())
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_delete_ok','success'));
				}else
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no372','danger'));
				}	
			}
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no376','danger'));
	}
	
	/**
	 * Save data to database
	 * 
	 * @param string $type
	 */
	function save($type,$post=null)
	{
		$post=$post==null ? $this->request->getPost() : $post;
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$refurl_ok=$refurl;
		$type=is_array($type) && count($type)>0 ? $type[0] :$type;
		
		if (array_key_exists('refurl_ok', $post))
		{
			$refurl_ok=$post['refurl_ok'];
		}
		
		$type='model_'.$type;
		$model=$this->{$type};
		if ($model==null)
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_invalid_save_model','danger'))->with('_postdata',$post);
		}
		
		if (array_key_exists($model->primaryKey,$post) && !is_numeric($post[$model->primaryKey]))
		{
		  unset($post[$model->primaryKey]);
		}
		
		$this->uploadFiles($post);
		
		if ($model->save($post))
		{
			//Log movements
			$this->addMovementsFromArray($post);
			
			if (array_key_exists('customfields', $post) && is_array($post['customfields']))
			{
				foreach ($post['customfields'] as $value) 
				{
					if (!array_key_exists('targetid',$value) && array_key_exists($model->primaryKey, $post))
					{
						$value['targetid']=$post[$model->primaryKey];
					}else
					if(!array_key_exists('targetid',$value))
					{
						$value['targetid']=$model->db->insertID();
					}
					$this->model_Settings_CustomFields->save($value);
				}
			}
			
			if (method_exists($this, '_after_save'))
			{	
				$ret=$this->{'_after_save'}($type,$post,$refurl,$refurl_ok);
				
				if ($ret==FALSE)
				{
						exit;
				}else
				if (!is_bool($ret))
				{
					
					return $ret;
				}
			}
			if (array_key_exists('print_pall', $post))
			{
				$post['print_pall']=str_replace('%id%', $model->getLastID(), $post['print_pall']);
			}
			if (!Str::startsWith(strtolower($refurl_ok),'http'))
			{
				$refurl_ok=base64url_decode($refurl_ok);
			}
			
			return redirect()->to($refurl_ok)->with('error',$this->createMessage('system.general.msg_save_ok','success'))->with('print_pall',array_key_exists('print_pall', $post) ? $post['print_pall'] : null);
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage($model->errors(),'danger'))->with('_postdata',$post);
		}
	}
	
	protected function addMovementsFromArray(array $post)
	{
		if (array_key_exists('movements_logger', $post))
			{
				if (is_string($post['movements_logger']))
				{
					if (Str::isJSON($post['movements_logger']))
					{
						$post['movements_logger']=json_decode($post['movements_logger'],TRUE);
					}else
					{
						$post['movements_logger']=json_decode(base64_decode($post['movements_logger']),TRUE);
					}
				}
				
				foreach (is_array($post['movements_logger']) ? $post['movements_logger'] : [] as $value) 
				{
					if (Arr::KeysExists(['mhtype','type','mhref','mhfrom','mhto','mhinfo'], $value))
					{
						$this->addMovementHistory($value['mhtype'],$value['mhfrom'],$value['mhto'],$value['mhref'],$value['mhinfo'],$value['type']);
					}
				}
			}
	}
	
	protected function uploadFiles(&$post)
	{
		$uploads=$this->request->getFiles();
		$uploads_dir=array_key_exists('_uploads_dir', $post) && file_exists(parsePath($post['_uploads_dir'])) ? $post['_uploads_dir'] : '@assets/files/uploads/';
		foreach ($uploads as $fieldName => $file) 
		{
			if ($file->isValid() && !$file->hasMoved())
			{
				$fileName =  $file->getClientName();
				$newFileName=$file->getRandomName();
				$nfilePath=$uploads_dir.DIRECTORY_SEPARATOR.($newFileName);
				$oFilePath=WRITEPATH.'uploads/'.($newFileName);
				$file->store('.',$newFileName);
				if (file_exists($oFilePath))
				{
					$file=new \CodeIgniter\Files\File($oFilePath);
					if ($file->move(parsePath($uploads_dir.DIRECTORY_SEPARATOR,TRUE)))
					{
						$post[$fieldName]=json_encode([$fileName=>$nfilePath]);
					}
				}
			}
		}
	}
	
	function _remap($method,...$params)
	{
		
		$access=AccessLevel::view;
		
		if (is_array($this->remaps) && array_key_exists($method, $this->remaps) )
		{
			$remaps=$this->remaps[$method];
			if (is_array($remaps))
			{
				if (count($remaps) > 2 && is_array($remaps[2]))
				{
					return loadModule($remaps[0],$remaps[1],$remaps[2]);
				}else
				if (count($remaps) > 1 && is_array($remaps[1]))
				{
					$params=$remaps[1];
					$method=$remaps[0];
				}else
				if (count($remaps) > 1)
				{
					return loadModule($remaps[0],$remaps[1]);
				}	
			}else
			if(is_string($remaps))
			{
				$method=$remaps;
			}
		}
		
		if (is_array($this->access) && array_key_exists($method, $this->access))
		{
			if (array_key_exists($this->access[$method], $this->_access_levels))
			{
				$access=$this->_access_levels[$this->access[$method]];
			}else
			{
				$access=$this->access[$method];
			}		
		}
		
		if ($this->auth->hasAccess($access))
		{
			if (method_exists($this, $method))
			{
				return $this->$method(...$params);
			}else
			{
				
				$this->view->setFile('errors/html/exception')
						   ->addData('msg',lang('system.errors.nopagefound_h'))
					   	   ->addData('type','danger')
					   	   ->render();exit;
			}
			
		}else
		{
			$this->view->setFile('errors/html/exception')
						   ->addData('msg',lang('system.errors.no_acces'))
					   	   ->addData('type','danger')
					   	   ->render();exit;
		}
	}
	
	
	/**
	 * Install tables etc
	 */
	 function install()
	 {
		$msg='warning: no models';
	 	if (is_array($this->assocModels) && count($this->assocModels)>0)
		{
			foreach($this->assocModels as $key=>$value)
			{
				$model='model_'.$key;
				$model=$this->{$model};
				if ($model!=null)
				{
					$msg=$model->installstorage();
				}else
				{
					$msg='error: '.$value.' is not valid model';
				}
			}
		}
		end_func:
		return $this->response->setJson([$msg]);
	 }

	/**
	 * Returns session temporary data
	 * 
	 * @param  string $key         Session temp data key
	 * @param  mixed  $defaultData Default data returned if session temp data not exists
	 * 
	 * @return mixed
	 */
	public function getFlashData($key,$defaultData=null)
	 {
	 	if (is_array($this->session->getFlashdata()) && array_key_exists($key, $this->session->getFlashdata()))
		{
			$data=$this->session->getFlashdata($key);
			if ($data!=null)
			{
				return is_string($data) ? lang($data) : $data;
			}
			return $data;
			
		}
		return $defaultData;
	 }
	 
	 
	
	 /**
	 * Create html message container
	 * 
	 * @param  String $message Message text (if prefix with @ it will be used as language tag name)
	 * @param  String $type    Type of message (danger,info,success)
	 * @param  mixed  $encode  Determine if html code is base64 (or base64url) encoded
	 * @return String
	 */
	 public function createMessage($message,$type='info',$encode=FALSE)
	 {
	 	return createErrorMessage($message,$type,$encode);
	 }
	/**
	 * Add movement item to audit table
	 * 
	 * @param  int 	  $mhtype
	 * @param  string $mhfrom
	 * @param  string $mhto
	 * @param  string $mhref
	 * @param  string $mhuser
	 * @param  string $mhinfo
	 * @param  string $mhdate
	 * 
	 * @return bool
	 */
	function addMovementHistory($mhtype,$mhfrom,$mhto,$mhref,$mhinfo=null,$type=null,$user=null)
	{
		if ($mhtype==MovementType::status)
		{
			$pallet_types=$this->model_Settings->get('pallets.pallet_types',TRUE);
			if (array_key_exists($mhfrom, $pallet_types))
			{
				$mhfrom=$pallet_types[$mhfrom];
			}
			if (array_key_exists($mhto, $pallet_types))
			{
				$mhto=$pallet_types[$mhto];
			}
		}
		$user=$user==null ? loged_user('username') : $user;
		$filters=['paramsgroups'=>'movement_types','( param'=>'movement_type_'.$mhtype,'|| tooltip )'=>$mhtype];
		$mhtype=$this->model_Settings->filtered($filters)->first();
		$type=$type==null ? strtolower(Str::afterLast(get_class($this),'\\')) : $type;
		if (is_array($mhtype) && array_key_exists('param', $mhtype) && Str::contains($mhtype['param'],'_'))
		{
			$mhtype=Str::afterLast($mhtype['param'],'_');
			return $this->model_Movements->addItem($mhtype,$user,$mhfrom,$mhto,$mhref,$mhinfo,null,$type);
		}
		return FALSE;	
	}
	
	function isMobile()
	{
		return loged_user('interface')==1;
	}
	
          /**
     * Returns reference url
     * 
     * @param  string $defUrl
     * @param  bool   $decode
     * 
     * @return string
     */
    public function getRefUrl($defUrl = '@<', $decode = TRUE, $checkPost = FALSE) {
        $defUrl = $defUrl == '@<' ? ($decode ? base64url_encode(previous_url()) : previous_url()) : $defUrl;
        $defUrl = $defUrl == '@' ? current_url(FALSE,$decode) : $defUrl;
        $refurl = $this->request->getGet('refurl');
        if ($checkPost) {
            $refurl = $refurl == null ? $this->request->getPost('refurl') : $refurl;
        }

        $refurl = $refurl == null ? $defUrl : $refurl;
        return $decode ? base64url_decode($refurl) : $refurl;
    }
        
	function __get($param)
	{
		if (Str::startsWith($param,'model_'))
		{
			$param=Str::afterLast($param, 'model_');
			if (array_key_exists(strtolower($param), $this->assocModels))
			{
				$param=$this->assocModels[strtolower($param)];
				$param=model(Str::endsWith($param,'Model') ? $param : $param.'Model');
			}else
			{
				$param=str_replace('_', ' ', $param);
				$param=ucwords($param);
				$param=explode(' ', $param);
				if (count($param)<2)
				{
					$param[]=$param[0];
				}
				$param[1]=$param[1].'Model';
				$param=model(implode('/', $param));
			}
			
			if (is_subclass_of($param,'\CodeIgniter\Model'))
			{
				return $param;
			}
			return null;
		}
	}		
	
	/**
	 * Return access level for current module for given access name
	 * 
	 * @param  string $name           Access level name
	 * @param  bool   $checkLogedUser Determine if returned access level will be check against loged user access
	 * @return Int
	 */
	protected function getModuleAccessLevel($name,$checkLogedUser=FALSE)
	{
		$controller=str_replace(['VCMS\\Controllers\\','Controller','Admin'], '', get_class($this));
		$controller=$this->model_Settings_Modules->where('modclass',$controller)->first();
		if (is_array($controller) && array_key_exists($name, $controller))
		{
			return $checkLogedUser ? $controller[$name]<=$this->auth->getLogedUserInfo('access'):$controller[$name];
		}
		return !$checkLogedUser ? 9999 : false;
	}
}
