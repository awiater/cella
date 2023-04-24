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
use \CELLA\Helpers\UserInterface;
use \CELLA\Helpers\Arrays as Arr;

class Scheduler extends BaseController
{
	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = ['scheduler'];
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'			=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'jobs'=>'Warehouse/Orders',
		'cards'=>'Scheduler/Card',
		'boards'=>'Scheduler/Board',
		'custom'=>'Settings/CustomFields',
		'types'=>'Settings/CustomFieldsTypes',
		'doc'=>'Documents/Document'
	];
	
	
	function index()
	{
		return $this->boards($this->model_Settings->get('scheduler.boardname'));
	}
	
	function boards($board,$dateFrom=null)
	{
		$dateFrom=$dateFrom==null ? formatDate('now','startOfWeek') : $dateFrom;
		if (formatDate($dateFrom,'dayofWeek')==1)
		{
			$dateFrom=formatDate($dateFrom,'+ 1 days');
		}
		$boardnames=$this->model_Boards->getBoardsNamesForForm();
		$settings=$this->model_Settings->get('scheduler.*');
		$board=$this->model_Boards->find($board);
		if (!is_array($board))
		{
			return $this->setErrorView('scheduler.errors.errorboardid',FALSE)
						->addBreadcrumb('scheduler.boards.mainmenu',url($this))
						->render();
		}
		if ($board['usedate'])
		{
			$dateTo=formatDate($dateFrom,'+ '.$board['weekdays'].' days');
		}else
		{
			$dateTo=formatDate($dateFrom,'+ 1 year');
			$dateFrom=formatDate($dateFrom,'- 1 year');
		}
		
		$board['data']=$this->model_Cards->getAllCards($dateFrom,$dateTo,$board['usedate']==0);
		
		$board['weekdays']=$board['weekdays']*$board['perdaycol'];
		$board['colwidth']='100px';//100/$board['weekdays'];
		$board['colcfg']=json_decode($board['colcfg'],TRUE);
		$board['colcfg']=is_array($board['colcfg']) ? $board['colcfg'] : [];
		$board['view']=json_decode(base64_decode($board['view']),TRUE);
		
		$viewFile='Scheduler/board_default';
		$viewFile='Scheduler/boards/board_table';
		//dump($board);exit;
		$this->view->setFile($board['view_file'])//
				   ->addData('board',$board)
				   ->addData('weekdays_names',lang('scheduler.boards.weekdays'))
				   ->addData('urlprev',url($this,'boards',[$board['sbid'],formatDate($dateFrom,'- 7 days')]))
			       ->addData('urlnxt',url($this,'boards',[$board['sbid'],formatDate($dateFrom,'+ 7 days')]))
			       ->addData('urlnow',url($this,'boards',[$board['sbid'],formatDate('now','startOfWeek')]))
				   ->addData('urlboard',url($this,'boards',['-board-',formatDate('now','startOfWeek')]))
				   ->addData('urlremovecard',url($this,'delinkjobs',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('dateFrom',$dateFrom)
				   ->addData('dateTo',$dateTo)
				   ->addData('views',$boardnames)
				   ->addData('changeorderurl',url($this,'save',['custom'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('invoiceurl',url($this,'invoice',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('viewmode',$this->request->getGet('viewmode'))
				   ->addData('settings',$settings)
				   ->addData('cardstpls',$this->model_Cards->getCardsTemplates())
				   //Breadcrumbs
				   ->addBreadcrumb('scheduler.boards.mainmenu',url($this))
				   ->addBreadcrumb($boardnames[$board['sbid']],current_url());
		return $this->view->render();
	}
	
	private function predel_edit($record)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'predelivery') : $refurl;
	
		if ($record==null)
		{
			return redirect()->to(url($this,'heights'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$isnew=TRUE;
		if ($record=='new')
		{
			$record=array_combine($this->model_Jobs->allowedFields, array_fill(0, count($this->model_Jobs->allowedFields), ''));
			$record[$this->model_Jobs->primaryKey]='';
		}else
		{
			$record=$this->model_Jobs->find($record);
			$record['attached']=$this->model_Settings_CustomFields->getFields('order',$record['oid'],'attched_paperwork');
			$record['attached']=is_array($record['attached']) && count($record['attached'])>0 && array_key_exists('value', $record['attached'][0]) && strlen($record['attached'][0]['value'])> 0 ? $record['attached'][0]['value'] : null;
			$record['attached']=json_decode($record['attached'],TRUE);
			$record['attached']=is_array($record['attached']) ? array_values($record['attached']) : null;
			$isnew=FALSE;
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'heights'))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		
		if (array_key_exists('ocfg', $record))
		{
			if (!is_array($record['ocfg']))
			{
				$record['ocfg']=json_decode($record['ocfg'],TRUE);
			}
		}
		
		$params=$this->model_Settings->get('orders.orders_*');
		
		return $this->setFormView('Scheduler/predel_edit')
					->setFormTitle('scheduler.predel.editpage',[$record['reference']])
					->setPageTitle('scheduler.predel.editpage')
					->setFormAction($this,'save',['jobs'],['refurl'=>$refurl])
					->setFormCancelUrl(base64url_decode($refurl))//$this,'predelivery')
					->setFormArgs([],['oid'=>$record['oid'],'refurl_ok'=>$refurl])
					
					->addBreadcrumb('scheduler.predel.mainmenu',url($this,'predelivery'))
					->addBreadcrumb($record[$this->model_Jobs->primaryKey]==null ? lang('scheduler.predel.new') : $record[$this->model_Jobs->primaryKey],site_url())
					
					->setCustomViewEnable(FALSE)
					->addDatePicker('scheduler.predel.duein','duein',$record['duein'],!$isnew ? ['readonly'=>TRUE] : ["required"=>1,'minDate'=>'new Date()','id'=>'id_duein']) 
					->addDropDownField('scheduler.predel.owner','owner',$this->model_Owners_Supplier->getForForm('code','name',TRUE),$record['owner'],['required'=>'true'])
					->addInputField('scheduler.predel.reference','reference',$record['reference'],!$isnew ? ['readonly'=>TRUE]:['required'=>'true'])
					->addNumberField('scheduler.predel.pallets_qty',$isnew ? null : $record['ocfg']['pallqty'],'ocfg[pallqty]',100,1, ['required'=>'true'])
					->addNumberField('scheduler.predel.box_qty',$isnew ? 0 : $record['ocfg']['boxqty']	,'ocfg[boxqty]',1000,0,[])
					->addDropDownField('system.orders.supplier_status','status',$this->model_Cards->getDeliveryStatuses($record['type'],$isnew ? 2 :'0.9'),$record['status'],/*$isnew ? ['type'=>'hidden','selectwithicons'=>1] :*/['selectwithicons'=>1])
					//->addCustomFields($this->model_Settings_CustomFields->getFields('order',$record['oid'],'attched_paperwork'))
					
					->addHiddenField('created',formatDate())
					->addHiddenField('type',0)
					->addHiddenField('operator',loged_user('username'))
					->addHiddenField('enabled',0)
					->addHiddenField('access',$params['orders_access_def'])   
					->addHiddenField('location',$params['orders_location_def_rec']) 
					
					->addData('checkingform',$this->predeliverycheckinform($record['status']=='0.1'?'0.99':'0.8'))
					->addData('record',$record)
					->render();
	}
	
	function predelivery($record=null)
	{
		if ($record!=null)
		{
			return $this->predel_edit($record);
		}
		$this->setTableView('Scheduler/predel_index')
			 ->setCustomViewEnable(FALSE)
			 ->setData('jobs',null,TRUE,null,['type'=>0,'enabled'=>0,'status >='=>0])
			 ->setPageTitle('scheduler.predel.page')
			 //->addFilters('heights')
			 //->addFilterField('code %')
			 ->addColumn('scheduler.predel.reference','reference',TRUE)
			 ->addColumn('scheduler.predel.owner','owner',FALSE,$this->model_Owners_Supplier->getForForm('code','name'))
			 ->addColumn('scheduler.predel.duein','duein',FALSE,[],'d M Y')
			 
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('predelivery/new')
			 ->addEditButton('scheduler.predel.editbtn','predelivery',null,'btn-primary','fa fa-edit')
			 ->addEditButton('scheduler.predel.checkbtn',null,null,'btn-success btnCheck','fas fa-clipboard-check',[],AccessLevel::view)
			 
			 ->addBreadcrumb('scheduler.predel.mainmenu',url($this))
			 ->addData('red_alert',formatDate('now',TRUE,'d M Y'))
			 ->addData('checkingform',$this->predeliverycheckinform())
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function predeliverycheckinform($minStatus='0.8')
	{
		$form=new Pages\FormView(null,TRUE);
		$form->addDropDownField('system.orders.supplier_status','status',$this->model_Cards->getDeliveryStatuses('orders_status_types_rec',2,$minStatus),1,['selectwithicons'=>1,'id'=>'id_prestatus']);
		$form->addHiddenField('job','AA',['id'=>'id_jobid']);
		$form->addDropDownField('scheduler.predel.username','username',$this->model_Auth_User->getForForm('username','name'),null,[]);
		//$form->addInputField('scheduler.predel.pass','password',null,['type'=>'password']);
		$form->setFormAction($this,'predeliverycheckin',[],['refurl'=>current_url(FALSE,TRUE)]);
		$form->setFormArgs(['id'=>'checkingform'],[]);
		$data=[];
		return view('Scheduler/predel_checkingform',$form->getViewData());
	}
	
	function predeliverycheckin()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$data=$this->request->getPost();
		
		if (!Arr::KeysExists(['username'/*,'password'*/,'job'],$data))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.erroruserpassjob','danger'));
		}
		/*
		if (!$this->auth->checkCredentials(base64_decode($data['username']),$data['password']))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.erroruserpassjob','danger'));
		}*/
		$post=$this->model_Jobs->find($data['job']);
		if (!is_array($post))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.errordeljobcode','danger'));
		}
		$post['ocfg']=json_decode($post['ocfg'],TRUE);
		$post['pallets_qty']=$post['ocfg']['pallqty'];
		$post['ocfg']=null;
		$post['enabled']=1;
		$post['status']=$data['status'];
		if ($post['status']==null || ($post['status']!=null && strlen($post['status']) < 1))
		{
			$post['status']='1';
		}
		$status=$this->model_Cards->getDeliveryStatuses('orders_status_types_rec',2,'0.8');
		
		$info=array_key_exists($post['status'], $status) ? $status[$post['status']]['value'] : 'Start Receiving';
		$info=$this->addMovementHistory('receive_order',null,null,$post['reference'],$info,'orders',base64_decode($data['username']));
		
		return loadModule('Orders','autoGeneratePallets',[$post]);
	}

	function delinkjobs($id=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$post=$this->request->getPost();
		
		if ($id!=null && is_numeric($id))
		{
			$post=['oid'=>[$id]];	
		}
		
		if (!is_array($post))
		{
			errornojobsselected:
			return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.errornojobsselected','warning'));
		}
		
		if (!array_key_exists('oid', $post))
		{
			goto errornojobsselected;
		}
		
		if (!is_array($post['oid']) || (is_array($post['oid']) && count($post['oid']) < 1))
		{
			goto errornojobsselected;
		}
		$settings=$this->model_Settings->get('scheduler.*');
		$settings['avaliable_in_scheduler']=str_replace($post['oid'], '', $settings['avaliable_in_scheduler']);
		$settings['avaliable_in_scheduler']=str_replace(',,', ',', $settings['avaliable_in_scheduler']);
		$this->model_Settings->write('avaliable_in_scheduler',$settings['avaliable_in_scheduler']);
		$post=$this->model_Jobs->whereIn('oid',$post['oid'])->find();
		$orders=[];
		foreach (is_array($post) ? $post : [] as  $value) 
		{
			$orders[]=$value['reference'];
		}
		$this->model_Custom->whereIn('targetid',$orders)->where('type',$settings['linked_orders'])->delete();
		return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.jobsremoved','success'));
	}
	
	function linkjobs()
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$post=$this->request->getPost();
		//$post=['oid'=>[174,143]];
		if (!is_array($post))
		{
			errornojobsselected:
			return redirect()->to($refurl)->with('error',$this->createMessage('scheduler.errors.errornojobsselected','warning'));
		}
		
		if (!array_key_exists('oid', $post))
		{
			goto errornojobsselected;
		}
		
		if (!is_array($post['oid']) || (is_array($post['oid']) && count($post['oid']) < 1))
		{
			goto errornojobsselected;
		}
		
		$delivery=$this->model_Jobs->filtered(['status >='=>0,'status <'=>2])->find();
		foreach ($delivery as $key => $value) 
		{
			$delivery[$value['oid']]=$value;
			unset($delivery[$key]);
		}
		//$post=$this->model_Cards->getLinkedJobs($post['oid']);
		$post=$this->model_Jobs->whereIn('oid',$post['oid'])->find();
		//dump($delivery);exit;
		return $this->setFormView('Scheduler/linkjobs')
					->setFormTitle('scheduler.orders.setformtitle')
					->setPageTitle('scheduler.orders.setformtitle')
					->setFormAction($this,'save',['links'],['refurl'=>base64url_encode($refurl)])
					->setFormCancelUrl($refurl)//$this,'predelivery')
					
					->addBreadcrumb('system.orders.index_bread',$refurl)
					->addBreadcrumb('scheduler.orders.orderbread',current_url(FALSE,FALSE))

					->addData('orders',$post)
					->addData('delivery',$delivery)
					->addData('status',$this->model_Jobs->getOrderStatusList(null))
					->addData('linked',$this->model_Cards->getLinkedJobs())
					->render();
	}
	
	function changejoblocation($job,$location)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$job=$this->model_Jobs->where('reference',$job)->first();
		if (is_array($job))
		{
			$job['location']=$location;
			$job['refurl_ok']=$refurl;
			return $this->save('jobs',$job);
		}
		return redirect()->to($refurl);
	}
	
	function invoice($id)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$item=$this->model_Custom->filtered(['targetid'=>$id])->first();
		if (!is_array($item))
		{
			$item=[];
		}
		$item['value']='in';
		$item['targetid']=$id;
		$item['target']='order';
		$item['type']=$this->model_Settings->get('scheduler.invoiced_order');
		return parent::save('custom',$item);
	}
	
	function cardedit($record)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		if ($record==null)
		{
			return redirect()->to(url($this));
		}
		$id=$record;
		$type=$this->request->getGet('type');
		$type=$type==null ? 'FILE' : $type;
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$file='';
		if ($record=='new')
		{
			$record=array_combine($this->model_Doc->allowedFields, array_fill(0, count($this->model_Doc->allowedFields), ''));
			$record[$this->model_Doc->primaryKey]='';
			$record['type']='SCHCARD';
			$record['path']=$type=='TPLS' ? '@views/Scheduler/c/%name%.php' : '';
			$record['access']=$this->model_Settings->get('system.def_access_level');
			$record['enabled']=1;
		}else
		{
			$record=$this->model_Doc->find($record);
			$record['path']=parsePath($record['path'],TRUE);
			if (file_exists($record['path']))
			{
				$file=file_get_contents($record['path']);
			}else
			{
				$file='';
			}
			
			$type=$record['type'];
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		
		return $this->setFormView('Documents/edit',FALSE)
					->setFormTitle('scheduler.cards.tpledit_title')
                    ->setPageTitle('scheduler.cards.tpledit_title')
					->setFormAction($this,'save',['doc'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl('settings')
					->setFormArgs(['class'=>'body-full'],['did'=>$record['did'],'refurl_ok'=>$refurl])
					
					->addBreadcrumb($type=='TPLS' ? 'system.documents.tpls_breadindex' : 'index',$type=='TPLS' ? url($this,'templates') : '/')
					->addBreadcrumb($record['name'],'/')
					
					->addInputField('system.documents.tpls_name','name',$record['name'],['required'=>'true','maxlength'=>50])
					->addHiddenField('type',$record['type'])
					->addHiddenField('path',$record['path'])
					->addHiddenField('access',$record['access'])
					->addHiddenField('enabled',$record['enabled'])
					->addTextAreaField('system.documents.tpls_desc','desc',$record['desc'],['rows'=>'2','cols'=>'3'])
					->addHiddenField('dataact','')
					->addHiddenField('orientation',1)
					->addCodeEditor('system.documents.tpls_file','text',$record['text'],[])
					->addData('_formview_card_class','body-full')
					->render();
	}
	
	function save($model,$post=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		$post=$post==null ? $this->request->getPost(): $post;
		//dump($post);exit;
		if ($model=='jobs')
		{
			if (array_key_exists('ocfg', $post) && is_array($post['ocfg']))
			{
				$post['ocfg']=json_encode($post['ocfg']);
			}
			if (array_key_exists('status', $post) && !is_numeric($post['status']))
			{
				$post['status']=0;
			}else
			if (!array_key_exists('status', $post))
			{
				$post['status']=0;
			}
			
			$this->uploadFiles($post);
			
			if (array_key_exists('attached', $post) && array_key_exists('oid', $post) )
			{
				$post['customfields']=$this->model_Settings_CustomFields->getFields('order',$post['oid'],'attched_paperwork');
				$post['customfields'][0]['value']=$post['attached'];
				$post['customfields'][0]['targetid']=$post['oid'];
				$post['status']='0.1';
			}
			
			if (array_key_exists('status', $post) && $post['status']>=1)
			{
				$post['ocfg']=json_decode($post['ocfg'],TRUE);;
				$post['enabled']=1;
				$post['pallets_qty']=$post['ocfg']['pallqty'];
				$post['ocfg']='';
				if ($post['status']==2)
				{
					$post['completed']=formatDate();
				}
				return loadModule('Orders','autoGeneratePallets',[$post]);
			}
		}else
		if ($model=='links')
		{
			if (array_key_exists('remove', $post))
			{
				$this->model_Custom->whereIn('cfid',$post['remove'])->delete();
			}
			
			if (array_key_exists('jobs', $post) && is_array($post['jobs']))
			{
				$post['jobs'][]=$this->model_Settings->get('scheduler.avaliable_in_scheduler');
				$this->model_Settings->write('avaliable_in_scheduler',implode(',',$post['jobs']));
			}
			
			if (array_key_exists('orders', $post))
			{
				$orders=[];
				$arr=[];
				$type=$this->model_Types->where('name','linked_orders')->first();
				if (is_array($type) && array_key_exists('cftid', $type))
				{
					$type=$type['cftid'];
				}else
				{
					$type=1;
				}
				$inserts=[];
				$updates=[];
				foreach ($post['orders'] as $key => $value) 
				{
					$value['type']=$type;
					$value['target']='order';
					if (array_key_exists('cfid', $value))
					{
						$updates[]=$value;
					}else
					{
						$inserts[]=$value;
					}
					
				}
				if (count($inserts) >0)
				{
					$this->model_Custom->insertBatch($inserts);
				}
				if (count($updates) >0)
				{
					$this->model_Custom->updateBatch($updates, 'cfid');
				}
			}
			return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_save_ok','success'));
		}
		if ($model=='custom')
		{
			$item=$this->model_Custom->filtered(['targetid'=>$post['id']])->first();
			if (!is_array($item))
			{
				$item=$this->model_Settings_CustomFieldsTypes->filtered(['name'=>'flag'])->first();
			}
		
			if (!is_array($item))
			{
				$post=['value'=>'','targetid'=>'','target'=>'order'];
			}
		
			$item['value']='in';
			$item['targetid']=$post['id'];
			$post=$item;
		}
		return parent::save($model,$post);
	}

	function getsettingstab()
	{
		$settings=$this->model_Settings->get('scheduler.*');
		$settings_orders=$this->model_Settings->get('orders.*');
		$settings_orders=Arr::fromFlatten($settings_orders['orders_status_types_rec'])+Arr::fromFlatten($settings_orders['orders_status_types_cus']);
		$boards=$this->model_Boards->getForForm();
		$dateControl=
		[	
			base64_encode(json_encode(['label'=>'Week $dateTo','format'=>'W'])),
			base64_encode(json_encode(['label'=>'$dateFrom - $dateTo','format'=>'dS M']))
		];
		$this->setFormView()
			 ->addDropDownField('scheduler.settings.boardname','settings[boardname]',$boards,$settings['boardname'],[])
			 ->addNumberField('scheduler.settings.autorefresh',$settings['autorefresh'],'settings[autorefresh]',1200,0,[])
			 ->addYesNoField('scheduler.settings.show_expired',$settings['show_expired'],'settings[show_expired]',[])
			 
			 ->addInputField('scheduler.settings.name','name',null,[])
			 ->addNumberField('scheduler.settings.weekdays',null,'weekdays',7,1,[])
			 ->addNumberField('scheduler.settings.rowqty',null,'rowqty',100,5,[])
			 ->addNumberField('scheduler.settings.cardheight',null,'cardheight',700,10,[])
			 ->addYesNoField('scheduler.settings.usedate',0,'usedate',[])
			 
			 ->addYesNoField('scheduler.settings.enabled',1)
			 ->addAcccessField('scheduler.settings.access',null)
			 ->addDropDownField('scheduler.settings.view_file','view_file',$this->model_Boards->getBoardsViewNames(),null,[])
			 ->addDropDownField('scheduler.settings.view','view',array_combine($dateControl, lang('scheduler.settings.view_list')),null,[])
			 ->addTextAreaField('scheduler.settings.colcfg','colcfg',null,[])
			 ->addHiddenField('sbid',null,['id'=>'id_sbid'])
			 ->addHiddenField('refurl_ok_board',current_url(FALSE,FALSE),['id'=>'refurl_ok'])
			 ->addData('boardsnames',$boards)
			 ->addData('boardsdata',$this->model_Boards->findAll())
			 ->addData('boardSaveUrl',url($this,'save',['boards'],['refurl'=>current_url(FALSE,TRUE)]))
			 ->addData('boardDelUrl',url($this,'delete',[],['refurl'=>current_url(FALSE,TRUE)]))
			 ->addData('defaultboard',base64_encode(json_encode($this->model_Boards->getDefault())))
			 ->addData('settings_colors',$this->model_Cards->getCardColors($settings['orders_status_types_colors']))
			 ->addData('settings_orders',$settings_orders)
			 ->addData('orders_status_types_colors',$settings['orders_status_types_colors'])
			 ->addData('cardstpls',$this->model_Cards->getCardsTemplates(TRUE))
			 ->addData('maincfgfieldsqty',3);
		return ['view'=>'Scheduler/settings','data'=>
		[
			'data'=>$this->view->getViewData()
		]];
	}
}
		