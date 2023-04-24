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

class Orders extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'receipts'=>AccessLevel::view,
		'receipt'=>AccessLevel::edit,
		'order'=>AccessLevel::view,
		'corder'=>AccessLevel::view,
		'save'=>AccessLevel::view,
		'delete'=>AccessLevel::settings,
		'predelivery'=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'orders'=>'Warehouse/Orders',
		'pallet'=>'Pallet/Pallet'
	];
	
	/**
	 * Array with controller method remaps ($key is fake function name and $value is actuall function name)
	 */
	public $remaps=
	[
		'setpriority'=>['Scheduler','linkjobs'],
		'rempriority'=>['Scheduler','delinkjobs'],
		'predelivery'=>['Scheduler','predelivery'],
		'newsuppjob'=>['Scheduler','predelivery',['new']],
		'customers'=>['index',['1']],
		'receipts'=>['index',['0']],
		
	];
	
	function index($type=null)
	{
		$status=$this->model_Settings->get('orders.orders_*');
		$filters=['enabled'=>1,'status <>'=>-2];//['access'=>'@loged_user'];//,'type %'=>$types 
		if (is_numeric($type))
		{
			$filters['type']=$type;
		}	 				
		$status['orders_status_types']=Arr::fromFlatten($status['orders_status_types']);
		$orders_status_types=$status['orders_status_types'];
		if (array_key_exists($type, $status['orders_status_types']))
		{
			$status['orders_status_types']=Arr::fromFlatten($status[$status['orders_status_types'][$type]]);
		}else
		{
			$status['orders_status_types']=$this->model_Orders->getAllOrdersStatusValues();
		}
		
		$this->setTableView('Warehouse/orders_list')
			 ->setData('orders',['status'],FALSE,null,$filters)
			 ->addData('_tableview_custom',FALSE)
			 ->addData('orderstatus',$this->model_Orders->getOrdersWithStatus($status['orders_status_full'],'oid'))
			 ->addData('completed',$this->model_Orders->getOrdersWithStatus($status['orders_status_comp'],'oid'))
			 ->addData('received',$this->model_Orders->getOrdersWithStatus($status['orders_status_recok'],'oid'))
			 ->addData('inpick',$this->model_Orders->getOrdersWithStatus($status['orders_status_pick'],'oid'))
			 ->addData('orders',$this->model_Orders->getAllOrdersForEachStatus())
			 ->addData('order_statuses',$status)
			 ->setPageTitle(array_key_exists($type, $orders_status_types) ? ('system.orders.'.$orders_status_types[$type]):'system.orders.orders_page')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')])
			 ->addFilters($type==0 && is_numeric($type)? 'receipts' : 'index')
			 ->addFilterField('reference %')
			 ->addFilterField('owner %');
			
		
			 
		foreach ($status['orders_status_types'] as $key=>$value) 
		{
			if (($type==0 || ($type!=0 && $key!=$status['orders_status_rec'])) && $key > -1)
			{
				$this->view->addFilterField('status',$value,$value);
			}
		}
		
		$newbtn=lang('system.orders.order_type_list');
		if ($type==0)
		{
			$newbtn=$status['orders_newreccomm'];
		}else
		if ($type==1)
		{
			$newbtn='corder/newjob';
		}else
		{
			$newbtn=[$newbtn[1]=>'corder/newjob',$newbtn[0]=>$status['orders_newreccomm']];
		}
		if ($type==1)
		{
			$this->view->addColumn('scheduler.orders.inboard_col','inboard',FALSE,'yesno');
		}
		$this->view->addFilterField('status',' ','Clear')
				   ->addColumn('system.orders.supplier_ordernr','reference',TRUE)
			 	   ->addColumn('system.orders.orders_owner','owner')
				   ->addColumn('system.orders.order_type','type',TRUE,lang('system.orders.order_type_list'))
			 	   //->addColumn('system.orders.supplier_location','location')
			       ->addColumn('system.orders.supplier_status','status',FALSE,$status['orders_status_types'])
			       ->addColumn('system.orders.supplier_palletsqty_index','palletsqty',FALSE,[],'prg:/')
			       //->addColumn('system.orders.supplier_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 	   //->addColumn('system.orders.supplier_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
				   
				   //->addBreadcrumb($type==1 ? 'Orders' : 'Receipts','/')
				   ->addBreadcrumb('system.orders.index_bread',url($this))
				   
				   //->addEditButton('system.orders.suppliers_ordpick','bookout',null,'btn-dark actBtn','fas fa-people-carry',['data-status'=>'orders_status_pick','data-orderid'=>'-reference-'],AccessLevel::state)
				   //->addEditButton('system.orders.suppliers_ordrec','received',null,'btn-warning text-white actBtn recBtn','fas fa-receipt',['data-status'=>'orders_status_full'],AccessLevel::state)
			 	   //->addEditButton('system.orders.suppliers_ordcomp','bookout',null,'btn-success text-white actBtn compBtn','far fa-check-circle',['data-status'=>'orders_status_pick'],AccessLevel::state)
				   //->addEditButton('system.orders.suppliers_info','receipt',null,'btn-info text-white actBtn viewBtn','fas fa-info-circle',['data-status'=>'orders_status_comp','data-orderid'=>'-reference-'],AccessLevel::view)
			 	   //->addEditButton('system.orders.suppliers_bookin','bookin',null,'btn-dark text-white actBtn bookinBtn','fab fa-dropbox',['data-status'=>'orders_status_rec','data-orderid'=>'-reference-'],AccessLevel::state)
				   ->addEditButton('system.orders.suppliers_palletlabels','palletlabels/'.($type==0 ? 'receipt':'order'),null,'btn-secondary ','fas fa-barcode',['target'=>'_blank'],AccessLevel::view)
			   	   ->addEditButton('system.orders.supplier_editbtn','order',null,'btn-primary edtBtn','fa fa-edit',['data-orderid'=>'-reference-','data-status'=>'-status-'],AccessLevel::view)
			 	   
			 	   //->addEnableButton()
			 	   //->addDisableButton()
			       ->addDeleteButton()
			       ->addNewButton($newbtn,AccessLevel::view)
				   ->addHeaderButton(null,'id_scanpallet','button','ml-2 btn btn-secondary btn-sm btn-scanpallet','<i class="fas fa-barcode mr-1"></i>',lang('system.orders.supplier_scanbtn'),AccessLevel::view)
			 	   //->addHeaderButton('bookout','id_dimm_orders','button','btn btn-success btn-sm tableview_def_btns','<i class="fas fa-clipboard-check mr-1"></i>',lang('system.orders.supplier_compbtn'),AccessLevel::view)
			 	   ->addData('scan_link',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
			 	   ->addData('new_link',url('Pallets','pallet',['new'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('print_pall',$this->getFlashData('print_pall',null))
			 	   ->addData('print_pall_url',url($this,'palletlabels',['receipt','-id-'])); 
		if ($type==1)
		{
			$this->view->addHeaderButton('setpriority','id_prioritybtn','button','btn btn-success btn-sm tableview_def_btns prioritybtn ml-2','<i class="far fa-calendar-plus mr-1"></i>',lang('scheduler.orders.orderbtn'),AccessLevel::delete)
					   ;//->addHeaderButton('rempriority','id_delprioritybtn','button','btn btn-danger btn-sm tableview_def_btns delprioritybtn ml-2','<i class="far fa-calendar-minus mr-1"></i>',lang('scheduler.orders.delbtn'),AccessLevel::delete);;
		}	 
		return $this->view->render();
	}
	
	
	function dispatched($type=1)
	{
		$status=$this->model_Settings->get('orders.orders_*');
		$filters=['enabled'=>1,'status'=>-2];//['access'=>'@loged_user'];//,'type %'=>$type
		
				
		$status['orders_status_types']=$this->model_Orders->getAllOrdersStatusValues();
	
		$this->setTableView('Warehouse/orders_list')
			 ->setData('orders',['status'],FALSE,null,$filters)
			 ->addData('_tableview_custom',FALSE)
			 ->addData('orderstatus',$this->model_Orders->getOrdersWithStatus($status['orders_status_full'],'oid'))
			 ->addData('completed',$this->model_Orders->getOrdersWithStatus($status['orders_status_comp'],'oid'))
			 ->addData('received',$this->model_Orders->getOrdersWithStatus($status['orders_status_recok'],'oid'))
			 ->addData('inpick',$this->model_Orders->getOrdersWithStatus($status['orders_status_pick'],'oid'))
			 ->addData('orders',$this->model_Orders->getAllOrdersForEachStatus())
			 ->addData('order_statuses',$status)
			 ->setPageTitle('system.orders.mainmenu_disp')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')])
			 ->addFilters('dispatched');
			 
		$newbtn=lang('system.orders.order_type_list');
		$this->view->addColumn('system.orders.supplier_ordernr','reference',TRUE)
			 	   ->addColumn('system.orders.orders_owner','owner')
			       ->addColumn('system.orders.orders_created','created',FALSE,[],'d M Y')
				   ->addColumn('system.orders.orders_comp','completed',FALSE,[],'d M Y')
				   ->addColumn('system.orders.supplier_palletsqty','palletsdisp')
				   
				   //->addBreadcrumb($type==1 ? 'Orders' : 'Receipts','/') edtBtn
				   ->addBreadcrumb('system.orders.mainmenu_disp',url($this))
				   
				   ->addEditButton('system.orders.suppliers_info','completed',null,'btn-info viewBtn','fas fa-info-circle',[],AccessLevel::view)
			       ->addData('scan_link',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
			 	   ->addData('new_link',url('Pallets','pallet',['new'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('print_pall',$this->getFlashData('print_pall',null))
			 	   ->addData('print_pall_url',url($this,'palletlabels',['receipt','-id-'])); 
			 
		return $this->view->render();
	}
	
	
	function receipt($record=null)
	{
		if ($record=='newjob')
		{
			return $this->newjob();	
		}
		return $this->order($record,0);
	}
	
	
	
	function corder($record=null)
	{
		if ($record=='newjob')
		{
			return $this->newcjob();	
		}
		return $this->order($record,1);
	}
	
	/*order_edit edit_order editorder orderedit*/
	function order($record=null,$type=1)
	{
		
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to(url($this,'receipts'));
		}

		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		
		if ($record=='new')
		{
			$record=array_combine($this->model_Orders->allowedFields, array_fill(0, count($this->model_Orders->allowedFields), ''));
			$record[$this->model_Orders->primaryKey]='';
		}else
		{
			$record=$this->model_Orders->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		$type=$record['type'];
		$status=$this->model_Settings->get('orders.orders_status_*');
		
		if (!array_key_exists($this->model_Orders->primaryKey, $record))
		{
			$record[$this->model_Orders->primaryKey]='';
			$record['operator']=$this->auth->getLogedUserInfo('username');
			$record['location']=null;
			$record['status']=$status['orders_status_def'];
		}

		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'receipts'))->with('error',$this->createMessage('system.owners.customer_id_error','danger'));
		}
		
		$content_table='';
		if ($this->model_Settings->get('pallets.showcontentedit'))
		{
			$content_table=['headers'=>['system.pallets.pallet_products_name'],'list'=>$this->model_Warehouse_Alocation->where('corder',$record['reference'])->orWhere('sorder',$record['reference'])->find()];
		}
		$ro=!$this->auth->hasAccess(AccessLevel::view) || ($record['status']==$status['orders_status_full'] || $record['status']==$status['orders_status_comp'] );
		$co=$ro;//$record['status']==$status['orders_status_comp'];
		
		$zone=$this->model_Settings->get($type==0 ? 'locations.goods_in_zone' : 'locations.goods_out_zone');
		
		$this->setFormView('Warehouse/receipt');
		$content_stacking=FALSE;
		if ($record['type']==1)
		{
			$content_stacking=$this->getDimmsSheet($record);
		}
		
		if ($this->isMobile())
		{
			return $this->info($record,$refurl);
		}
		$statuses_list=$this->model_Orders->getOrderStatusList($record['type'],$status,'0.99');
		if (array_key_exists($record['status'], $statuses_list))
		{
			$record['status_name']=$statuses_list[$record['status']];
		}
		
		return $this->view
					->setFormTitle($type==0 ? 'system.orders.suppliers_page_receipt' : 'system.orders.customer_page_order')
					->setPageTitle($type==0 ? 'system.orders.suppliers_page_receipt' : 'system.orders.customer_page_order')
					->setFormAction($this,'save',['orders'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl,null)
					->setFormArgs([],['refurl_ok'=>current_url()])
					->addData('receiptref',1)//$this->model_Owners_Supplier->getNextReference())
					->addData('products_list',[])
					->addData('_pallets_list_columns',!$ro ? [] : ['reference','size','stack','height','status'])
					->addData('content_table',$content_table,TRUE)
					->addData('content_stacking',$content_stacking)
					->addData('save_dis',$record['status']==-2 || $record['status']==-1)
					->addData('pallets',$this->model_Pallet_Pallet->filtered(['( sorder'=>$record['reference'],'|| corder )'=>$record['reference'],'status > '=>'-1'])->find())
					->addData('pallets_types',$this->model_Settings->get('pallets.pallet_types',TRUE))
					->addData('pallets_status_notcomp',$this->model_Pallet_Pallet->getQtyOfPalletsFromOrder($record['reference'],['status <>'=>1,'status >'=>-1,'status <'=>100],$type))
					->addData('pallets_status_comp',$this->model_Pallet_Pallet->getQtyOfPalletsFromOrder($record['reference'],['status'=>1,'|| status'=>100],$type))
					->addData('pallets_status_all',$this->model_Pallet_Pallet->getQtyOfPalletsFromOrder($record['reference'],['status >'=>-1],$type))
					->addData('movements',$record[$this->model_Orders->primaryKey]=='' ? null :loadModule('Movements','mevementsbyref',
									[
										[$record['reference'],'orders'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto'
											,'mhref'=>['value'=>'mhref','format'=>'rep:_'.$record['type'].':']
											,'mhinfo'
										],
									]			
							))
					
					->addBreadcrumb('system.orders.index_bread',url($this))
					->addBreadcrumb($record['reference'],current_url())
					
					->addDropDownField('system.orders.orders_owner','owner',$type==0 ? $this->model_Owners_Supplier->getForForm('code','name') : $this->model_Owners_Customer->getForForm('code','name'),$record['owner'],$co || $ro ? ['disabled'=>'true','class'=>'bg-light'] : ['required'=>'true'])
					->addInputField('system.orders.supplier_ordernr','reference',$record['reference'],$co || $ro  ? ['readonly'=>'true','class'=>'bg-light'] : ['required'=>'true','maxlength'=>120])
					//->addInputListField('system.orders.supplier_location','location',$record['location'],$this->model_Warehouse_Location->getForForm('code','code',FALSE,null,['zone'=>$zone]),$co ? ['readonly'=>'true','class'=>'bg-light'] : [])
					->addDropDownField('system.orders.supplier_status','status',$statuses_list,$record['status'],$co ? ['disabled'=>'true','class'=>'bg-light'] :[])
					//->addYesNoField('system.orders.supplier_enabled',$record['enabled'],'enabled',$co ? ['disabled'=>'true','class'=>'bg-light'] :['required'=>'true'])
					//->addAcccessField('system.orders.supplier_access',$record['access'],'access',[],$co ? ['disabled'=>'true','class'=>'bg-light'] : [])
					
					->addHiddenField('created',$record['created'])
					->addHiddenField('oid',$record['oid'],['id'=>'id_oid'])
					->addHiddenField('type',$type)
					->addHiddenField('operator',$record['operator'])
					->addHiddenField('reference_old',$record['reference'])
					->addHiddenField('ocfg',$record['ocfg'],['id'=>'id_ocfg'])
					->addCustomFields($this->model_Settings_CustomFields->getFields('orders',$record['oid']))
					->addDataTableScript()
					->addData('scan_link',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
					->addData('pdfBtn',url('Documents','template',['order_stacking'],['id'=>$record['oid'],'refurl'=>current_url(FALSE,TRUE)]))
					->addData('print_pall',$this->getFlashData('print_pall',null))
			 	   	->addData('print_pall_ord_url',url($this,'palletlabels',['receipt','-id-']))
					->addData('print_pall_url',url('Pallets','label',['-id-']))
					->addData('record',$record)
					->render();
	}

	public function completed($record=null,$type=1)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl);
		}

		$record=$this->model_Orders->find($record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'receipts'))->with('error',$this->createMessage('system.owners.customer_id_error','danger'));
		}
		
		$content_stacking=FALSE;
		if ($record['type']==1)
		{
			$content_stacking=$this->getDimmsSheet($record,TRUE);
		}
		
		if ($this->isMobile())
		{
			return $this->info($record,$refurl);
		}
		$status=$this->model_Settings->get('orders.orders_status_*');
		return $this->setFormView('Warehouse/receipt')
					->setFormTitle('system.orders.orders_comppage')
					->setFormCancelUrl($refurl,null)
					->addData('receiptref',1)
					->addData('products_list',[])
					
					->addData('content_stacking',$content_stacking)
					->addData('save_dis',TRUE)
					->addData('pallets',$this->model_Pallet_Pallet->filtered(['( sorder'=>$record['reference'],'|| corder )'=>$record['reference'],'status'=>'-2'])->find())
					->addData('pallets_types',$this->model_Settings->get('pallets.pallet_types',TRUE))
					->addData('pallets_status_all',$this->model_Pallet_Pallet->getQtyOfPalletsFromOrder($record['reference'],['status'=>-2],$type))
					->addData('movements',$record[$this->model_Orders->primaryKey]=='' ? null :loadModule('Movements','mevementsbyref',
									[
										$record['reference'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto'
											,'mhref'=>['value'=>'mhref','format'=>'rep:_'.$record['type'].':']
											,'mhinfo'
										],
									]			
							))
					
					->addBreadcrumb('system.orders.index_bread',url($this))
					->addBreadcrumb($record['reference'],current_url())
					
					->addDropDownField('system.orders.orders_owner','owner',$type==0 ? $this->model_Owners_Supplier->getForForm('code','name') : $this->model_Owners_Customer->getForForm('code','name'),$record['owner'],['disabled'=>'true','class'=>'bg-light'])
					->addInputField('system.orders.supplier_ordernr','reference',$record['reference'],['readonly'=>'true','class'=>'bg-light'])
					->addDropDownField('system.orders.supplier_status','status',$this->model_Orders->getOrderStatusList($record['type'],$status),$record['status'],['disabled'=>'true','class'=>'bg-light'])
			
					->addDataTableScript()
					->addData('_pallets_list_columns',['reference','size','stack','height','sorder','xsnumber'] )
					->addData('pdfBtn',strlen($record['ocfg']) > 0 ? url('Documents','template',['order_stacking'],['id'=>$record['oid'],'refurl'=>current_url(FALSE,TRUE)]) : false)
					->render();
	}

	public function move($mode='html')
	{
		if (!$this->view->isMobile())
		{
			//return redirect()->to(url($this))->with('error',$this->createMessage('system.errors.onlymobile_option','warning'));
		}
		
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$class='';
		
		$this->setFormView('Warehouse/Orders/move')
					->setFormTitle('system.orders.mobile_move_title')
					->setFormAction($this,'save',['orders'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['refurl_ok'=>$refurl]);
								
		if ($this->view->isMobile())
		{
			$class='form-control-lg';
			$this->view->setFormSaveUrl(['class'=>'btn btn-lg btn-success w-100 mb-3 d-none','text'=>'system.orders.mobile_move','type'=>'button']);
		}else
		{
			$this->view->setFormSaveUrl(['class'=>'btn btn-success d-none','text'=>'system.orders.mobile_move','type'=>'button']);
		}
		return $this->view->setCustomViewEnable(FALSE)
					->addInputListField('system.orders.mobile_ref','reference',null,$this->model_Orders->getOrdersForMoveForm(),['validation'=>false,'class'=>$class])
					->addInputListField('system.orders.supplier_location','location','',$this->model_Warehouse_Location->getForForm('code','code'),['validation'=>false,'class'=>$class,'readonly'=>'true'])
					->addHiddenField('oid',null,['id'=>'id_oid'])
					->addData('orders',array_flip($this->model_Orders->getOrdersForMoveForm()))
					->render($mode);	
	}	
	
	private function getDimmsSheet($record,$dispPallets=FALSE)
	{
		if ($dispPallets)
		{
			$data['readonly']=TRUE;
		}
		$data['data']=$this->model_Orders->getLoadPalletsForDimSheet($record['reference'],$dispPallets ? 'pallets.status=-2' : null);
		
		$data['table']=json_decode($record['ocfg'],TRUE);
		$data['headers']=count($data['data']) > 0 ? array_keys($data['data'][0]) : [];
		$data['stacking_save_url']=url($this,'save',['orders'],['refurl'=>current_url(FALSE,TRUE)]);
		$data['btlist']=['','B','M','N','M2','T'];
		
		return $data;
	}
	
	private function info($record,$refurl)
	{
		$statuses=Arr::fromFlatten($this->model_Settings->get('orders.orders_status_types'));
		$options=[lang('system.general.no'),lang('system.general.yes')];
		$owners=$this->model_Owners_Supplier->getForForm('code','name');
		$content=$this->model_Warehouse_Alocation->where('corder',$record['reference'])->orWhere('sorder',$record['reference'])->find();
		//dump($content);exit;
		return $this->setFormView()
					->setFormTitle('')
					->setPageTitle('system.orders.suppliers_page_receipt')
					->setFormCancelUrl($refurl)
					->setFormArgs([],[],['refurl_ok'=>$refurl])
					->addInputField('system.orders.supplier_ordernr','reference',$record['reference'],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.orders.supplier_location','location',$record['location'],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.orders.supplier_status','status',$statuses[$record['status']],['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.orders.supplier_supplier','owner',$owners[$record['owner']],['readonly'=>'true','class'=>'bg-light'])
					->addTextAreaField('system.orders.receipt_tab_stock','',Arr::implode(';',$content,'code'),['readonly'=>'true','class'=>'bg-light'])
					->addInputField('system.orders.supplier_enabled','enabled',$options[$record['enabled']],['readonly'=>'true','class'=>'bg-light'])
					->render();
	}
	
	function palletlabels($type=null,$record=null,$pallet_label=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if (Str::contains($record,'_'))
		{
			$pallets=$this->model_Pallet_Pallet->findForLabel(['access'=>'@loged_user','reference In'=>explode('_',$record),'enabled'=>1])->find();
			goto pallets_aloc;
		}
		$orecord=$this->model_Orders->where('reference',$record)->orWhere('oid',$record)->first();
		if ($orecord==null)//
       	{
        	$pallets=$this->model_Pallet_Pallet->findForLabel(['access'=>'@loged_user','reference'=>$record,'enabled'=>1])->find();
	        if (is_array($pallets) && count($pallets)>0)
           	{
            	goto pallets_aloc;
         	}
     		$orecord=null;
      	}
		$record=$orecord;
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_invalidordqty','danger'));
		}
		$type=$record['type']==0 ? 'sorder' : 'corder';
			
		$pallets=$this->model_Pallet_Pallet->findForLabel(['access'=>'@loged_user',$type=>$record['reference'],'enabled'=>1])->find();
		if (is_array($pallets) && count($pallets) < 1)
		{
			$pallets=$this->model_Pallet_Pallet->findForLabel(['access'=>'@loged_user','corder'=>$record['reference'],'enabled'=>1])->find();
		}
		
		pallets_aloc:
		$params=$this->model_Settings->get('orders.*');
		$pallet_label=$this->model_Settings->get('system.pallet_label');
		$pallet_label_rec=$params['orders_pall_labelrec'];
		$pallet_status=$this->model_Settings->get('pallets.pallet_types',TRUE);
		//dump($pallets);exit;
		foreach ($pallets as $key => $value) 
		{
			$pallets[$key]['template']=$value['status']==0 ? $pallet_label_rec : $pallet_label;
			if ( array_key_exists($value['status'], $pallet_status))
			{
				$pallets[$key]['status']=$pallet_status[$value['status']];
			}			
		}
		
		foreach ($pallets as $record) 
		{
			$this->addMovementHistory(MovementType::labels,null,null,$record['reference'],lang('system.pallets.print_from_order'),'pallet');
		}
		
		return loadModule('Documents','template',[$pallets]);		
	}

	function bookin($record=null)
	{
		return $this->book($record,FALSE);
	}
	
	function bookout($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=$this->request->getPost();
		
		if (array_key_exists('oid', $post))
		{
			$pallets=$this->model_Pallet->getQtyOfIncompletePalletsForOrders($post['oid']);
			$orders=[];
			foreach ($post['oid'] as $reference) 
			{
				$reference=$this->model_Orders->find($reference);
				if ($reference==null)
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.order_id_error','danger'));
				}
				if (array_key_exists($reference['reference'], $pallets))
				{
					return redirect()->to($refurl)->with('error',$this->createMessage(lang('system.orders.customer_bookinpage',[$reference['reference']]),'warning'));
				}	
				$orders[]=$reference['reference'];
			}
				$status=$this->model_Settings->get('orders.orders_status_comp');
				if ($this->model_Orders->setOrderStatus($status,$orders))
				{
					$this->addMovementHistory(MovementType::order_picked,null,null,$reference['reference'],null,'orders');
					return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
				}
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_save_no','danger'));
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.order_dimm_error','warning'));
		}
		dump($post);exit;
	}
	
	function book($record,$out)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$record=$this->model_Orders->find($record);
		
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_invalidordqty','danger'));
		}
		
		$status=$this->model_Settings->get('orders.*');
		
		if ($out)
		{
			$pallets=$this->model_Pallet->getQtyOfPalletsInPick($record['reference']);
		}else
		{
			$pallets=$this->model_Pallet->getQtyOfPalletsInDelivery($record['reference']);
		}
		//dump($pallets);exit;
		if ($pallets==0 && $out)
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.orders.customer_ordercompok','info'));
		}else
		if ($pallets==0 && !$out)
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.orders.supplier_palletsbooked','info'));
		}else
		if ($pallets==-2)
		{
			if ($out)
			{
				$this->model_Orders->setOrderStatus($status['orders_status_comp'],$record['reference']);
				$this->addMovementHistory(MovementType::receipt_full,null,null,$record['reference'],null,'orders');
			}else
			{
				$this->model_Orders->setOrderStatus($status['orders_status_full'],$record['reference']);
				$this->addMovementHistory(MovementType::receipt_full,null,null,$record['reference'],null,'orders');
			}
			
			$this->model_Tasks_Rule->actionRuleByTrigger('Order_booked',[$record]);
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_palletsbooked','info'));
		}
		
		if ($record['status']==$status['orders_status_def'] && !$out)
		{
			$this->model_Orders->setOrderStatus($status['orders_status_rec'],$record['reference']);
			$this->addMovementHistory(MovementType::receipt_receiving,null,null,$record['reference'],null,'orders');
			$record['status']=$status['orders_status_rec'];
		}
		
		if ($out && $record['type']==1)
		{
			$this->model_Orders->setOrderStatus($status['orders_status_pick'],$record['reference']);
			$this->addMovementHistory(MovementType::order_inpick,null,null,$record['reference'],null,'orders');
			$record['status']=$status['orders_status_pick'];
		}

		$this->model_Tasks_Rule->actionRuleByTrigger('Order_book',[$record]);
		
		$filters=['access'=>'@loged_users','enabled'=>1];
		
		if ($out)
		{
			$filters[$record['status']==$status['orders_status_recok'] ? $status['orders_status_pick_column'] : 'corder']=$record['reference'];
			$filters['status <>']=$this->model_Settings->get('pallets.pallet_type_load');
		}else
		{
			$filters['sorder']=$record['reference'];
		}

		$this->setTableView('Warehouse/receipt_bookin')
			 ->setData('pallet',null,TRUE,null,$filters)
			 ->setPageTitle($record['type']==0 ? 'system.orders.supplier_bookinpage' : 'system.orders.customer_bookinpage',[$record['reference']])
			 ->addColumn('system.pallets.index_reference','reference',TRUE)
			 ->addColumn('system.orders.suppliers_palletowner','customer')
			 ->addColumn('system.pallets.index_status','status',TRUE,$this->model_Settings->get('pallets.pallet_types',TRUE))
			 ->addColumn('system.pallets.index_size','size')
			 ->addColumn('system.pallets.index_stack','stack')
			 ->addColumn('system.pallets.index_location','location')
			 
			 ->addBreadcrumb('system.orders.index_bread',url($this))
			 ->addBreadcrumb($record['reference'],url($this,'order',[$record['oid']],['refurl'=>base64url_encode(url($this))]))
			 ->addBreadcrumb('system.pallets.mainemnenu_pallets','/')
			 
			 ->addData('scan_link',url('Pallets','pallet',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
			 ->addData('new_link',url('Pallets','pallet',['new'],['refurl'=>current_url(FALSE,TRUE),'sorder'=>base64url_encode(json_encode($record))]))
			 ->addEditButton('system.pallets.index_lblbtn','Pallets::label',null,"btn-secondary",'fas fa-barcode',['target'=>'_blank'],AccessLevel::view)
			 ->addEditButton('system.pallets.index_editbtn','Pallets::pallet',null,'btn-primary','fa fa-edit',[],AccessLevel::view)
			
			 ->addHeaderButton(($out ? 'corder/' : 'receipt/').$record['oid'],null,'link','btn btn-secondary btn-sm','<i class="fas fa-file-import mr-1"></i>',lang($out ? 'system.orders.supplier_ordbtn' : 'system.orders.supplier_recbtn'),AccessLevel::edit)
			 ->addHeaderButton(null,'id_scanpallet','button','btn btn-secondary btn-sm','<i class="fas fa-barcode mr-1"></i>',lang('system.orders.supplier_scanbtn'),AccessLevel::view)
			 ->addHeaderButton(null,'id_newpallet','button','btn btn-primary btn-sm tableview_def_btns','<i class="fa fa-plus mr-1"></i>',lang('system.buttons.new'),AccessLevel::view);
		if (($out && $record['status']==$status['orders_status_pick']) || !$out)
		{
			//$this->view->addHeaderButton('received/'.$record['oid'],null,'button','btn btn-success btn-sm tableview_def_btns','<i class="fas fa-clipboard-check mr-1"></i>',lang('system.orders.supplier_combtn'),AccessLevel::view);
		}
		$this->view->render();
	}
	
	function palletcomplete($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$record=$this->model_Orders->find($record);
		
		$post=$this->request->getPost();
		
		if (!array_key_exists('pid', $post) || (array_key_exists('pid', $post) && !is_array($post['pid']) || (array_key_exists('pid', $post) && is_array($post['pid']) && count($post['pid'])<1)))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_palletsinvqty','warning'));
		}
		$status=$this->model_Settings->get('system.pallet_type_*');
		
		if ($this->model_Pallet->completeReceiptPallets($post['pid'],$record['reference'],$status['pallet_type_create'],$status['pallet_type_booked']))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_palletcompok','success'));
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_palletcompfailed','danger'));
	}
	
	function completeOrderIfPalletsCompleted($reference,$type,$status=null)
	{
		if (strlen($reference)>0)
		{
			$pallet_type_create=$this->model_Settings->get($type==1 ? 'pallets.pallet_type_comp' : 'system.pallet_type_create');
			
			$pallet_type_create=$this->model_Pallet->getQtyOfPalletsFromOrder($reference,$pallet_type_create,$type);
			
			if (($pallet_type_create==0 || $pallet_type_create=='0') || ($type==1 && $pallet_type_create>0))
			{
				$status=$status==null ? $this->model_Settings->get($type==1 ? 'orders.orders_status_comp' : 'orders.orders_status_recok') : $status;
				
				if ($this->model_Orders->setOrderStatus($status,$reference))
				{
					$this->addMovementHistory($type==1 ?MovementType::order_picked : MovementType::receipt_complete,null,null,$reference,null,'orders');
					return TRUE;
				}
			}
		}
		
	}
	
	
	function received($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$record=$this->model_Orders->find($record);
		
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_invalidordqty','danger'));
		}
		if ($record['type']==0)
		{
			$pallet_type_create=$this->model_Settings->get('system.pallet_type_create');
		}else
		{
			$pallet_type_create=$this->model_Settings->get('pallets.pallet_type_comp');
		}
		
		$pallets_all=$this->model_Pallet->getQtyOfPalletsFromOrder($record['reference'],null,$record['type']);
		
		$pallets_intrns=$this->model_Pallet->getQtyOfPalletsFromOrder($record['reference'],$pallet_type_create,$record['type']);
		
		
		if (($record['type']==0 && $pallets_intrns!=0) || ($record['type']==1 && $pallets_all!=$pallets_intrns))
		{
			$pallet_types=$this->model_Settings->get('pallets.pallet_types',TRUE);
			return redirect()->to($refurl)->with('error',$this->createMessage(lang('system.orders.supplier_palletsinvstatus',[$pallet_types[$pallet_type_create]]),'warning'));
		}

		$status=$this->model_Settings->get('orders.*');
		if ($record['type']==0)
		{
			$status=$status['orders_status_recok'];
		}else
		{
			$status=$status['orders_status_comp'];
		}
		//$status=1;
		if ($this->model_Orders->setOrderStatus($status,$record['reference']))
		{
			$this->addMovementHistory(MovementType::receipt_complete,null,null,$record['reference'],null,'orders');
			$this->model_Tasks_Rule->actionRuleByTrigger($record['type']==0 ? 'Order_received' : 'Order_complete',[$record]);
			return redirect()->to(url($this))->with('error',$this->createMessage($record['type']==0 ? 'system.orders.supplier_ordercompok' : 'system.orders.customer_ordercompok' ,'success'));
		}else
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_ordercompfailed','danger'));
		}
	}

	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=count($post)>0 ? $post : $this->request->getPost();
		if (array_key_exists('model', $post) && $post['model']=='orders')
		{
			if (!array_key_exists('oid', $post))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
			}
			if ($this->model_Orders->deleteOrder($post))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_delete_ok','success'));
			}
		}else
		{
			return parent::delete($post);
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
	}
	
	function save($model,$post=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$post=$post==null ? $this->request->getPost(): $post;
		$post['model']=$model;
		
		if ($model=='orders')
		{
			$settings=$this->model_Settings->get('orders.*');
			
			if (Arr::KeysExists(['type','status'],$post) && ($post['type']==0 || $post['type']=='0') && $post['status']==$settings['orders_status_full'])
			{
				$pall_status=$this->model_Settings->get('pallets.pallet_types',TRUE);
				
				if ($this->model_Pallet_Pallet->count(['sorder'=>$post['reference'],'status'=>0]) > 0)
				{
					$refurl=$refurl.(Str::contains('?',$refurl) ? '?' : '&').'tab=pall';
					return redirect()->to($refurl)->with('error',$this->createMessage(lang('system.orders.supplier_palletsinvstatus',[$pall_status[0]]),'warning'));
				}	
			}
			
			if (array_key_exists('pallets', $post) && is_array($post['pallets']) && count($post['pallets']) > 0)
			{
				$this->model_Pallet_Pallet->updateMany($post['pallets'],'reference');
			}
			
			if (array_key_exists('ocfg', $post) && is_array($post['ocfg']))
			{
				$post['ocfg']=json_encode($post['ocfg']);
			}
			
			if (Arr::keysExists(['status','type','reference'],$post) && $post['type']==1 && $post['status']==$settings['orders_status_comp'])
			{
				if ($this->model_Pallet_Pallet->getQtyOfPalletsInPick($post['reference'])>0)
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.orders.supplier_palletsinvstatus_error','warning'));
				}
			}
			
			if (!array_key_exists('print_pall', $post))
			{
				 if(array_key_exists('oid', $post) && strlen($post['oid'])>0)
				{
					$post['print_pall']=$post['oid'];
				}else
				{
					$post['print_pall']='%id%';
				}
			}
		}
		
		return parent::save($model,$post);	
	}
	
	function _after_save($type,$post,$refurl,$refurl_ok)
	{
		if ($type=='model_orders' || $type=='orders')
		{
			$status=$this->model_Settings->get('orders.*');
			if (array_key_exists('reference', $post))
			{
				$this->model_Warehouse_Alocation->where('pallet',$post['reference'])->delete();
				if (array_key_exists('stock', $post))
				{
					foreach ($post['stock'] as $value) 
					{
						$value['sorder']=array_key_exists('reference', $post) ? $post['reference']:'';
						$this->model_Warehouse_Alocation->save($value);
					}
				}
				$this->model_Tasks_Rule->actionRuleByTrigger('Order_save',[$post]);
				if (Arr::keysExists(['reference','reference_old'], $post) && strlen($post['reference_old']) > 0 && strlen($post['reference']))
				{
					$this->model_Pallet_Pallet->changePalletsOrder($post['reference_old'],$post['reference']);
				}
				/**/
			}
			
			if (array_key_exists('status', $post) && ($post['status']==$status['orders_status_comp'] || $post['status']==$status['orders_status_full'] ))
			{
				$this->addMovementHistory($post['status']==$status['orders_status_full'] ? 'receipt_complete' : 'order_picked',null,null,$post['reference'],null);
				$this->model_Orders->save(['oid'=>$post['oid'],'completed'=>formatDate()]);
			}
			
			if (array_key_exists('status', $post) && Str::contains($this->model_Settings->get('orders.orders_autocreate_palletslabels'),$post['status']))
			{
				$post['oid']=!array_key_exists('oid', $post) ? $this->model_Orders->getLastID() : $post['oid'];
				return $this->palletlabels('receipt',$post['oid'],$this->model_Settings->get('orders.orders_pall_labelrec'));
			}
		}
		return TRUE;
	}
	
	//newcustjob
	function newcjob()
	{
		
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$status=$this->model_Settings->get('orders.orders_status_*');
		
		$params=$this->model_Settings->get('orders.orders_*');
		$record=array_combine($this->model_Orders->allowedFields, array_fill(0, count($this->model_Orders->allowedFields), ''));
		$record[$this->model_Orders->primaryKey]='';
		
		return $this->setFormView('Warehouse/newjob')
					->setFormTitle('system.orders.suppliers_page_receipt_new')
					->setPageTitle('system.orders.suppliers_page_receipt_new')
					->setFormAction($this,'autoGenerateCustomerPallets',[],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl,null)
					->setFormArgs([],['refurl_ok'=>url($refurl)])
					->setCustomViewEnable(FALSE)
					
					->addBreadcrumb('system.orders.index_bread',url($this))
					->addBreadcrumb('New Job',current_url())
					
					->addNumberField('system.pallets.pallet_height',null,'height[]',3000,10,['required'=>'true'])
					->addDropDownField('system.pallets.pallet_stack','stack[]',$this->model_Pallet_PalletStack->getForForm('code','name',TRUE),null,['required'=>'true'])
					->addInputField('system.pallets.suppalref','suppalref[]',null,['maxlength'=>150])
					
					->addDropDownField('system.pallets.pallet_customer','customer',$this->model_Owners_Customer->getForForm('code','code',TRUE),null,['required'=>'true'])
					->addInputListField('system.pallets.pallet_orderreford','corder',null,$this->model_Warehouse_Orders->getCOrdersForForm(),['validation'=>FALSE])
					->addInputField('XSNumber','xsnumber',null,[])//only for apd
					->addDropDownField('system.pallets.pallet_supplier','supplier',$this->model_Owners_Supplier->getForForm('code','name',TRUE),null,['required'=>'true'])
					->addInputListField('system.pallets.pallet_orderrefrec','sorder',null,$this->model_Warehouse_Orders->getReceiptsForForm(),['validation'=>FALSE])
					->addDropDownField('system.pallets.pallet_size','size',$this->model_Pallet_PalletSize->getForForm('name','name'),'EUR_120x80',['required'=>'true'])
					
					->addDropDownField('system.pallets.index_status','status',$this->model_Settings->getPalletStatusForForm(0,100),$record['status'],[])
					->addInputListField('system.pallets.pallet_location','location',$params['orders_location_def_rec'],$this->model_Warehouse_Location->getForForm('code','code'),[])
					->addNumberField('system.orders.supplier_palletsqty','','pallets_qty',100,1,['required'=>'true'])
					
					
					
					->addHiddenField('created',formatDate())
					//->addHiddenField('status',1)
					->addHiddenField('type',0)
					->addHiddenField('operator',loged_user('username'))
					->addHiddenField('reference_old','',['id'=>'id_reference_old'])
					->addHiddenField('enabled',1)
					->addHiddenField('access',$params['orders_access_def'])  
					
					
					->addData('apiurl',url('owners','api',['nextreference']))
					->addData('newjobc',1)
					->addData('orders_inpick',$this->model_Orders->getOrdersWithStatus($this->model_Settings->get('orders.orders_status_pick')))
					
					->render();
	}
	
	function newjob()
	{
		
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$status=$this->model_Settings->get('orders.orders_status_*');
		
		if ($this->isMobile())
		{
			return $this->info($record,$refurl);
		}
		$params=$this->model_Settings->get('orders.orders_*');
		return $this->setFormView('Warehouse/newjobsupp')
					->setFormTitle('system.orders.suppliers_page_receipt_new')
					->setPageTitle('system.orders.suppliers_page_receipt_new')
					->setFormAction($this,'autoGeneratePallets',[],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl,null)
					->setFormArgs([],['refurl_ok'=>url($refurl)])
					->setCustomViewEnable(FALSE)
					->addDropDownField('system.orders.supplier_supplier','owner',$this->model_Owners_Supplier->getForForm('code','name',TRUE),null,['required'=>'true'])
					
					->addBreadcrumb('system.orders.index_bread',url($this))
					->addBreadcrumb('New Job',current_url())
					
					->addInputField('system.orders.supplier_ordernr','reference',null,['required'=>'true'])
					->addNumberField('system.orders.supplier_palletsqty','','pallets_qty',100,1,['required'=>'true'])
					
					->addHiddenField('created',formatDate())
					->addHiddenField('type',0)
					->addHiddenField('operator',loged_user('username'))
					->addHiddenField('reference_old','',['id'=>'id_reference_old'])
					->addHiddenField('enabled',1)
					->addHiddenField('access',$params['orders_access_def'])  
					->addHiddenField('status',$params['orders_status_def']) 
					->addHiddenField('location',$params['orders_location_def_rec']) 
					->addData('apiurl',url('owners','api',['nextreference']))
					->render();
	}
	/**
	 * Create multi customer pallets
	 * 
	 * #autocust#
	 */
	function autoGenerateCustomerPallets()
	{
		$data=$this->request->getPost();
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if (array_key_exists('pallets_qty', $data) && is_numeric($data['pallets_qty']))
		{
			$records=[];
			$movements=[];
			$reference=$this->model_Pallet_Pallet->generatePalletReference(null,FALSE);
			$params=$this->model_Settings->get('pallets.*');
			$height=[];
			if (array_key_exists('height', $data) && is_array($data['height']) && count($data['height'])>0 )
			{
				$height=$data['height'];
			}
			
			$stack=[];
			$print_pall=[];
			if (array_key_exists('stack', $data) && is_array($data['stack']) && count($data['stack'])>0 )
			{
				$stack=$data['stack'];
			}
			$suppalref=[];
			if (array_key_exists('suppalref', $data) && is_array($data['suppalref']) && count($data['suppalref'])>0 )
			{
				$suppalref=$data['suppalref'];
			}
			for ($i=1; $i <= $data['pallets_qty']; $i++) 
			{
				$reference=$this->model_Pallet_Pallet->generatePalletReference($reference,$i==$data['pallets_qty']); 
				$records[]=
				[
					'reference'=>$reference,
					'customer'=>$data['customer'],
					'corder'=>$data['corder'],
					'xsnumber'=>$data['xsnumber'],
					'supplier'=>$data['supplier'],
					'sorder'=>$data['sorder'],
					'size'=>$data['size'],
					'stack'=>array_key_exists($i-1, $stack) ? $stack[$i-1] : null,
					'height'=>array_key_exists($i-1, $height) ? $height[$i-1] : null,
					'suppalref'=>array_key_exists($i-1, $suppalref) ? $suppalref[$i-1] : null,
					'operator'=>$data['operator'],	
					'location'=>$data['location'],
					'access'=>$data['access'],
					'enabled'=>1,
					'status'=>$data['status'],
					'order'=>count($records)+1
				];
				$print_pall[]=$reference;
				$movements[]=
				[
					'mhtype'=>0,
					'mhdate'=>formatDate(),
					'mhuser'=>loged_user('username'),
					'mhfrom',
					'mhto',
					'mhref'=>$reference,
					'mhinfo'=>$data['corder']
				];
			}
			
			if (count($records)>0)
			{
				$this->model_Pallet_Pallet->insertBatch($records);
				$this->model_Warehouse_Movements->insertBatch($movements);
			}
		}
		
		$order=$this->model_Orders->filtered(['reference'=>$data['corder']])->first();
		$data['status']=3;
		$data['refurl_ok']=url($this);
		$data['reference']=$data['corder'];
		$data['owner']=$data['customer'];
		$data['type']=1;
		if (array_key_exists('reference_old', $data))
		{
			unset($data['reference_old']);
		}
		$data['print_pall']=implode('_',$print_pall);
		$this->addMovementHistory(MovementType::receipt_full,null,null,$data['reference'],'auto','orders');
		return is_array($order) && count($order)>0 ? 
		redirect()->to(url($this))->with('error',$this->createMessage('system.general.msg_save_ok','success'))->with('print_pall',$data['print_pall'])
		: $this->save('orders',$data);		
	}
	
	function autoGeneratePallets(array $data=[])
	{
		$data=count($data) > 0 ? $data :$this->request->getPost();
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$reference=null;
		if (array_key_exists('pallets_qty', $data) && is_numeric($data['pallets_qty']))
		{
			$records=[];
			$movements=[];
			$reference=$this->model_Pallet_Pallet->generatePalletReference($reference,FALSE);
			$params=$this->model_Settings->get('pallets.*');
			for ($i=1; $i <= $data['pallets_qty']; $i++) 
			{
				$reference=$this->model_Pallet_Pallet->generatePalletReference($reference,$i==$data['pallets_qty']); 
				$records[]=
				[
					'reference'=>$reference,
					'operator'=>$data['operator'],
					'supplier'=>$data['owner'],
					'sorder'=>$data['reference'],
					'status'=>array_key_exists('status', $data)?$data['status']:0,
					'size'=>$params['def_pallet_size'],
					'stack'=>$params['def_pallet_stack'],
					'location'=>$data['location'],
					'access'=>$data['access'],
					'enabled'=>1,
					'order'=>count($records)+1
				];
				$movements[]=
				[
					'mhtype'=>0,
					'mhdate'=>formatDate(),
					'mhuser'=>array_key_exists('username', $data) ? $data['username'] : loged_user('username'),
					'mhfrom',
					'mhto',
					'mhref'=>$reference,
					'mhinfo'=>$data['reference']
				];
			}
			
			if (count($records)>0)
			{
				$records=$this->model_Pallet_Pallet->insertBatch($records);
				$this->model_Warehouse_Movements->insertBatch($movements);
				
			}
			
		}
		if(!array_key_exists('status', $data)) 
		{
			$data['status']=$this->model_Settings->get('orders.orders_status_def');
		}
		if(!array_key_exists('refurl_ok', $data)) 
		{
			$data['refurl_ok']=url($this);
		}
		
		$this->addMovementHistory(MovementType::receipt_full,null,null,$data['reference'],'auto','orders');
		return $this->save('orders',$data); 
	}

	function movePalletForCustomerOrder($referrence,$status,$location,$statusCheck=null)
	{
		$statusCheck=$status==null ? $this->model_Settings->get('orders.orders_status_pick') : $statusCheck;
		
		if ($statusCheck==$status)
		{
			$referrence=$this->model_Orders->find($referrence);
			
			if (!is_array($referrence) && $referrence==null)
			{
				return FALSE;
			}
			$pallets=$this->model_Pallet_Pallet->filtered(['access'=>'@loged_user','enabled'=>1,'corder'=>$referrence['reference'],'location <>'=>$location])->find();
			
			if (!is_array($pallets) && $pallets==null)
			{
				return FALSE;
			}
			foreach ($pallets as $value) 
			{
				$this->model_Tasks_Task->addNewPalletMovement($value['pid'],$referrence['location'],$value['access']);
			}
			return TRUE;
		}
		return FALSE;
	}
	
}