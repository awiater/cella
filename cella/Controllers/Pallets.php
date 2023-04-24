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
use \CELLA\Helpers\UserInterface;
use \CELLA\Helpers\Arrays as Arr;

class Pallets extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'move'		=>AccessLevel::state,
		'index'		=>AccessLevel::view, 
		'label'		=>AccessLevel::view,
		'pallet'	=>AccessLevel::view,
		'heights'	=>AccessLevel::delete, 
		'height'	=>AccessLevel::delete, 
		'sizes'		=>AccessLevel::delete, 
		'size'		=>AccessLevel::delete,
		'save'		=>AccessLevel::view,
		'delete'	=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'size'=>'Pallet/PalletSize',
		'height'=>'Pallet/PalletStack',
		'pallet'=>'Pallet/Pallet'
	];
	
	function index()
	{
		$status=$this->model_Settings->get('pallets.pallet_types',TRUE);
		$filters=['access'=>'@loged_user'];
		if ($this->request->getGet('filtered')==null)
		{
			$filters=['status >'=>-1,'access'=>'@loged_user'];
		}else
		{
			$filters['status >']=-1;
		}
		$status=$this->model_Settings->get('orders.orders_*');
		$newbtn=lang('system.orders.order_type_list');
		return $this->setTableView('Pallets/index')
			 ->setData('pallet',null,FALSE,null,$filters)
			 ->setPageTitle('system.pallets.index_page')
			 ->addFilters('index')
			 ->addFilterField('reference %')
			 //->addFilterField('status',$status['-2'],$status['-2'])
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')])
			 
			 ->addColumn('system.pallets.index_reference','reference',TRUE)
			 ->addColumn('system.pallets.index_operator','operator')
			 ->addColumn('system.pallets.index_owner','customer')
			 ->addColumn('system.orders.supplier_ordernr','orderref')
			 ->addColumn('system.pallets.index_status','status',TRUE,$this->model_Settings->get('pallets.pallet_types',TRUE))
			 ->addColumn('system.pallets.index_size','size')
			 ->addColumn('system.pallets.index_stack','stack')
			 ->addColumn('system.pallets.index_location','location')
			 //->addColumn('system.pallets.index_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 //->addColumn('system.pallets.index_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 
			 //->addEditButton('system.pallets.index_info','info',null,'btn-info actBtn','fas fa-info-circle',['data-status'=>'-status-'],AccessLevel::view)
			 ->addEditButton('system.pallets.index_lblbtn','label',null,"btn-secondary",'fas fa-barcode',['target'=>'_blank'],AccessLevel::view)
			 ->addEditButton('system.pallets.index_editbtn','pallet',null,'btn-primary edtBtn','fa fa-edit',['data-status'=>'-status-'],AccessLevel::view)
			 
			 //->addEnableButton()
			 //->addDisableButton()
			 ->addDeleteButton(AccessLevel::view)
			 //->addNewButton('pallet/new',AccessLevel::view)
			 ->addNewButton([$newbtn[1]=>'newjob/customer',$newbtn[0]=>'newjob/supp'],AccessLevel::view)
			 ->addHeaderButton(null,'id_movepallets','button','btn btn-secondary btn-sm putawayPalletBtn','<i class="fas fa-compress-arrows-alt mr-1"></i>',lang('system.pallets.index_movebtn'),AccessLevel::view)
			 ->addHeaderButton(null,'id_scanpallet','button','btn btn-secondary btn-sm btn-scanpallet','<i class="fas fa-barcode mr-1"></i>',lang('system.orders.supplier_scanbtn'),AccessLevel::view)
			 ->addHeaderButton(null,'id_tableview_btn_csv','button','btn btn-info btn-sm ml-2','<i class="fas fa-file-csv mr-2"></i>',lang('system.buttons.exportbtn'),AccessLevel::view)
			 
			 ->addData('new_link',url('Pallets','pallet',['new'],['refurl'=>current_url(FALSE,TRUE)]))
			 ->addData('scan_link',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
			 ->addData('comp',[])
			 ->addData('print_pall',$this->getFlashData('print_pall',null))
			 ->addData('print_pall_url',url($this,'label',['-id-']))
			 ->render();
	}

	function newjob($type)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($type=='customer')
		{
			return redirect()->to(url('Orders','corder',['newjob'],['refurl'=>base64url_encode($refurl)]));
		}else
		{
			return redirect()->to(url('Orders','receipt',['newjob'],['refurl'=>base64url_encode($refurl)]));
		}
	}
	/*pallet_edit palletedit*/
	function pallet($record=null,$sorder=null) 
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl);
		}
		
		$params=$this->model_Settings->get('*');
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		
		if ($record=='new')
		{
			$record=array_combine($this->model_Pallet->allowedFields, array_fill(0, count($this->model_Pallet->allowedFields), ''));
			$record[$this->model_Pallet->primaryKey]='';
			$record['operator']=$this->auth->getLogedUserInfo('username');
			$record['location']=null;
			$record['status']=1;//$params['pallet_type_create'];
			$record['reference']=$this->model_Pallet->generatePalletReference();
			$record['enabled']=1;
			$record['access']=$params['def_access_level'];
			$record['size']=$params['def_pallet_size_edit'];
			if ($this->request->getGet('sorder')!=null)
			{
				$sorder=json_decode(base64url_decode($this->request->getGet('sorder')),TRUE);
				
				$record['supplier']=$sorder['owner'];
				$record['sorder']=$sorder['reference'];
				$record['location']=$sorder['location'];
			}
			$isnew=TRUE;
		}else
		{
			$isnew=FALSE;
			$_record=$this->model_Pallet->find($record);
			if (!is_array($_record))
			{
				$record=$this->model_Pallet->filtered(['reference'=>$record])->first();
			}else
			{
				$record=$_record;
			}
		}
		
		$record=$this->getFlashData('_postdata',$record);
		//$sorder=$this->model_Warehouse_Orders->filtered(['reference'=>$record['sorder']])->first();
		//$corder=$this->model_Warehouse_Orders->filtered(['reference'=>$record['corder']])->first();
		
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.pallet_id_error','danger'));
		}
		
		if (array_key_exists('status', $record) && (in_array($record['status'], Arr::fromFlatten($params['pallet_readonly_status']))))
		{
			return $this->info($record);
		}
		
		if (array_key_exists('status', $record) && $record['status'] < 0)
		{
			return $this->info($record,$refurl);
		}
		
		$content_table='';
		if ($this->model_Settings->get('pallets.showcontentedit'))
		{
			$content_table=['headers'=>['system.pallets.pallet_products_name'],'list'=>$this->model_Warehouse_Alocation->where('pallet',$record['reference'])->find()];
		}
		
		$edit_access=$isnew || $this->auth->hasAccess(AccessLevel::edit) ? 'box':'hidden';
		$edit_access_cust=$record['status'] ==0 || $record['status'] ==1? 'box':'hidden';
		
		$statuslist=$this->model_Settings->getPalletStatusForForm($edit_access!='' ? -1 : -99);
		if (array_key_exists($record['status'], $statuslist))
		{
			$record['statusname']=$statuslist[$record['status']];
		}
		return $this->setFormView('Pallets/pallet')
					->setFormTitle('system.pallets.pallet_page')
					->setPageTitle('system.pallets.pallet_page')
					->setFormAction($this,'save',['pallet'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['pid'=>$record['pid'],'old_status'=>$record['status'],'refurl_ok'=>$refurl])
					->addData('isnew',$isnew)
					->addData('apiurl',url('owners','api',['nextreference']))
					->addData('edit_access',$edit_access!='hidden')
					->addData('movements',loadModule('Movements','mevementsbyref',
									[
										$record['reference'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto','mhref','mhinfo'
										],
									]			
							))
						
					->addBreadcrumb('system.pallets.mainemnenu_pallets',url($this))
					->addBreadcrumb($record['reference'],'/')
					
					->addData('products_list',[])
					->addData('content_table',$content_table,TRUE)
					->addData('orders_inpick',$this->model_Warehouse_Orders->getOrdersWithStatus($this->model_Settings->get('orders.orders_status_pick')))
					->AddData('record',$isnew ? null :$record)
					->setFieldAccessRule('pallet')
					
					->addInputField('system.pallets.pallet_reference','reference',$record['reference'],$isnew ? ['required'=>'true','maxlength'=>150] : ['required'=>'true','maxlength'=>150, 'readonly'=>'1'])
					
					//->addDropDownField('system.pallets.pallet_customer','customer',$this->model_Owners_Customer->getForForm('code','name'),$record['customer'],['required'=>'true','data-mode'=>$edit_access_cust])
					->addInputListField('system.pallets.pallet_customer','customer',$record['customer'],$this->model_Owners_Customer->getForForm('code','code',TRUE),$edit_access_cust=='box' ? ['validation'=>$isnew ? FALSE : TRUE]:['readonly'=>'true'])
					->addInputListField('system.pallets.pallet_orderreford','corder',$record['corder'],$this->model_Warehouse_Orders->getCOrdersForForm(),$edit_access_cust=='box' ? ['validation'=>$isnew ? FALSE : TRUE]:['readonly'=>'true'])
					
					->addInputField('XSNumber','xsnumber',$record['xsnumber'],[])//only for apd
					
					->addDropDownField('system.pallets.pallet_supplier','supplier',$this->model_Owners_Supplier->getForForm('code','name',TRUE),$record['supplier'],['required'=>'true','data-mode'=>$edit_access])
					->addInputListField('system.pallets.pallet_orderrefrec','sorder',$record['sorder'],$this->model_Warehouse_Orders->getReceiptsForForm(),['validation'=>$isnew ? FALSE : TRUE,'data-mode'=>$edit_access])
					
					
					
					->addInputListField('system.pallets.pallet_location','location',$record['location'],$this->model_Warehouse_Location->getForForm('code','code'),[])
					->addAcccessField('system.pallets.pallet_access',$record['access'],'access',[],['data-mode'=>$edit_access])
					->addDropDownField('system.pallets.index_status','status',$statuslist,strval($record['status']),[])
					->addDropDownField('system.pallets.pallet_size','size',$this->model_Size->getForForm('name','name'),$record['size'],['required'=>'true'])
					->addDropDownField('system.pallets.pallet_stack','stack',$this->model_Height->getForForm('code','name'),$record['stack'],['required'=>'true'])
					->addNumberField('system.pallets.pallet_height',$record['height'],'height',3000,10,['required'=>'true'])
					//->addYesNoField('system.pallets.pallet_enabled',$record['enabled'],'enabled',['required'=>'true','data-mode'=>$edit_access])
					->addInputField('system.pallets.suppalref','suppalref',$record['suppalref'],['maxlength'=>150])
					->addCustomFields($this->model_Settings_CustomFields->getFields('pallet',$record['pid']))
					->addHiddenField('operator',$record['operator'])
					
					->addHiddenField('oldref',$record['reference'],['id'=>'oldref'])
					->addHiddenField('ordref_cust',$record['corder'],['id'=>'id_ordref_cust'])
					//->addHiddenField('status',$record['status'])  
					
					->render();
	}
	
	public function info($record,$refurl=null)
	{
		$refurl=$refurl==null ? $this->request->getGet('refurl') : $refurl;
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if (!is_array($record))
		{
			$record=$this->model_Pallet->find($record);
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.pallet_id_error','danger'));
		}
		
		$sizes=$this->model_Size->getForForm('name','name');
		
		$statuses=$this->model_Settings->getPalletStatusForForm(-99);
		$stacks=$this->model_Height->getForForm('code','name');
		$order_type = $record['status'] > 0 ? 'corder' :'sorder';
		$options=[lang('system.general.no'),lang('system.general.yes')];
		$edit_access=$this->auth->hasAccess(AccessLevel::edit) ? 'box':'hidden';
		$edit_access_cust=$record['status'] ==0 || $record['status'] ==1? 'box':'hidden';
		
		return $this->setFormView('Pallets/pallet')
					->setFormTitle('system.pallets.pallet_page')
					->setPageTitle('system.pallets.pallet_page')
					->setFormCancelUrl($refurl)
					->setFormArgs([],['pid'=>$record['pid'],'refurl_ok'=>$refurl])
					->addInputField('system.pallets.pallet_reference','reference',$record['reference'],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_location','location',$record['location'],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.index_status','status',$statuses[$record['status']],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_size','size',array_key_exists($record['size'], $sizes) ? $sizes[$record['size']] : null,['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_stack','size',$stacks[$record['stack']],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_height','height',$record['height'],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_orderref','height',$record[$order_type],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.pallets.pallet_enabled','enabled',$options[$record['enabled']],['readonly'=>'true','class'=>'bg-light'])
					->addCustomFields($this->model_Settings_CustomFields->getFields('pallet',$record['pid']),TRUE)
					
					->addBreadcrumb('system.pallets.mainemnenu_pallets',url($this))
					->addBreadcrumb($record['reference'],'/')
					
					->addData('record',$record)
					->addData('edit_access',$edit_access)
					->addData('isnew',FALSE)
					->addData('movements',loadModule('Movements','mevementsbyref',
									[
										$record['reference'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto','mhref','mhinfo'
										],
									]			
							))
					->render();
	}
	
	function putaway($type=1)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		
		$fields=$this->model_Pallet->getPalletsForPutaway(['tasks.type'=>$type]);
		
		if ($this->isMobile() && count($fields)>0)
		{
			$keys=array_keys($fields);
			$fields=array_values($fields);
			$fields=$fields[0];
			$fields['reference']=$keys[0];
		}
		
		return $this->setFormView($this->isMobile() ? 'Pallets/move_mobile' : 'Pallets/move')
					->setFormTitle('')
					->setPageTitle('system.pallets.move_page')
					->setFormAction($this,'save',['move'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['refurl_ok'=>$refurl])
					
					->addBreadcrumb('system.pallets.mainmenu_putaway','/')
					
					->addData('move_fields',$fields)
					->addData('pallets',$this->model_Pallet->getPalletsForPutaway())
					->addData('locations',$this->model_Warehouse_Location->getForForm('code','code'))
					->addData('putaway',1)
					->render();	
	}
	
	function move($pallets=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=$this->request->getPost();
		
		$fields=[['location'=>'','putaway'=>'','sorder'=>'','pid'=>'']];
		if (array_key_exists('pid', $post) && is_array($post['pid']))
		{
			$fields=$this->model_Pallet->getForMoveForm($post['pid']);
		}
		
		$pallets=is_array($pallets) ? $pallets : $this->model_Pallet->getForMoveForm();
		if ($this->isMobile())
		{
			$fields=$fields[0];
		}
		
		return $this->setFormView($this->isMobile() ? 'Pallets/move_custom_mobile': 'Pallets/move')
					->setFormTitle('')
					->setPageTitle('system.pallets.move_page')
					->setFormAction($this,'save',['task'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['refurl_ok'=>current_url()])
					->addData('move_fields',$fields)
					->addData('pallets',$pallets)
					->addData('locations',$this->model_Warehouse_Location->getForForm('code','code'))
					->addData('automove',TRUE)
					->render();		
	}
	
	function label($record) 
	{
		if ($record==null)
		{
			return redirect()->to(url($this));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$record=$this->model_Pallet->findForLabel($record);//find($record);
		
		if (strlen($record['corder'])<1)
		{
			$record['maxpages']=$this->model_Pallet->count(['sorder'=>$record['sorder']]);
			$pallet_label='orders.orders_pall_labelrec';
		}else
		{
			$pallet_label='system.pallet_label';
		}
		
		$record['page']=$record['order'];
		$pallet_label=$this->model_Settings->get($pallet_label);
		
		if (array_key_exists('status', $record))
		{
			$status=$this->model_Settings->get('pallets.pallet_types',TRUE);
			if (array_key_exists($record['status'], $status))
			{
				$record['status']=$status[$record['status']];
			}
			
		}
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.pallet_id_error','danger'));
		}
		$record['template']=$pallet_label;
		
		$this->addMovementHistory(MovementType::labels,null,null,$record['reference'],lang('system.pallets.print_single_pall'),'pallet');
		return loadModule('Documents','template',[$record]);
		return redirect()->to(url('documents','template',$pallet_label,['data'=>base64url_encode(json_encode($record))]));
	}
	
	function heights() 
	{
		$this->setTableView()
			 ->setData('height',null,TRUE)
			 ->setPageTitle('system.pallets.stacks_page')
			 ->addFilters('heights')
			 ->addFilterField('code %')
			 ->addColumn('system.pallets.stack_code','code',TRUE)
			 ->addColumn('system.pallets.stack_height','height')
			 ->addColumn('system.pallets.stack_desc','desc',FALSE,[],'len:30')
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.pallets.stack_editbtn','height',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('height/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function height($record=null) 
	{
		if ($record==null)
		{
			return redirect()->to(url($this,'heights'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Height->allowedFields, array_fill(0, count($this->model_Height->allowedFields), ''));
			$record[$this->model_Height->primaryKey]='';
		}else
		{
			$record=$this->model_Height->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'heights'))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		return $this->setFormView()
					->setFormTitle('system.pallets.stack_title',[$record['code']])
					->setPageTitle('system.pallets.stack_page')
					->setFormAction($this,'save',['height'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,'sizes')
					->setFormArgs([],['ptid'=>$record['ptid'],'refurl_ok'=>url($this,'height')])
					
					->addInputField('system.pallets.stack_code','code',$record['code'],['required'=>'true','maxlength'=>50,'placeholder'=>lang('system.pallets.stack_newitem')])
					//->addNumberField('system.pallets.stack_height',$record['height'],'height',3000,10,['required'=>'true'])
					->addYesNoField('system.pallets.size_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addTextAreaField('system.pallets.size_desc','desc',$record['desc'],[])
					->render();
	}
		
	function sizes() 
	{
		$this->setTableView()
			 ->setData('size',null,TRUE)
			 ->setPageTitle('system.pallets.sizes_page')
			 ->addFilters('sizes')
			 ->addFilterField('name %')
			 ->addColumn('system.pallets.size_name','name',TRUE)
			 ->addColumn('system.pallets.size_width','width')
			 ->addColumn('system.pallets.size_length','length')
			 ->addColumn('system.pallets.size_type','type',FALSE,$this->model_Settings->get('pallets.pallet_sizetypes',TRUE))
			 ->addColumn('system.pallets.size_desc','desc',FALSE,[],'len:30')
			 ->addColumn('system.general.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])//$model,$filters,$orderBy=null,$pagination=FALSE
			 ->addEditButton('system.pallets.size_editbtn','size',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('size/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function size($record=null)
	{
		if ($record==null)
		{
			return redirect()->to(url($this,'sizes'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Size->allowedFields, array_fill(0, count($this->model_Size->allowedFields), ''));
			$record[$this->model_Size->primaryKey]='';
		}else
		{
			$record=$this->model_Size->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'sizes'))->with('error',$this->createMessage('system.pallets.size_id_error','danger'));
		}
		return $this->setFormView()
					->setFormTitle('system.pallets.size_title',[$record['name']])
					->setPageTitle('system.pallets.size_page')
					->setFormAction($this,'save',['size'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,'sizes')
					->setFormArgs([],['psid'=>$record['psid'],'refurl_ok'=>url($this,'sizes')])
					
					->addInputField('system.pallets.size_name','name',$record['name'],['required'=>'true','maxlength'=>50,'placeholder'=>lang('system.pallets.size_newitem')])
					->addNumberField('system.pallets.size_width',$record['width'],'width',2000,10,['required'=>'true'])
					->addNumberField('system.pallets.size_length',$record['length'],'length',2000,10,['required'=>'true'])
					->addYesNoField('system.pallets.size_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addDropDownField('system.pallets.size_type','type',$this->model_Settings->get('pallets.pallet_sizetypes',TRUE),$record['type'],[])
					->addTextAreaField('system.pallets.size_desc','desc',$record['desc'],[])
					->addCustomFields($this->model_Settings_CustomFields->getFields('pallet_size',$record['psid']))
					->render();
	}
	public function deletepallet($record)
	{
		return $this->delete(['model'=>'pallet','pid'=>[$record]]);	
	}
	
	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=count($post)>0 ? $post : $this->request->getPost();
		
		if (array_key_exists('model', $post) && $post['model']=='pallet')
		{
			if (!array_key_exists('pid', $post))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
			}
			if ($this->model_Pallet->deletePallets($post,loged_user('username')))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_delete_ok','success'));
			}
		}else
		{
			return parent::delete($post);
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
	}
	
	function save($type,$post=null)
	{
		$post=$this->request->getPost();
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$print_pall=null;
		
		if ($type=='task')
		{
			if (array_key_exists('automove', $post) && $post['automove']==1)
			{
				goto save_move_pallets;
			}
			if (array_key_exists('pallets', $post) && array_key_exists('pids', $post['pallets']))
			{
				foreach ($post['pallets'] as $key => $value) 
				{
					if (array_key_exists('location', $value))
					{
						if (!$this->model_Tasks_Task->addNewPalletMovement($post['pallets']['pids'][$key],$value))
						{
							return redirect()->to($refurl)->with('error',$this->createMessage($this->model_Tasks_Task->errors(),'danger'));
						}
					}
				}
			}
			return redirect()->to($post['refurl_ok'])->with('error',$this->createMessage('system.general.msg_save_ok','success'));
		}else
		if ($type=='move')
		{
			save_move_pallets:
			if (array_key_exists('pallets', $post))
			{
				foreach ($post['pallets'] as $key => $value) 
				{
					if (is_numeric($key))
					{
						if (!$this->model_Pallet->set('location',$value['location'])->set('putaway','')->where('reference',$value['reference'])->update())
						{
							return redirect()->to($refurl)->with('error',$this->createMessage($this->model_Pallet->errors(),'danger'));
						}else
						{
							$this->model_Tasks_Task->set('status',1)->set('done',formatDate())->where('tref',$value['reference'])->delete();
							$this->addMovementHistory('locations_moveout',null,null,$value['old_location'],$value['reference'],'locations');
							$this->addMovementHistory('locations_movein',null,null,$value['location'],$value['reference'],'locations');
							$this->addMovementHistory(strlen($value['putaway'])>0 ? MovementType::putaway : MovementType::move,$value['old_location'],$value['location'],$value['reference'],array_key_exists('info', $value) ? $value['info'] : null,'pallet');
						}
					}
				}
				if (array_key_exists('pids', $post['pallets']) && is_array($post['pallets']['pids']))
				{
					$this->model_Tasks_Task->completeTasks($post['pallets']['pids']);
				}
				
			}
		}else
		if ($type=='pallet')
		{
			$old_status=null;
			if (array_key_exists('pid', $post) && !is_numeric($post['pid']))
			{
				unset($post['pid']);
			}else
			{
				$old_status=$this->model_Pallet->find($post['pid']);
			}
			
			if (Arr::KeysExists(['old_status','status'],$post) && intval($post['status'])!=intval($post['old_status']) && intval($post['status'])==1)
			{
				$post['operator']=loged_user('username');
			}
			
			if (!$this->model_Pallet->save($post))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage($this->model_Pallet->errors(),'danger'));
			}else
			{
				$this->addMovementsFromArray($post);
				
				if (array_key_exists('corder', $post) && strlen($post['corder']))
				{
					if ($this->model_Warehouse_Orders->count(['reference'=>$post['corder']])==0)
					{
						$this->model_Warehouse_Orders->createCOrderFromPallet($post);
					}
				}
				
				if (is_array($old_status) && array_key_exists('status', $old_status) && array_key_exists('status', $post) && $old_status['status']!=$post['status'])
				{
					$this->addMovementHistory(MovementType::status,$old_status['status'],$post['status'],$post['reference'],null,'pallet');
				}
				
				if (\CELLA\Helpers\Arrays::KeysExists(['reference_cust','reference'],$post) && strcmp($post['reference'],$post['reference_cust'])===0)
				{
					$this->model_Owners_Customer->setNewReference($post['customer'],$post['reference']);
				}
				  
				if (\CELLA\Helpers\Arrays::KeysExists(['ordref_cust','corder'],$post) && strcmp($post['corder'],$post['ordref_cust'])===0)
				{
					$this->model_Owners_Customer->setNewReference($post['customer'],$post['corder'],1);
				}
				
				if (array_key_exists('customfields', $post) && is_array($post['customfields']))
				{
					$model=$this->model_Pallet;
					foreach ($post['customfields'] as $value) 
					{
						if (array_key_exists($model->primaryKey, $post))
						{
							$value['targetid']=$post[$model->primaryKey];
						}else
						{
							$value['targetid']=$model->db->insertID();
						}
						$this->model_Settings_CustomFields->save($value);
					}
				}
			
				
				$this->model_Warehouse_Alocation->where('pallet',$post['reference'])->delete();
				if (array_key_exists('stock', $post))
				{
					foreach ($post['stock'] as $value) 
					{
						$value['corder']=array_key_exists('corder', $post) ? $post['corder']:'';
						$value['sorder']=array_key_exists('sorder', $post) ? $post['sorder']:'';
						$value['pallet']=$post['reference'];
						$this->model_Warehouse_Alocation->save($value);
					}
				}
				if (!array_key_exists('pid', $post))
				{
					$this->addMovementHistory(MovementType::create,'',$post['location'],$post['reference'],null,'pallet');
				}
				
				$this->model_Tasks_Rule->actionRuleByTrigger('Pallet_save',[$post]);
				if (array_key_exists('pid', $post))
				{
					$print_pall=$post['pid'];
				}else
				{
					$print_pall=$this->model_Pallet->getLastID();
				}
			}
		}else
		{
			return parent::save($type,$post);
		}
		return redirect()->to($post['refurl_ok'])->with('error',$this->createMessage('system.general.msg_save_ok','success'))->with('print_pall',$print_pall);
	}

	
	
}