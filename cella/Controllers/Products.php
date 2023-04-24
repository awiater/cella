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
use \CELLA\Helpers\MovementType;
use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;

class Products extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'stocktaking'=>AccessLevel::view
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'aloc'=>'Products/Alocation',
		'stocktake'=>'Products/Stocktake',
		'items'=>'Products/StocktakeItem'
	];
	
	function index()
	{
		return $this->stocktaking();
	}
	
	function stocktaking()
	{
		$status=lang('system.products.stocktake_status_list');
		$filters=[];
		if ($this->request->getGet('filtered')==null && $this->request->getGet('filter')==null)
		{
			$filters=['status <'=>2];
		}
		
		return $this->setTableView()
			 ->setData('stocktake',null,FALSE,null,$filters)
			 ->setPageTitle('system.products.stocktakes_page')
			 ->addFilters('stocktaking')
			 ->addFilterField('reference %')
			 ->addFilterField('status','1',$status[1])
			 ->addFilterField('status','2',$status[2])
			 
			 ->addColumn('system.products.stocktake_reference','reference',TRUE)
			 ->addColumn('system.products.stocktake_operator','operator')
			 ->addColumn('system.products.stocktake_status','status',TRUE,$status)
			 ->addColumn('system.products.stocktake_type','type',FALSE,lang('system.products.stocktake_type_list'))
			 ->addColumn('system.products.stocktake_created','created',FALSE,[],'d M Y')
			 ->addColumn('system.products.stocktake_completed','completed',FALSE,[],'d M Y H:i')
			 ->addColumn('system.products.stocktake_progress','progress')
			 //->addColumn('system.products.stocktake_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 //->addColumn('system.products.stocktake_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 
			 ->addBreadcrumb('system.products.stocktake_mainmenu',url($this,'stocktaking'))
			 
			 ->addEditButton('system.products.stocktake_editbtn','stocktake',null,'btn-primary edtBtn','fa fa-edit',['data-status'=>'-status-'],AccessLevel::view)
			 
			 //->addEnableButton(AccessLevel::modify)
			 //->addDisableButton(AccessLevel::modify)
			 ->addDeleteButton(AccessLevel::modify)
			 ->addNewButton('stocktake/new',AccessLevel::modify)
			  ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')])
			 ->render();
	}
	
	function stocktake($record=null) 
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.products.stocktake_iderror','danger'));
		}
		
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Stocktake->allowedFields, array_fill(0, count($this->model_Stocktake->allowedFields), ''));
			$record[$this->model_Stocktake->primaryKey]='';
			$record['reference']=$this->model_Stocktake->generateReference(null,FALSE);
			$record['enabled']='1';
			$record['access']=$this->model_Settings->get('system.def_access_level');
			$record['type_cfg']='zone';
			$isnew=TRUE;
		}else
		{
			$isnew=FALSE;
			$_record=$this->model_Stocktake->find($record);
			if (!is_array($_record))
			{
				$record=$this->model_Stocktake->filtered(['reference'=>$record])->first();
			}else
			{
				$record=$_record;
			}
		}
		
		//$record=$this->getFlashData('_postdata',$record);

		$params=$this->model_Settings->get('stocktakes.*');
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.products.stocktake_iderror','danger'));
		}
		
		$this->setFormView('Products/stocktake')
			 ->setFormTitle('')
			 ->setPageTitle('system.products.stocktake_editstocktake')
			 ->setCustomViewEnable(TRUE)
			 
			 ->setFormCancelUrl($refurl)
			 
			 ->addData('isnew',$isnew)
			 ->setFieldAccessRule('stocktakes'); 
			 
		$type_list=lang('system.products.stocktake_type_list');
		$status=lang('system.products.stocktake_status_list');
		if (array_key_exists($record['status'], $status))
		{
			$record['status_name']=$status[$record['status']];
		}	
		
		$completed=$record['status']==2 || $record['status']=='2';
		if (!$completed)
		{
			$this->view->setFormAction($this,'save',['stocktake'],['refurl'=>current_url(FALSE,TRUE)]);
		}
		if (!$isnew)
		{
			$this->view
			     ->addInputField('system.products.stocktake_reference','reference',$record['reference'],['readonly'=>1,'class'=>'bg-light'])
				 ->addInputField('system.products.stocktake_type','type_ro',$type_list[$record['type']],['readonly'=>1,'class'=>'bg-light'])
			     ->addDropDownField('system.products.stocktake_status','status',$status,$record['status'],$completed ? ['disabled'=>'true','class'=>'bg-light'] :[])
				 ->addData('items',$this->model_Items->getItemsFromStockTake($record['reference'],null))
				 ->addDataTableScript('stockTakeItemsTable',['searching'=>'true','ordering'=>'false'])
				 ->addData('movements',loadModule('Movements','mevementsbyref',
									[
										$record['reference'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto','mhref','mhinfo'
										],
									]			
							));
		}else
		{
			$this->view->addInputField('system.products.stocktake_reference','reference',$record['reference'],['required'=>'true','maxlength'=>150])
					   ->addDropDownField('system.products.stocktake_type_info','type',$type_list,$record['type'],[])
					   ->addDropDownField('system.products.stocktake_type_zone','type_cfg_zone',$this->model_Warehouse_Location->getZones(['enabled'=>1]),$record['type_cfg'],['class'=>'type_cfg'])
					   ->addDropDownField('system.products.stocktake_type_row','type_cfg_row',$this->model_Warehouse_Location->getRows(['enabled'=>1]),$record['type_cfg'],['class'=>'type_cfg'])
					   ->addDropDownField('system.products.stocktake_type_column','type_cfg_column',$this->model_Warehouse_Location->getColumns(['enabled'=>1]),$record['type_cfg'],['class'=>'type_cfg'])
					   ->addDropDownField('system.products.stocktake_type_size','type_cfg_size',$this->model_Pallet_PalletSize->getForForm('name'),$record['type_cfg'],['class'=>'type_cfg'])
					   ->addDropDownField('system.products.stocktake_type_order','type_cfg_corder',$this->model_Warehouse_Orders->getCOrdersForForm(),$record['type_cfg'],['class'=>'type_cfg']);
		}				
		return $this->view//->addYesNoField('system.products.stocktake_enabled',$record['enabled'],'enabled',$completed ? ['disabled'=>'true','class'=>'bg-light'] : ['required'=>'true'])
				          //->addAcccessField('system.products.stocktake_access',$record['access'],'access',[],$completed ? ['disabled'=>'true','class'=>'bg-light'] :[])
				   		  ->setFormArgs([],['stid'=>$record['stid'],'refurl_ok'=>$refurl])
				   		  ->addHiddenField('type_cfg',$record['type_cfg'],['id'=>'id_type_cfg'])
				  		  ->addCustomFields($this->model_Settings_CustomFields->getFields('stocktakes',$record['stid']))
						  ->addBreadcrumb('system.products.stocktake_mainmenu',url($this,'stocktaking'))
						  ->addBreadcrumb($record['reference'],'/')
						  
						  ->addData('pallet_url',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
						  ->addData('operators',$this->model_Auth_User->getForForm('username','name'))
						  ->addData('record',$record)
				   	      ->render();
	}
	
	function stocktakingitems()
	{
		if (!$this->view->isMobile())
		{
			return redirect()->to(site_url());
		}
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$record=$this->model_Items->filtered(['access'=>'@loged_user','enabled'=>1,'status'=>0,'( operator'=>'@'.loged_user('username'),'|| operator )'=>null])->first();
		$this->model_Tasks_Notification->changeStatus(['text %'=>'STK'],0);
		
		$this->setFormView('Products/check')
			 ->setFormTitle('')
			 ->setPageTitle('system.products.stocktake_editstocktake')
			 ->setCustomViewEnable(TRUE);
		
		if (!is_array($record))
		{
			return $this->view 
			 //->setFormAction($this,'save',['items'],['refurl'=>current_url(FALSE,TRUE)])
			 ->setFormCancelUrl($refurl)
			 ->addData('msg',$this->createMessage('system.products.stocktake_noitems','info'))
			 ->render();
		}
		return $this->view 
			 ->setFormAction($this,'save',['items'],['refurl'=>current_url(FALSE,TRUE)])
			 ->setFormCancelUrl($refurl)
			 ->setFormSaveUrl(['class'=>'btn btn-success w-100 mb-3 btn-lg','text'=>'system.buttons.confirm'])
			 ->setFormArgs([],
			 			   [
			 			   	'siid'=>$record['siid'],
			 			   	'status'=>2,
			 			   	'started'=>formatDate(),
			 			   	'refurl_ok'=>$refurl,
			 			   	'pallet_valid'=>base64_encode($record['pallet']),
							'stocktake'=>$record['stocktake']
			 			   	])
			 ->addInputField('system.products.stocktake_locationinfo','location_text',$record['location'],['class'=>'bg-light','readonly'=>1])
			 ->addInputField('system.products.stocktake_location','location_new',null,['class'=>'form-control-lg'])
			 ->addInputField('system.products.stocktake_pallet','new_pallet',null,['class'=>'form-control-lg','readonly'=>1])
			 
			 ->render();
	}
	
	function emailWhenStockTakeChange($ref)
	{
		if (!is_array($ref))
		{
			$ref=$this->model_Stocktake->where('reference',$ref)->orWhere('sid',$ref)->first();
		}
		
		if (is_array($ref) && array_key_exists('reference', $ref) && array_key_exists('operator', $ref) && array_key_exists('status', $ref))
		{
			if ($ref['status']==2 || $ref['status']=='2')
			{
				$user=$this->model_Auth_User->getUserBasicData($ref['operator']);
				$ref=array_merge($ref,$user);
				$ref['url']=url('Products','stocktake',[$ref['stid']]);
				$ref['file']='stock_take_email';
				$ref['subject']=lang('system.products.stocktake_email_subject',[$ref['reference']]);
				return loadModule('Documents','emaildocument',[$ref]);
			}
		}
	}
	
	function save($type,$post=null)
	{
		$post=$post==null ? $this->request->getPost() : $post;
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($type=='stocktake')
		{
			$isnew=FALSE;
			$model=$this->model_Stocktake;
			$refurl_ok=array_key_exists('refurl_ok', $post) ? $post['refurl_ok'] : $refurl;
			if (!array_key_exists($model->primaryKey,$post) || (array_key_exists($model->primaryKey,$post) && !is_numeric($post[$model->primaryKey])))
			{
		  		unset($post[$model->primaryKey]);
				$isnew=TRUE;
				$post['created']=formatDate();
				$post['progress']=0;
				$post['operator']=loged_user('username');
				$post['status']=0;
				$post['isnew']=$isnew;
				$post['movements_logger']=base64_encode(json_encode(['mhtype'=>'stocktake_create','type'=>'stocktakes','mhref'=>$post['reference']]));
			}
			$data=[];
			
			if (array_key_exists('type_cfg', $post) && array_key_exists('type', $post) && $isnew)
			{
					$data=$this->model_Warehouse_Location->getLocationsForStockTake($post['type'],$post['type_cfg']);
					
					if (is_array($data) && count($data) > 0)
					{
						$this->model_Stocktake->generateReference($post['reference'],TRUE);
					}else
					{
						return redirect()->to(url($this,'stocktaking'))->with('error',$this->createMessage('system.products.stoktakenoitemserror','warning'))->with('_postdata',$post);
					}
			}else
			if ($isnew)
			{
				goto error;
			}
			
			if (array_key_exists('type', $post) && Str::contains($post['type'],'.'))
			{
				$post['type']=Str::afterLast($post['type'], '.');
			}
			
			if ($model->save($post))
			{
				$this->addMovementsFromArray($post);
				
				if (!array_key_exists('stid', $post))
				{
					$this->model_Tasks_Notification->addForMobile(lang('system.products.stocktake_mobilenotifymsg',[$post['reference']]));
				}

				if (Arr::keysExists(['status','enabled'], $post))
				{
					$this->model_Items->changeActiveMultiple($post['reference'],$post['enabled']);
				}
				
				if (count($data) > 0)
				{
					$this->model_Items->filtered(['stocktake',$post['reference'],'status'=>0])->delete();
					$records=[];
					foreach ($data as  $value) 
					{
						$records[]=
						[
							'stocktake'=>$post['reference'],
							'status'=>0,
							'created'=>formatDate(),
							'product'=>'pallet',
							'location'=>$value['code'],
							'pallet'=>$value['reference'],
							'qty'=>$value['corder'],
							'access'=>$value['access'],
							'enabled'=>$value['enabled']
						];
					}
	
					if (!$this->model_Items->insertBatch($records))
					{
						$this->model_Stocktake->filtered(['reference',$post['reference']])->delete();
						return redirect()->to($refurl)->with('error',$this->createMessage($this->model_Items->errors(),'danger'))->with('_postdata',$post);
					}
					return redirect()->to($refurl_ok)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
				}
			}else
			{
				error:
				return redirect()->to($refurl)->with('error',$this->createMessage($model->errors(),'danger'))->with('_postdata',$post);
			}
			return redirect()->to($refurl_ok)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
			
		}else
		if ($type=='items')
		{
			$post['completed']=formatDate();
			$post['operator']=loged_user('username');
			
			if (array_key_exists('pallet_valid', $post) && array_key_exists('new_pallet', $post))
			{
				$post['match']=$this->model_Items->isMatch(base64_decode($post['pallet_valid']),$post['new_pallet']);
			}
			if(array_key_exists('stocktake', $post))
			{
				if (!$this->model_Stocktake->updateProgress($post['stocktake'],TRUE))
				{
					$this->model_Stocktake->changeStockTakeStatus($post['stocktake'],1);
					$this->addMovementHistory(MovementType::stocktake,$post['pallet_valid'],$post['new_pallet'],$post['location_new'],$post['stocktake'],'locations');
				}							
			}
			return parent::save($type,$post);
		}else
		{
			return parent::save($type,$post);
		}
	}
	
	
}