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
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\Arrays as Arr;

class Reports extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'reports'=>'Documents/Report',
		'kpi'=>'Warehouse/Movements',
	];
	
	/**
	 * Array with controller method remaps ($key is fake function name and $value is actuall function name)
	 */
	public $remaps=
	[
		'index1'=>'kpi'
	];
	
	function index()
	{
		$filters=['access'=>'@loged_user'];
		if (!$this->auth->hasAccess(AccessLevel::create))
		{
			$filters['enabled']=1;
		}
		/*if ($this->request->getGet('filtered')==null)
		{
			$filters['status <>']=$status['orders_status_comp'];
		}
		*/
		$this->setTableView()
			 ->setData('reports',null,TRUE,null,$filters)
			 ->setPageTitle('system.reports.mainmenu')
			 ->addFilters('index')
			 ->addFilterField('rname %');
		
		$access=$this->model_Settings->get('reports.reports_edit_access');
		$this->view->addColumn('system.reports.rname','rname',TRUE)
			 	   ->addColumn('system.reports.rdesc','rdesc',TRUE)
			       ->addColumn('system.reports.access','access',FALSE,$this->model_Auth_UserGroup->getForForm('ugref'))
			 	   ->addColumn('system.reports.enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
				   
				   ->addBreadcrumb('system.reports.mainmenu','/')
				   
				   ->addEditButton('system.reports.runbtn','report',null,'btn-success edtBtn','far fa-play-circle',[],AccessLevel::view)
				   ->addEditButton('system.reports.editbtn','edit',null,'btn-primary edtBtn','fa fa-edit',[],$access)
			 	   
			 	   ->addEnableButton($access)
			 	   ->addDisableButton($access)
			       ->addDeleteButton($access)
			       ->addNewButton('edit/new',$access)
				   ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]); 
			 
		return $this->view->render();
	}

	function edit($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this) : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.id_error','danger'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Reports->allowedFields, array_fill(0, count($this->model_Reports->allowedFields), ''));
			$record[$this->model_Reports->primaryKey]='';
			$record['rtype']=2;
		}else
		{
			$record=$this->model_Reports->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!array_key_exists($this->model_Reports->primaryKey, $record))
		{
			$record[$this->model_Reports->primaryKey]='';
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.id_error','danger'));
		}
		
		$this->setFormView('Reports/edit')
			 ->setCustomViewEnable(FALSE)
			 ->setFormTitle('')
			 ->setPageTitle('system.reports.edit_page')
			 ->setFormAction($this,'save',['reports'],['refurl'=>current_url(FALSE,TRUE)])
			 ->setFormCancelUrl($refurl)
		     ->setFormArgs([],['rid'=>$record['rid'],'rtype'=>1,'refurl_ok'=>$refurl])
		     
			 ->addBreadcrumb('system.reports.mainmenu',url($this))
			 ->addBreadcrumb(lang('system.reports.edit_bread').'-'.$record['rid'],'/')
			 
		     ->addInputField('system.reports.rname','rname',$record['rname'],['required'=>'true','maxlength'=>120])
			 ->addTextAreaField('system.reports.rdesc','rdesc',$record['rdesc'],['rows'=>3])
			 ->addYesNoField('system.reports.enabled',$record['enabled'],'enabled')
			 ->addAcccessField('system.reports.access',$record['access'])
			 //->addDropDownField('system.reports.rtables','rtables',$this->model_Reports->getTablesForForm(),$record['rtables'],['required'=>'true'])
			 //->addTextAreaField('system.reports.rcolumns','rcolumns',$record['rcolumns'],['required'=>'true'])
			 ->addDropDownField('system.reports.rtype','rtype',lang('system.reports.rtype_list'),$record['rtype'],[])
			 ->addTextAreaField('system.reports.rsql','rsql',$record['rsql'],['required'=>'true'])
			 ->addTextAreaField('system.reports.rfilters','rfilters',$record['rfilters'],[])
			 ->addData('intertpl','W2RhdGFfZmV0Y2hdCmNvbnRyb2xsZXI9IkNvbnRyb2xsZXIgb3IgTW9kZWwgTmFtZSIKYWN0aW9uPSJBY3Rpb24gTmFtZSIKYXJncz1AZGF0YV9mZXRjaF9hcmdzCltkYXRhX2ZldGNoX2FyZ3NdCmFyZzE9ImFyZzEgVmFsdWUi')
			 ->addData('tbltpl',base64_encode(file_get_contents(parsePath('@views/Reports/Templates/table.php',TRUE))))
			 ->addData('record',$record);
			return	$this->view->render();
	}

	function report($record)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this) : base64url_decode($refurl);
		$noform=$this->request->getGet('noform');
		$noform=$noform==null ? FALSE : $noform;
		
		$record=$this->model_Reports->find($record);
		
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.id_error','danger'));
		}
		
		if ($record['rtype']== 1)
		{
			$this->convert_legacy_report($record);	
		}
		
		$record['rsql']=parse_ini_string($record['rsql'],TRUE);
		$filters=[];
		$form=null;
		
		if (array_key_exists('filter_fields', $record['rsql']) && is_array($record['rsql']['filter_fields']) && count($record['rsql']['filter_fields'])>0)
		{
			$noform=TRUE;
			$form=$this->setFormView();	
			foreach ($record['rsql']['filter_fields'] as $key => $value) 
			{
				$form=$form->addCustomFieldFromData($value,FALSE,'reportfields[@name]');
				$value=explode('|', $value);
				$filters['@'.$value[0]]=$value[1];
			}
			$form=$form->setFormTitle('')
			 			->setPageTitle(lang('system.reports.mainmenu').' - '.$record['rname'])
			 			->setFormAction($this,'report',[$record['rid']],['refurl'=>current_url(FALSE,TRUE),'noform'=>1])
			 			->setFormCancelUrl($refurl)
		     			->setFormArgs([],[])
						->addBreadcrumb('system.reports.mainmenu',url($this))
						->addBreadcrumb($record['rid'],url($this))
						->setCustomSaveButton('system.reports.runbtn','far fa-play-circle');
			$form=['view'=>$form->render('text',FALSE),'data'=>$form->getViewData('scripts')];
		}
		
		if ($this->request->getMethod()=='post')
		{
			$post=$this->request->getPost();
			
			if (array_key_exists('reportfields', $post) && is_array($post['reportfields']) && count($post['reportfields']) > 0)
			{
				foreach ($post['reportfields'] as $key => $value) 
				{
					$filters['@'.$key]=$value;
				}
			}
			$noform=FALSE;
		}
		
		$data=[];
		if ($record['rtype']==0)
		{
			$noform=FALSE;
		}
		
		if (!$noform)
		{
			$data=$this->model_Reports->getDataForReport($record['rsql'],$filters,FALSE);
		}
		//dump($data);exit;
		
		if ((!is_array($data) || (is_array($data) && count($data) <1)) && !$noform)
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.noresultserror','info'));
		}

		$this->getReportView($data,$record['rname']);
		if (is_array($form))
		{
			$this->view->setViewData(['scripts'=>$form['data']],TRUE);
			$form=$form['view'];
		} 
		
		if ($record['rtype']==0)
		{
			$this->view->setFile('Reports/chart')
					   ->addChartObject('bar','KPI',$data,['class'=>'col-8']);
		}
		$this->view
			 ->addBreadcrumb('system.reports.mainmenu',url($this))
			 ->setCustomViewEnable(FALSE)
			 ->addBreadcrumb($record['rid'],url($this))
			 ->addData('rname',$record['rname'])
			 ->addData('showform',$noform)
			 ->addData('form',$form);
			 
		return $this->view
					->addData('exportcsv',url($this,'exportcsv',[$record['rid']]))
			 		->addData('exportpdf',url($this,'exportpdf',[$record['rid']]))
					->addData('fname',str_replace(' ', '_', strtolower($record['rname'])).'_'.formatDate())
					->addData('rname',$record['rname'].' '.formatDate('now',TRUE,'d M Y H:m'))
					->render();
	}
	
	function convert_legacy_report(&$record)
	{
		$record['rsql']=['data_fetch'=>['sql'=>'"'.$record['rsql'].'"']];
		
		if (strlen($record['rfilters']) > 0)
		{
			$record['rsql']['filter_fields']=[];
			foreach (explode(PHP_EOL,$record['rfilters'])as $key => $value) 
			{
				$record['rsql']['filter_fields']['field'.$key]="'".$value."'";//str_replace('|','@', $value);
				unset($record['rsql']['filter_fields'][$key]);
			}
		}
		//$record['rfilters']='';
		$record['rsql']=\CELLA\Helpers\Arrays::toINI($record['rsql']);
		$record['rtype']=2;
		$this->model_Reports->save($record);
	}
	
	function legacy_report($record,$refurl,$noform)
	{	
		if (!is_array($record))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.id_error','danger'));
		}
		
		if ($this->request->getMethod()=='post')
		{
			$post=$this->request->getPost();
			
			if (array_key_exists('reportfields', $post) && is_array($post['reportfields']) && count($post['reportfields']) > 0)
			{
				$data=$this->model_Reports->runReport($record['rsql'],$post['reportfields'],TRUE);
				
				goto render_data;
			}
		}
		
		$record['rfilters']=explode(PHP_EOL,$record['rfilters']);
		$view=$this->setFormView();
		if (is_array($record['rfilters']) && count($record['rfilters']) > 0 && Str::contains($record['rfilters'][0],'|') && $noform==0)
		{
			$fields=[];
			foreach ($record['rfilters'] as $value) 
			{
				$value=explode('|',$value);
				$view=$view->addCustomFieldFromData(
				[
					'value'=>$value[1],
					'name'=>$value[0],
					'cfid'=>$value[0],
					'type'=>$value[2],
					'label'=>count($value) > 3 ? $value[3] : ucwords($value[0])
				],FALSE,'reportfields[@name]');
			}
			return $view->setFormTitle('')
			 			->setPageTitle(lang('system.reports.mainmenu').' - '.$record['rname'])
			 			->setFormAction($this,'report',[$record['rid']],['refurl'=>current_url(FALSE,TRUE),'noform'=>1])
			 			->setFormCancelUrl($refurl)
		     			->setFormArgs([],[])
						->addBreadcrumb('system.reports.mainmenu',url($this))
						->addBreadcrumb($record['rid'],url($this))
						->setCustomSaveButton('system.reports.runbtn','far fa-play-circle')
			 			->render();
		}
		
		$data=$this->model_Reports->runReport($record['rsql'],[],TRUE);
		
		render_data:
		
		if (!is_array($data) || (is_array($data) && count($data) <1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.noresultserror','info'));
		}

		$this->getReportView($data,$record['rname']); 
		
		
			 
		$this->view
			 ->addBreadcrumb('system.reports.mainmenu',url($this))
			 ->setCustomViewEnable(FALSE)
			 ->addBreadcrumb($record['rid'],url($this))
			 ->addData('rname',$record['rname']);
		return $this->view
					->addData('exportcsv',url($this,'exportcsv',[$record['rid']]))
			 		->addData('exportpdf',url($this,'exportpdf',[$record['rid']]))
					->addData('fname',str_replace(' ', '_', strtolower($record['rname'])).'_'.formatDate())
					->addData('rname',$record['rname'].' '.formatDate('now',TRUE,'d M Y H:i'))
					->render();
	}
	
	private function getReportView($data,$name)
	{
		if (count($data)<1)
		{
			$this->view->setPageTitle(lang('system.reports.mainmenu').' - '.$name)
					   ->setFile('Reports/view');
		}else
		{
			$this->setTableView('Reports/view')
			 	 ->setData($data,null,FALSE)
			 	 ->addDataTableScript()
			 	 ->setPageTitle(lang('system.reports.mainmenu').' - '.$name)
			 	 ->setBorderedTable();
		
			foreach (array_keys($data[0]) as $value) 
			{
				$list=[];
				if ($value=='mhtype' || strtolower($value)=='movement type')
				{
					$list=$this->model_Settings->get('system.movement_types',TRUE);
				}
				$this->view->addColumn(ucwords($value),$value,FALSE,$list,Str::contains(strtolower($value),'date') || Str::endsWith(strtolower($value),' on') ? 'd M Y H:m' : null);
			}
		}
	}
	
	function kpi($user=null,$kpi2_field=null,$select_color='blue',$bar_color='red')
	{
		$post=$this->request->getPost();
		$users=$this->model_Auth_User->getForForm('username','name',FALSE,null,FALSE);
		
		if (is_array($post) && count($post) > 0 && Arr::KeysExists(['user','from','to'],$post))
		{
			$value['user']=$post['user'];
			$value['from']=convertDate($post['from'],'DB','Ymd');
			$value['to']=convertDate($post['to'],'DB','Ymd');
			$value['kpi2_field']=$post['kpi2_field'];
			
		}else
		{
			$value=['user'=>$user];
			$value['from']=convertDate(formatDate('now','- 1 months'),'DB','Ymd');
			$value['to']=convertDate(formatDate('now','+ 1 day'),'DB','Ymd');
			$value['kpi2_field']=$kpi2_field==null ? 99 : $kpi2_field;
		}
		
		$data=$this->model_KPI->getKPIData($value['from'],$value['to'],$value['user'],0);
		
		$kpi_1=$this->model_KPI->getMovementsTypesForKPI($data);
		$kpi_2=[];
		
		$kpi2_field=array_keys($kpi_1);
		
		if ($value['kpi2_field']==99 || $value['kpi2_field']=='99')
		{
			$value['kpi2_field']=$kpi2_field[0];
		}
		$kpi2_field=array_combine($kpi2_field, $kpi2_field);
		
		$operators=$this->model_Auth_User->getForForm('username','name',FALSE,null,FALSE);
		foreach ($operators as $key => $name) 
		{
			$kpi_2[$name]=$this->model_KPI->getMovementsTypesForKPI($this->model_KPI->getKPIData($value['from'],$value['to'],$key,0),$value['kpi2_field']);
		}
		//$kpi_1=[];
		$value['user_full']=$operators[$value['user']];
		$this->setFormView('Reports/kpi')
			 ->setFormAction(current_url(FALSE),null,[],[])
			 ->addDropDownField('system.movements.mhuser','user',$users,$value['user'],[])
			 ->addDatePicker('system.reports.kpi_datefilterfrom','from',$value['from'].'0000',[])
			 ->addDatePicker('system.reports.kpi_datefilterto','to',$value['to'].'0000',[])
			 ->addDropDownField('system.reports.kpi_kpi2_field','kpi2_field',$kpi2_field,$value['kpi2_field'],[])
			 ->addChartObject('bar','kpi_1',$kpi_1,['labels'=>array_keys($kpi_1),
			 				'legend'=>'false',
			 				'multivalue'=>FALSE,
			 				'printable'=>TRUE,
			 				'title'=>lang('system.reports.kpi_totalevents',[$value['user_full']]),
			 				'datalabels'=>TRUE,
			 				'colors'=>[$value['kpi2_field']=>$select_color],
			 				'defcolor'=>$bar_color
			 				])
			 ->addChartObject('bar','kpi_2',$kpi_2,
			 				[
			 				'legend'=>'false',
			 				'multivalue'=>FALSE,
			 				'horizontal'=>TRUE,
			 				'datalabels'=>['align'=>'right'],
			 				'title'=>lang('system.reports.kpi_usercompareevents',[$value['kpi2_field']]),
			 				'colors'=>[$value['user_full']=>$select_color],
			 				'defcolor'=>$bar_color
			 				])
			 ->addData('lastevents',$data)
			 ->addData('filters',$value)
			 ->addData('kpi_1',$kpi_1)
			 ->addBreadcrumb('system.reports.mainmenu',url($this))
			 ->addBreadcrumb('kpi',url($this));
		return $this->view->render();	
	}
	
	
	function exportcsv($record)
	{
		return $this->export('csv',$record,TRUE);
	}
	
	function exportpdf($record)
	{
		return $this->export('pdf',$record,TRUE);
	}
	
	function export($type,$record,$parse=FALSE)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this) : base64url_decode($refurl);
		
		$data=null;
		if ($parse)
		{
			$data=base64url_encode(loged_user('username').$record);
			$data=parsePath('@temp/'.$data.'.json',TRUE);
			if (!file_exists($data))
			{
				goto error;
			}
			$data=file_get_contents($data);
			$data=json_decode($data,TRUE);
		}
		$name='report';

		if (is_array($data) && count($data) > 0)
		{
			if (array_key_exists('rname', $data))
			{
				$name=$data['rname'];
				unset($data['rname']);
			}
			goto render_data;
		}
		
		$record=$this->model_Reports->find($record);
		
		if (!is_array($record))
		{
			error:
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.id_error','danger'));
		}
		
		
		$data=$this->model_Reports->runReport($record);
		$data=$data['data']->find();
		$name=$record['rname'];
		render_data:
		if (!is_array($data) || (is_array($data) && count($data) <1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.reports.noresultserror','info'));
		}
		
		if ($type=='pdf')
		{
				$table = new \CodeIgniter\View\Table();
				$table->setHeading(array_keys($data[0]));
				$dompdf = new \Dompdf\Dompdf();
				$dompdf->loadHtml($table->generate($data));
				$dompdf->setPaper('A4', 'portrait');
        		$dompdf->render();
				$dompdf->stream($name.'.pdf',['Attachment'=>FALSE]);exit(0);	
		}
		
		return loadModule('Documents','getcsv',[$data,$name,TRUE]);exit;
	}
}