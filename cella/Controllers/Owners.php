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

class Owners extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'		=>AccessLevel::delete,
		'supplier'	=>AccessLevel::delete,
		'supplier'	=>AccessLevel::delete,
		'customers'	=>AccessLevel::delete,
		'customer'	=>AccessLevel::delete,
		'api'		=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'supplier'=>'Owners/Supplier',
		'customer'=>'Owners/Customer',
	];
	
	function index()
	{
		return redirect()->to(url($this,'suppliers'));
	}
	
	function suppliers() 
	{
		$this->setTableView()
			 ->setData('supplier',null,TRUE)
			 ->setPageTitle('system.owners.suppliers_page')
			 ->addFilters('suppliers')
			 ->addFilterField('name %')
			 ->addFilterField('|| code %')
			 ->addColumn('system.owners.supplier_name','name',TRUE)
			 ->addColumn('system.owners.supplier_code','code')
			 ->addColumn('system.owners.supplier_addr','address',FALSE,[],'len:120')
			 ->addColumn('system.owners.supplier_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addEditButton('system.owners.supplier_editbtn','supplier',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('supplier/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function supplier($record=null)
	{
		if ($record==null)
		{
			return redirect()->to(url($this,'suppliers'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Supplier->allowedFields, array_fill(0, count($this->model_Supplier->allowedFields), ''));
			$record[$this->model_Supplier->primaryKey]='';
		}else
		{
			$record=$this->model_Supplier->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		if (!array_key_exists($this->model_Supplier->primaryKey, $record))
		{
			$record[$this->model_Supplier->primaryKey]='';
		}
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this,'suppliers'))->with('error',$this->createMessage('system.owners.supplier_id_error','danger'));
		}
		return $this->setFormView()
					->setFormTitle('{0}',[$record['name']])
					->setPageTitle('system.owners.supplier_page')
					->setFormAction($this,'save',['supplier'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,'suppliers')
					->setFormArgs([],['sid'=>$record['sid'],'refurl_ok'=>url($this,'suppliers')])
					
					->addInputField('system.owners.supplier_name','name',$record['name'],['required'=>'true','maxlength'=>150])
					->addInputField('system.owners.supplier_code','code',$record['code'],['required'=>'true','maxlength'=>25])
					->addYesNoField('system.owners.supplier_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addInputField('system.owners.customer_ordref','ordref',$record['ordref'],['required'=>'true','maxlength'=>120])
					->addTextAreaField('system.owners.supplier_addr','address',$record['address'],[])
					->addCustomFields($this->model_Settings_CustomFields->getFields('supplier',$record['sid']))
					->render();
	}

	
	function customers() 
	{
		$this->setTableView()
			 ->setData('customer',null,FALSE)
			 ->setPageTitle('system.owners.customers_page')
			 ->addFilters('customers')
			 ->addFilterField('name %')
			 ->addFilterField('|| code %')
			 ->addColumn('system.owners.customer_name','name',TRUE)
			 ->addColumn('system.owners.customer_code','code')
			 ->addColumn('system.owners.customer_addr','address',FALSE,[],'len:120')
			 ->addColumn('system.owners.customer_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addEditButton('system.owners.customer_editbtn','customer',null,'btn-primary','fa fa-edit')
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('customer/new')
			 ->setAsDataTable(['"pageLength"'=>$this->model_Settings->get('system.tables_rows_per_page')]);
		return $this->view->render();
	}
	
	function customer($record=null)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? url($this,'customers') : base64url_decode($refurl);
		
		if ($record==null)
		{
			return redirect()->to(url($this,'customers'));
		}
		$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		if ($record=='new')
		{
			$record=array_combine($this->model_Customer->allowedFields, array_fill(0, count($this->model_Customer->allowedFields), ''));
			$record[$this->model_Customer->primaryKey]='';
		}else
		{
			$record=$this->model_Customer->find($record);
		}
		
		$record=$this->getFlashData('_postdata',$record);
		if (!array_key_exists($this->model_Customer->primaryKey, $record))
		{
			$record[$this->model_Customer->primaryKey]='';
		}

		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to($refurl)->with('error',$this->createMessage('system.owners.customer_id_error','danger'));
		}

		$ro=!$this->auth->hasAccess($this->access['customer']);
		
		$this->setFormView()
					->setFormTitle('{0}',[$record['name']])
					->setPageTitle('system.owners.customer_page');
		if (!$ro)
		{
			$this->view->setFormAction($this,'save',['customer'],['refurl'=>current_url(FALSE,TRUE)]);
		}
					
		
		$this->view->setFormCancelUrl($refurl)
					->setFormArgs([],[$this->model_Customer->primaryKey=>$record[$this->model_Customer->primaryKey],'refurl_ok'=>url($this,'customers')])
					->addInputField('system.owners.customer_name','name',$record['name'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>150])
					->addInputField('system.owners.customer_code','code',$record['code'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] : ['required'=>'true','maxlength'=>25])
					->addInputField('system.owners.customer_ordref','ordref',$record['ordref'],$ro ? ['readonly'=>TRUE,'type'=>'hidden'] : ['required'=>'true','maxlength'=>120])
					->addYesNoField('system.owners.customer_iseu',$record['iseu'],'iseu',$ro ? ['disabled'=>TRUE,'class'=>'bg-light'] : ['required'=>'true'])
					->addYesNoField('system.owners.customer_enabled',$record['enabled'],'enabled',$ro ? ['disabled'=>TRUE,'class'=>'bg-light'] : ['required'=>'true'])
					->addTextAreaField('system.owners.customer_addr','address',$record['address'],$ro ? ['readonly'=>TRUE,'class'=>'bg-light'] :[]);
		if (!$ro)
		{
			$this->view->addCustomFields($this->model_Settings_CustomFields->getFields('customer',$record['cid']));
		}	
					
		return $this->view->render();
	}

	function api($method)
	{
		$data=['result'=>0];
		
		if ($method=='nextreference')
		{
			if ($this->request->getGet('customer')!=null || ($this->request->getGet('mode')=='customer' && $this->request->getGet('code')!=null))
			{
				$data=['result'=>$this->model_Customer->getNextReference($this->request->getGet($this->request->getGet('code')!=null ? 'code': 'customer'))];
			}
			else
			if ($this->request->getGet('supplier')!=null || ($this->request->getGet('mode')=='supplier' && $this->request->getGet('code')!=null))
			{
				$data=['result'=>$this->model_Supplier->getNextReference($this->request->getGet($this->request->getGet('code')!=null ? 'code': 'supplier'))];
			}
			
		}
		return $this->response->setJson($data);
	}
}