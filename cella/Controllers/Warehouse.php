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

class Warehouse extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'locations'=>AccessLevel::view,
		'location'=>AccessLevel::delete,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'location'=>'Warehouse/Location',
		'product'=>'Warehouse/Product',
		'collections'=>'Collections/Load',
		'items'=>'Collections/Items'
	];
	
	/* Collections */
	function index()
	{
		
		if ($this->isMobile())
		{
			return $this->index_mobile();
		}
		
		$filters=[];
		if ($this->request->getGet('filtered')==null && $this->request->getGet('filter')==null)
		{
			$filters['status <>']=2;
		}
		$filters['access']='@loged_user';
		$count=$this->model_Collections->count(['status <'=>2]);
		$this->setTableView('Warehouse/Loads/list')
			 ->setData('collections','duein DESC',FALSE,'reference',$filters)
			 ->setPageTitle('system.warehouse.mainmenu_collections')
			 ->addFilters('index')
			 ->addFilterField('reference %')
			 			 
			 ->addColumn('system.warehouse.collections_reference','reference',TRUE)
			 ->addColumn('system.warehouse.collections_status','status',TRUE,lang('system.warehouse.collections_status_list'))
			 ->addColumn('system.warehouse.collections_duein','duein',FALSE,[],'d M Y')
			 ->addColumn('system.warehouse.collections_created','created',FALSE,[],'d M Y')
			 ->addColumn('system.warehouse.collections_createdby','operator_created')
			 ->addColumn('system.warehouse.collections_progress','progress',FALSE,[],'prg:/')
			 //->addColumn('system.warehouse.collections_location','location',TRUE)
			 //->addColumn('system.warehouse.collections_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 //->addColumn('system.warehouse.collections_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])			 
			 
			 ->addEditButton('system.warehouse.collections_stackbtn','stackingplancreator',null,'btn-danger actBtn','fas fa-magic',['data-status'=>'S_-reference-'],AccessLevel::edit)
			 //->addEditButton('system.warehouse.collections_stackbtn','stackingplan',null,'btn-secondary actBtn','fab fa-unsplash',['data-status'=>'S_-reference-'],AccessLevel::edit)
			 
			 //->addEditButton('system.warehouse.collections_infobtn','infocollection',null,'btn-info actBtn','fas fa-info-circle',['data-status'=>'2_-reference-,3-reference-'],AccessLevel::view)
			 //->addEditButton('system.warehouse.collections_compbtn','complete',null,'btn-success actBtn','far fa-check-circle',['data-status'=>'1_-reference-'],AccessLevel::state)
			 ->addEditButton('system.warehouse.collections_editbtn','collection',null,'btn-primary edtBtn','fa fa-edit',['data-status'=>'-status-'])			 
			 
			 //->addEnableButton()
			 //->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('collection/new')
			 
			 ->addHeaderButton('complete','id_complete','button','ml-2 btn btn-success btn-sm tableview_def_btns'.($count < 1 ? ' d-none' : ''),'<i class="far fa-check-circle mr-1"></i>',lang('system.warehouse.collections_comppalls'),AccessLevel::edit)
			 
			 ->addBreadcrumb('system.warehouse.mainmenu_collections',url($this))
			 
			 ->addData('readytocompl',$this->model_Collections->getReadyToCompleteLoads())
			 ->addData('comp',$this->model_Collections->getCompletedLoads())
			 ->addData('stack',$this->model_Collections->getLoadsWithPallets())
			 ->setCustomViewEnable(FALSE)
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
			 
		foreach (lang('system.warehouse.collections_status_list') as $key => $value) 
		{
			$this->view->addFilterField('status',$value,$value);	
		}
		return $this->view->render();
	}
	
	private function index_mobile()
	{
		$post=$this->request->getPost();
		if ($this->request->getMethod()=='post')
		{
			if (is_array($post) && array_key_exists('reference', $post))
			{
				return redirect()->to(url($this,'collection',[$post['reference']],['refurl'=>base64url_encode(url($this))]));
			}
			error:
			return redirect()->to(url($this))->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}
		$loads=$this->model_Collections->getLoadsForMobile('reference');
		
		
		 $this->setFormView('Warehouse/Loads/index_mobile')
			  ->setFormTitle('system.warehouse.mainmenu_collections')
			  ->setPageTitle('system.warehouse.mainmenu_collections')
			  ->setFormCancelUrl(site_url())
			  ->setCustomViewEnable(TRUE)
			  //->setFormSaveUrl(['class'=>'btn btn-lg btn-success mb-3 w-100','text'=>'system.warehouse.collection_taskstart','icon'=>'fas fa-truck-loading'])
			  ->addData('loads',$loads)
			  ->addHiddenField('reference','aa',['id'=>'id_reference','type'=>'text']);
		if (is_array($loads) && count($loads) > 0)
		{
			$this->view->setFormAction($this)
					   //->addInputListField('system.warehouse.collections_reference','reference',null,$loads,[])
					   ->addDropDownField('system.warehouse.collections_reference','reference',$loads,null,[]);
		}else
		{
			$this->view->addData('_form_error',$this->createMessage('system.warehouse.collections_noorders','info'));
		}
			  
		$this->view->render();
	}
	
	/*Collectionedit*/
	function collection($record=null,$readonly=FALSE)
	{
		
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'collections') : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl);
		}

		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$record=is_string($record) && $record =='last' ? $this->model_Collections->getLastID() : $record;
		$isnew=$record=='new';
		if ($record=='new')
		{
			$record=array_combine($this->model_Collections->allowedFields, array_fill(0, count($this->model_Collections->allowedFields), ''));
			$record[$this->model_Collections->primaryKey]='';
			$record['enabled']='1';
			$record['access']=$this->model_Settings->get('system.def_access_level');
			$ref_ok=url($this,'collection',['last'],['refurl'=>base64url_encode($refurl)]);
			
		}else
		{
			$ref_ok=current_url();
			if (is_numeric($record))
			{
				$record=$this->model_Collections->find($record);
			}else
			{
				$record=$this->model_Collections->filtered(['reference'=>$record])->first();
			}
			
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}

		if (!array_key_exists($this->model_Collections->primaryKey, $record))
		{
			$record[$this->model_Location->primaryKey]='';
			$record['location']='GOODS OUT';
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}
		
		if ($this->view->isMobile())
		{
			$this->model_Tasks_Notification->changeStatus(['text %'=>$record['reference']],0);
			return $this->loadtask($record);
		}
	
		$readonly=$record['status'] ==2;
		
		$ro=!$this->auth->hasAccess(AccessLevel::delete);
		$ro=$readonly;
		
		$record['status']=$record['status']==33 || $record['status']=='33' ? 3 : $record['status'];
		
		$statuslist=lang('system.warehouse.collections_status_list');
		$record['status']=$record['status']==3||$record['status']=='3' ? 33 : $record['status'];
		if (array_key_exists($record['status'], $statuslist))
		{
			$record['status_name']=$statuslist[$record['status']];
		}
		$record['isnew']=$isnew;
		
		$this->setFormView('Warehouse/Loads/load')
					->setFormTitle('system.warehouse.collections_editbtn')
					->setPageTitle('system.warehouse.collections_editbtn')
					->setFormAction($this,'save',['collections'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
				   	->setFormArgs([],['lid'=>$record['lid'],'refurl_ok'=>$ref_ok])
					->setCustomViewEnable(FALSE)
					->setFieldAccessRule('load')
				   	
					->addDatePicker('system.warehouse.collections_duein','duein',$record['duein'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ["required"=>1,'minDate'=>'new Date()','id'=>'id_duein']) 
				   	->addInputListField('system.warehouse.collections_reference','reference',$record['reference'],$this->model_Warehouse_Orders->getOrdersWithStatus($this->model_Settings->get('orders.orders_status_comp'),1),!$isnew || $ro ? ['readonly'=>TRUE,'class'=>'bg-light','validation'=>FALSE] : ['validation'=>FALSE,'required'=>'true','maxlength'=>150])
				   	
				   	//->addInputListField('system.warehouse.collections_location','location',$record['location'],$this->model_Warehouse_Location->getGoodsOutLocations(),["required"=>1])
					->addDropDownField('system.warehouse.collections_status','status',$statuslist,$record['status'],$isnew  ? ['type'=>'hidden'] : ($record['status']==2 ? ['disabled'=>TRUE,'class'=>'bg-light']:['validation'=>FALSE]))
				   	//->addYesNoField('system.warehouse.locations_enabled',$record['enabled'],'enabled',$ro ? ['disabled'=>TRUE,'class'=>'bg-light'] : [])
				   	->addAcccessField('system.warehouse.locations_access',$record['access'],'access',[],['type'=>'hidden'])
					->addHiddenField('location','GOODS OUT')
					->addHiddenField('enabled',1)
					
					->addData('record',$record)
					->addData('items',$this->model_Items->getReadyForLoadJobs($record['status'] ==2 ? $record['reference']: null))//getReadyForLoadPallets
					->addData('load_items',$this->model_Items->getLoadPallets($record['reference']))
					->addData('load_items_status',lang('system.warehouse.collections_status_list'))
					->addData('load',$record['reference']==null ? 'new' : $record['reference'])
					->addData('load_id',$record['reference']==null ? 'new' : $record['lid'])
					->addData('items_layout','load_jobs')
					->addData('set_pallets',$this->model_Items->getLoadPallets($record['reference']))
					->addData('pallets_types',$this->model_Settings->get('pallets.pallet_types',TRUE))
					->addData('complpalurl',url($this,'complete',[],['refurl'=>current_url(FALSE,TRUE)]))
					->addData('curtab',$this->request->getGet('tab')==null ? 0 : $this->request->getGet('tab'))
					->addData('readonly',$ro)
					->addData('savevis',$record['status'] < 2 || $record['status']==3)
					->addData('stackingplan',$this->model_Items->getLoadPalletsForStackPlan($record['reference'],'load'))
					->addData('stackingedit',TRUE)
					//mevementsbyref mevementsbyinfo
					->addData('movements',loadModule('Movements','mevementsbyref',
									[
										[$record['reference'],'collections'],
										[
											'mhtype'=>['value'=>'mhtype','list'=>$this->model_Settings->getMovementTypes()]
											,'mhdate','mhuser','mhfrom','mhto','mhref','mhinfo'
										],
									]			
							))
					->addBreadcrumb(lang('system.warehouse.mainmenu_collections'),$refurl)
					->addBreadcrumb($record['lid'],$refurl)
					->addHelpContent('warehouse')
					->addDataTableScript(['loadItemsTableAvaliable','loadItemsTable1','loadTasksTable'],['pageLength'=>100,'ordering'=>'false']);
			
			return	$this->view->render();
	}
	
	function collectionmanifest($record)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'collections') : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl);
		}

		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		
		if (is_numeric($record))
		{
			$record=$this->model_Collections->find($record);
		}else
		{
			$record=$this->model_Collections->filtered(['reference'=>$record])->first();
		}
		
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}
		
		$record['pallets']=$this->model_Items->getLoadPalletsForStackPlan($record['reference'],'load');
		$record['template']='collection_manifest';
		return loadModule('Documents','template',[$record,TRUE]);
	}
	
	function getcollectionref($ref)
	{
		$ref="COL".$ref;
		check_ref:
		$col=$this->model_Collections->count(['reference'=>$ref]);
		if ($col > 0)
		{
			$n=1;
			if (Str::contains($ref,'_'))
			{
				$ref=explode('_',$ref);
				$n=$ref[1];
				$n++;
				$ref=$ref[0];
			}
			$ref=$ref.'_'.$n;
			goto check_ref;
		}
		return $this->response->setJson(['result'=>$ref]);
	}
	
	/*collectionmobile*/
	private function loadtask($record)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'collections') : base64url_decode($refurl);
		
		$post=$this->request->getPost();
		if ($this->request->getMethod()=='post')
		{
			//dump($post);exit;
			if (array_key_exists('pallets', $post) && is_array($post['pallets']) && count($post['pallets']) > 0)
			{
				$post=$post['pallets'][0];
				if (array_key_exists('reference', $post))
				{
					if (Arr::KeysExists(['reference','location','info','old_location'],$post))
					{
						$this->model_Items->changeTaskStatus($post['reference'],33,$post['location']);
						$this->addMovementHistory('move',$post['old_location'],$post['location'],$post['reference'],$post['info'],'pallets');
						$this->addMovementHistory('collections_loadedtask',$post['old_location'],$post['location'],$post['info'],$post['reference'],'collections');
					}
				}
				
				if ($this->model_Items->getQtyOfNotDispatchedItems($post['info']) > 0)
				{
					return redirect()->to(url($this,'collection',[$post['info']],['refurl'=>base64url_encode($refurl)]));
				}else
				{
					$post=$this->model_Collections->filtered(['reference'=>$post['info']])->first();
					
					if (is_array($post) && count($post) > 0)
					{
						$post['refurl_ok']=url($this);
						$post['status']=3;
						$post['loaded']=formatDate();
						return $this->save('collections',$post);
					}else
					{
						return redirect()->to(url($this));
					}
				}	
			}
		}
		$ref=$this->request->getGet('palref');
		if ($ref!=null)
		{
			$move_fields=$this->model_Items->getLoadPallets($record['reference'],TRUE,$ref);
			if (is_array($move_fields) && count($move_fields) > 0)
			{
				$move_fields=$move_fields[0];
			}else
			{
				return redirect()->to(url($this))->with('error',$this->createMessage('system.warehouse.collections_notasks','info'));
			}
		}else
		{
			$move_fields=$this->model_Items->getLoadPallets($record['reference'],TRUE);
			
			if (count($move_fields)==0)
			{
				return redirect()->to(url($this))->with('error',$this->createMessage('system.warehouse.collections_notasks','info'));
			}
			return $this->setFormView('Warehouse/Loads/mobile_colpalllist')
			  ->setFormTitle('system.warehouse.mainmenu_collections')
			  ->setPageTitle('system.warehouse.mainmenu_collections')
			  ->setFormCancelUrl(site_url())
			  ->setCustomViewEnable(TRUE)
			  //->setFormSaveUrl(['class'=>'btn btn-lg btn-success mb-3 w-100','text'=>'system.warehouse.collection_taskstart','icon'=>'fas fa-truck-loading'])
			  ->addData('data',$move_fields)
			  ->render();
		}
	
		$this->model_Items->changeTaskStatus($move_fields['pallref'],1);
		
		if ($record['status']==0)
		{
			$this->model_Collections->changeLoadStatus($move_fields['loadref'],1,TRUE);
			
		}
		
		$move_fields=
		[
			'reference'=>$move_fields['pallref'],
			'putaway'=>$this->model_Warehouse_Location->getForForm('code','code'),
			'location'=>$move_fields['location'],
			'sorder'=>$move_fields['loadref'],
			'stacking'=>$move_fields['stacking'],
			'pid'=>$move_fields['iid'],
		];
		return $this->setFormView('Pallets/move_mobile')//Warehouse/Loads/tasks')
					->setFormTitle('')
					->setPageTitle('system.warehouse.collections_editbtn')
					->setFormAction($this,'collection',[$move_fields['sorder']])
					->setFormCancelUrl(site_url())
					->setCustomViewEnable(FALSE)
					->setFormSaveUrl(['class'=>'btn btn-lg btn-success mb-3 w-100','text'=>'system.warehouse.collection_taskstart','icon'=>'fas fa-truck-loading'])
					->addData('move_fields',$move_fields)
					->render();
	}
	
	function despatch()
	{
		if (!$this->view->isMobile())
		{
			return redirect()->to(site_url())->with('error',$this->createMessage('system.errors.onlymobile_option','warning'));
		}
		$post=$this->getFlashData('_postdata',[]);
		if ($this->request->getMethod()=='post' || count($post) > 0)
		{
			$post=count($post) > 0 ? $post : $this->request->getPost();
			//
			if (array_key_exists('model', $post))
			{
				
				unset($post['model']);
				if (!array_key_exists('pallref', $post))
				{
					return redirect()->to(url($this,'despatch'))->with('error',$this->createMessage('system.warehouse.collections_nocolselerror','warning'))->with('_postdata',$post);
				}
				$statuslist=lang('system.warehouse.collections_status_list');
				
				foreach ($post['pallref'] as $value) 
				{	
					$this->addMovementHistory('collections_disppal',null,null,$post['reference'],$value,'collections');
					$loads=$this->model_Items->changeTaskStatus($value,2,'GOODS OUT');
				}

				$loads=$this->model_Items->getLoadPallets($post['reference'],33);
				
				if (count($loads)>0)
				{
					return redirect()->to(url($this,'despatch'))->with('error',$this->createMessage('system.warehouse.collections_dessingle','info'))->with('_postdata',$post);
				}else
				{
					$this->addMovementHistory('change_status',null,$statuslist[2],$post['reference'],null,'collections');
					$this->model_Collections->changeLoadStatus($post['reference'],2,TRUE);
					return redirect()->to(url($this,'despatch'))->with('error',$this->createMessage(lang('system.warehouse.collections_despalldone',[$post['reference']]),'info'));
				}
			}
			
			if (is_array($post) && !array_key_exists('reference', $post))
			{
				return redirect()->to(url($this,'despatch'))->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
			}
			$loads=$this->model_Items->getLoadPallets($post['reference'],33);
			if (!is_array($loads) || (is_array($loads)&&count($loads)<1))
			{
				return redirect()->to(url($this,'despatch'))->with('error',$this->createMessage('system.warehouse.collections_nodespatchtasks','warning'));
			}
			return $this->setTableView('Warehouse/Loads/despatch_mobile')
			 			->setData($loads,null,FALSE,'stacking')
						->setTickBox(TRUE,'pallref')
			 			->setPageTitle('system.warehouse.mainmenu_collections')
			 			->addColumn('system.warehouse.collections_pallref','pallref',TRUE)
						->addColumn('system.warehouse.collections_location','location',TRUE)
						->addData('_formview_footer','aa')
						->addData('reference',$post['reference'])
						->setCustomViewEnable(FALSE)
			 			->render();
			
		}
		
		$loads=$this->model_Collections->getLoadsForMobile('reference',33);
		
		
		 $this->setFormView('Warehouse/Loads/index_mobile')
			  ->setFormTitle('system.warehouse.mainmenu_collections')
			  ->setPageTitle('system.warehouse.mainmenu_collections')
			  ->setFormCancelUrl(site_url())
			  ->setCustomViewEnable(TRUE)
			  ->addData('loads',$loads)
			  ->addHiddenField('reference','aa',['id'=>'id_reference','type'=>'text']);
		if (is_array($loads) && count($loads) > 0)
		{
			$this->view->setFormAction($this,'despatch')
					   ->addDropDownField('system.warehouse.collections_reference','reference',$loads,null,[]);
		}else
		{
			$this->view->addData('_form_error',$this->createMessage('system.warehouse.collections_noorders','info'));
		}
			  
		$this->view->render();
	}
	
	function infocollection($record=null)
	{
		return $this->collection($record,TRUE);
	}
	
	function complete()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'collections') : base64url_decode($refurl);
		
		$post=$this->request->getPost();
		if (!array_key_exists('lid', $post))
		{
			error:
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_notsel','warning'));
		}
		if (!is_array($post['lid']))
		{
			goto error;
		}
		if (count($post['lid']) < 1)
		{
			goto error;
		}
		$orders=$this->model_Collections->getReadyToCompleteLoads(FALSE,'lid');
		
		foreach ($post['lid'] as $value) 
		{
			$value=$this->model_Collections->find($value);
			
			if (!in_array($value['lid'], $orders))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage(lang('system.warehouse.collections_comperror',[$value['reference']]),'warning'));
			}
			
			$this->model_Collections->changeLoadStatus($value['reference'],2,TRUE);			
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_compok','success'));
	}
	
	function complete_old($id=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if ($this->request->getMethod()=='get')
		{
			$id=$this->model_Collections->find($id);
			if (!is_array($id))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
			}
			
			$dispPalls=$this->model_Items->getQtyOfNotDispatchedItems($id['reference']);
			
			if ($this->view->isMobile())
			{
				if ($dispPalls==0)
				{
					goto pallets_dispatched;
				}
				$refurl=url($this,'warehouse');
				if ($id['status'] > 0)
				{
					return redirect()->to(url('Pallets','putaway',['2'],['refurl'=>base64url_encode($refurl)]));
				}
				if ($this->model_Collections->createMovementTasksForLoad($id))
				{
					$this->model_Collections->save(['status'=>1,'lid'=>$id['lid']]);
					return redirect()->to(url('Pallets','putaway',['2'],['refurl'=>base64url_encode($refurl)]));
				}else
				{
					return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_comperror','warning'));
				}
			}
			
			if ($dispPalls > 0)
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_comperror','warning'));
			}
			
			if ($this->model_Collections->dispatchPallets($id['reference']))
			{
				pallets_dispatched:
				$this->model_Collections->createMovementsHistoryForLoad($id['reference'],2,-1);
				$id['status']=2;
				$id['loaded']=formatDate();
				$id['operator']=loged_user('username');
				if ($this->model_Collections->save($id))
				{
					$this->model_Tasks_Rule->actionRuleByTrigger('Load_complete',[$id]);
				}
				return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_compok','success'));
			}
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_comperror','warning'));
		}else
		if ($this->request->getMethod()=='post')
		{
			$post=$this->request->getPost();
			if (!array_key_exists('pallets', $post))
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_compnopallerror','warning'));
			}
			
			if ($this->model_Items->completePallets($post['pallets']))
			{
				$this->model_Tasks_Rule->actionRuleByTrigger('Load_palletscomplete',[$post]);
				$this->model_Collections->createMovementsHistoryForLoad($id['reference'],2,TRUE);
				return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_comppallok','success'));
			}
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_comppallerror','danger'));
		}
		
	}
	function stackingplancreator($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this) : base64url_decode($refurl);
		$post=$this->request->getPost();
		if ($this->request->getMethod()=='post')
		{
			
			if (is_array($post) && array_key_exists('pallets', $post))
			{
				$this->model_Pallet_Pallet->updateMany($post['pallets'],'reference');
				return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_save_ok','success'));		
			}else
			{
				return redirect()->to(current_url())->with('error',$this->createMessage('system.errors.msg_save_no','danger'));
			}
		}
		
		$load=$this->model_Collections->find($record);
		
		$type=TRUE;
		if (!is_array($load))
		{
			$data=$this->model_Warehouse_Orders->filtered(['reference'=>$record])->first();
			$type=FALSE;
			$load=['reference'=>$record['reference']];
		}else
		{
			$record=$load['reference'];
		}
		
		if (!is_array($load))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}
		if ($load['status']==2 || $load['status']=='2')
		{
			
		}
		$error=null;
		if ($this->request->getMethod()=='post')
		{
			$post=$this->request->getPost();
			if (!array_key_exists('lcfg', $post))
			{
				$error=$this->createMessage('system.warehouse.stackplan_save_error','danger');
			}

			if ($type)
			{
				$post['lid']=$load['lid'];
				$load['lcfg']=$post['lcfg'];
				$post=$this->model_Collections->save($post);
			}else
			{
				$post['ocfg']=$post['lcfg'];
				$post['oid']=$data['oid'];
				$load['lcfg']=$post['lcfg'];
				$post=$this->model_Warehouse_Orders->save($post);
			}
			
			if ($post)
			{
				$error=$this->createMessage('system.general.msg_save_ok','success');
			}else
			{
				$error=$this->createMessage('system.warehouse.stackplan_save_error','danger');
			}
		}
		
		$data=$this->model_Items->getLoadPalletsForStackPlan($load['reference'],$type ? 'load' : 'corder',TRUE);
		
		$data[1]['width']='1000';
		$data[1]['iseur']='0';
		return $this->view->setFile('Warehouse/Loads/stacking_creator')
							  ->addData('data',$data)
							  ->addData('load',$load)
							  ->addData('form_action',current_url())
							  ->addData('error',$error)
							  ->addBreadcrumb(lang('system.warehouse.mainmenu_collections'),url($this))
							  ->addBreadcrumb(lang('system.warehouse.collections_stackingplan'),current_url())
							  ->addBreadcrumb($record,$refurl)
							  ->setPageTitle('system.warehouse.collections_stackingplantitle',$record)
							  ->addDataTableScript()
							  ->addPDFMakeScript()
							  ->render();
	}
	
	function stackingplan($record=null,$mode='html')
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this) : base64url_decode($refurl);
		
		$data=$this->model_Collections->find($record);
		
		$type=TRUE;
		if (!is_array($data))
		{
			$data=$this->model_Warehouse_Orders->filtered(['reference'=>$record])->first();
			$type=FALSE;
		}else
		{
			$record=$data['reference'];
		}
		
		if (!is_array($data))
		{
			return $mode!='html' ? null : redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collections_iderror','danger'));
		}
		
		$dimm=[];
		if (array_key_exists('lid', $data))
		{
			$orders=$this->model_Items->getOrdersFromLoad($data['reference']);
			$orders=$this->model_Warehouse_Orders->whereIn('reference',$orders)->find();
			foreach (is_array($orders) ? $orders : [] as $value) 
			{
				if (array_key_exists('ocfg', $value) && strlen($value['ocfg']) > 0)
				{
					$value['ocfg']=json_decode($value['ocfg'],TRUE);
					$dimm=array_merge($dimm,$value['ocfg']);
				}
			}
		}
		else
		if (array_key_exists('ocfg', $data) && strlen($data['ocfg']) > 0)
		{
			$dimm=json_decode($data['ocfg'],TRUE);
		}
		
		return $this->view
					->setFile('Warehouse/Loads/stacking_viewer')
					->addData('data',$data)
					->addData('stackingdata',$this->model_Items->getLoadPalletsForStackPlan($data['reference'],$type ? 'load' : 'corder'))
					->addDataTableScript()
					->addBreadcrumb(lang('system.warehouse.mainmenu_collections'),url($this))
					->addBreadcrumb(lang('system.warehouse.collections_stackingplan'),current_url())
					->addBreadcrumb($data['reference'],'/')
					->render();
	}
	
	function locations()
	{
		$this->setTableView()
			 ->setData('location',null,FALSE,'name')
			 ->setPageTitle('system.warehouse.locations_page')
			 ->addFilters('locations')
			 ->addFilterField('zone %')
			 ->addFilterField('|| code %')
			 
			 ->addBreadcrumb('system.warehouse.mainmenu_locations',url($this))
			 
			 ->addColumn('system.warehouse.locations_code','code',TRUE)
			 ->addColumn('system.warehouse.locations_zone','zone',TRUE)
			 ->addColumn('system.warehouse.locations_row','row')
			 ->addColumn('system.warehouse.locations_column','column')
			 ->addColumn('system.warehouse.locations_size_width','width')
			 ->addColumn('system.warehouse.locations_size_length','length')
			 ->addColumn('system.warehouse.locations_level','height')
			 ->addColumn('system.warehouse.locations_access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 ->addColumn('system.warehouse.locations_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addEditButton('system.warehouse.locations_editbtn','location',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('location/new')
			  ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function location($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'locations') : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to(url($this,'locations'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Location->allowedFields, array_fill(0, count($this->model_Location->allowedFields), ''));
			$record[$this->model_Location->primaryKey]='';
		}else
		{
			$record=$this->model_Location->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		if (!array_key_exists($this->model_Location->primaryKey, $record))
		{
			$record[$this->model_Location->primaryKey]='';
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.location_id_error','danger'));
		}
		
		$ro=!$this->auth->hasAccess(AccessLevel::delete);
		
		$this->setFormView()
					->setFormTitle('{0}',[$record['code']])
					->setPageTitle('system.warehouse.location_page');
		if (!$this->isMobile())
		{
			$this->view->setFormAction($this,'save',['location'],['refurl'=>current_url(FALSE,TRUE)]);
		}
					
		$this->view->setFormCancelUrl($refurl)
				   ->setFormArgs([],['lid'=>$record['lid'],'refurl_ok'=>url($this,'locations')])
				   
				   ->addBreadcrumb('system.warehouse.mainmenu_locations',url($this,'locations'))
				   ->addBreadcrumb($record['code'],'/')
				   
				   ->addInputField('system.warehouse.locations_zone','zone',$record['zone'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>150])
				   ->addInputField('system.warehouse.locations_code','code',$record['code'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>25])
				   ->addInputField('system.warehouse.locations_row','row',$record['row'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>50])  
				   ->addInputField('system.warehouse.locations_column','column',$record['column'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>50])
				   ->addNumberField('system.warehouse.locations_size_width',$record['width'],'width',2000,10,$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true'])
				   ->addNumberField('system.warehouse.locations_size_length',$record['length'],'length',2000,10,$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true'])
				   ->addNumberField('system.warehouse.locations_level',$record['height'],'height',2000,0,$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true'])
				   ->addYesNoField('system.warehouse.locations_enabled',$record['enabled'],'enabled',$ro ? ['disabled'=>TRUE,'class'=>'bg-light'] : [])
				   ->addAcccessField('system.warehouse.locations_access',$record['access'],'access',[],$ro ? ['disabled'=>TRUE,'class'=>'bg-light'] : []);
			
			return	$this->view->render();
	}

	function label($record) 
	{
		if ($record==null)
		{
			return redirect()->to(url($this));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$record=$this->model_Location->find($record);
		$pallet_label=$this->model_Settings->get('system.pallet_label');
		$pallet_label=explode(':', $pallet_label);
		if (!$pallet_label[1]=='html')
		{
			$record['autoprint']=TRUE;
		}
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.pallet_id_error','danger'));
		}
		return redirect()->to(url('documents','template',$pallet_label,['data'=>base64url_encode(json_encode($record))]));
	}
	
	function info()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if (!$this->isMobile())
		{
			return redirect()->to(site_url())->with('error',$this->createMessage('system.errors.onlymobile_option','warning'));
		}
		
		if ($this->request->getMethod()=='post')
		{
			$post=$this->request->getPost();
			if (is_array($post) && array_key_exists('reference', $post))
			{
				$ref=$post['reference'];
				$post=$this->model_Pallet_Pallet->filtered(['reference'=>$ref])->first();
				if (is_array($post) && count($post)>0)
				{
					return redirect()->to(url('pallets','pallet',[$post['pid']],['refurl'=>current_url(FALSE,TRUE)]));
				}
				
				$post=$this->model_Warehouse_Orders->filtered(['reference'=>$ref])->first();
				if (is_array($post) && count($post)>0)
				{
					return redirect()->to(url('orders','order',[$post['oid']],['refurl'=>current_url(FALSE,TRUE)]));
				}
				
				$post=$this->model_Location->filtered(['code'=>$ref])->first();
				if (is_array($post) && count($post)>0)
				{
					return redirect()->to(url($this,'location',[$post['lid']],['refurl'=>current_url(FALSE,TRUE)]));
				}
				
				$post=$this->model_Owners_Customer->filtered(['code'=>$ref])->first();
				if (is_array($post) && count($post)>0)
				{
					return redirect()->to(url('owners','customer',[$post['cid']],['refurl'=>current_url(FALSE,TRUE)]));
				}
				
				goto error;
			}else
			{
				error:
				return redirect()->to(url($this,'info'))->with('error',$this->createMessage('system.warehouse.invalidreferror','warning'));
			}
		}
		
		return $this->setFormView('Warehouse/info')
					->setFormTitle('')
					->setPageTitle('system.warehouse.info')
					->setFormAction($this,'info',[],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($refurl)
					->setFormArgs([],['refurl_ok'=>$refurl])
					->addInputField('system.warehouse.info_reference','reference')
					->addData('_formview_custom',FALSE)
					->render();	
	}
	
	/*savetodb*/
	function save($type,$post=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$post=$post==null ? $this->request->getPost() : $post;
		
		
		if ($type=='collections')
		{
			if (array_key_exists('lid', $post) && !is_numeric($post['lid']))
			{
				unset($post['lid']);
				$post['created']=formatDate();
				$post['operator_created']=loged_user('username');
			}else
			{
				$post['operator']=loged_user('username');
			}
			
			if (!is_numeric($post['duein']))
			{
				return redirect()->to($refurl)->with('_postdata',$post)->with('error',$this->createMessage('system.warehouse.collections_dueinerror','danger'));
			}
			
			if (array_key_exists('status', $post) && $post['status']==2 && $this->model_Items->getQtyOfNotDispatchedItems($post['reference']) > 0)
			{
				return redirect()->to($refurl)->with('_postdata',$post)->with('error',$this->createMessage('system.warehouse.collections_comperror','warning'));
			}else
			{
				$post['loaded']=formatDate();	
			}
			
			if ($this->model_Collections->save($post))
			{
				if (!array_key_exists('lid', $post))
				{
					$this->model_Tasks_Notification->addForMobile(lang('system.warehouse.collections_newnotifymsg',[$post['reference']]));
				}
				
				$this->addMovementsFromArray($post);
				
				if (array_key_exists('removedJobs', $post))
				{
					//$post['removedJobs']=explode(';', substr($post['removedJobs'],1));
					$this->model_Items->whereIn('orderref',$post['removedJobs'])->delete();
				}
				
				if (array_key_exists('changePalletsStatus', $post) && ($post['changePalletsStatus']==1 || $post['changePalletsStatus']=='1'))
				{
					$this->model_Items->set(['status'=>$post['status'],'operator'=>loged_user('username'),'assign'=>formatDate()])->where('loadref',$post['reference'])->update();
				}
				
				if ($post['status']==2)
				{
					$this->model_Tasks_Rule->actionRuleByTrigger('Load_complete',[$post]);
					$this->model_Collections->changeLoadStatus($post['reference'],2,TRUE);
				}else
				{
					$this->model_Tasks_Rule->actionRuleByTrigger('Load_save',[$post]);
				}
				
				if (array_key_exists('stackinpallets', $post) && is_array($post['stackinpallets']) && count($post['stackinpallets']) > 0)
				{
					$this->model_Pallet_Pallet->updateMany($post['stackinpallets'],'reference');
				}
				
				if ($post['status']!=2 && array_key_exists('pallets', $post) && is_array($post['pallets']) && count($post['pallets']) > 0 )
				{
					$inPrgTasks=$this->model_Items->getForForm('pallref',null,FALSE,null,['loadref'=>$post['reference'],'status!='=>0]);
					
					$data=[];
					foreach ($post['pallets'] as  $job) 
					{
						foreach ($this->model_Pallet_Pallet->filtered(['enabled'=>1,'corder'=>$job,'status > '=>-1])->find() as $key => $value) 
						{
							if (!array_key_exists($value['reference'], $inPrgTasks))
							{
								$data[]=
								[
									'loadref'=>$post['reference'],
									'pallref'=>$value['reference'],
									'orderref'=>$job,
									'status'=>$post['status'],
									'location'=>$post['location'],
									'created_operator'=>loged_user('username'),
									'access'=>$post['access'],
									'enabled'=>$post['enabled']
								];
							}
						}
						
					}
                                        if (count($data)>0)
                                        {
                                          $this->model_model_Items->insertBatch($data);  
                                        }
					
				}
				
				if (array_key_exists('status', $post) && $post['status']==2)
				{
					$this->model_Collections->dispatchPallets($post['reference']);
				}
				
				return redirect()->to(array_key_exists('refurl_ok', $post) ? $post['refurl_ok'] : $refurl)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
			}else
			{
				return redirect()->to($refurl)->with('_postdata',$post)->with('error',$this->model_Collections->errors());
			}
		}else
		{
			return parent::save($type,$post);
		}
	}

	public function delete(array $post=[])
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$model=$this->request->getGet('model');
		if ($model=='items' && is_numeric($this->request->getGet('id')))
		{
			$id=$this->request->getGet('id');
			$id=$this->model_Items->find($id);
			if ($id['status']==0)
			{
				return parent::delete(['model'=>$model,'iid'=>[$id['iid']]]);
			}
			return redirect()->to($refurl)->with('error',$this->createMessage('system.warehouse.collection_taskdelerror','warning'));
		}
		return parent::delete();
	}
}