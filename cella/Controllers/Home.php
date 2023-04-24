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

class Home extends BaseController
{
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'			=>AccessLevel::view,
		'dashboard'		=>AccessLevel::view,
		'logout'		=>AccessLevel::view,
		'pageNotFound'	=>AccessLevel::view,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'dash'=>'Settings/Dashboard',
	];
	
	function index()
	{
		if (loged_user('interface')==UserInterface::mobile)
		{
			return $this->mobilemenu();
		}
		return redirect()->to(url('/dashboard'));
	}
	
	function dashboard()
	{
		$this->setDashBoardView()->addData('editable',$this->auth->hasAccess($this->model_Settings->get('dashboard.dashboard_editable')));		
		$this->view->addData('_delete_tile_url',url($this,'deletetile',['-id-'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addData('_new_tile_url',url($this,'save',['dash'],['refurl'=>current_url(FALSE,TRUE)]))
				   ->addFromDB('home');					
		return $this->view->render();
	}
	
	function mobilemenu()
	{
		$notify=$this->model_Tasks_Notification->getMessagesForMobile();
		if (is_array($notify) && count($notify)>0)
		{
			$this->session->setFlashdata('error', createErrorMessage($notify));
		}
		return $this->view
				    ->setPageTitle('home')
					->setFile('Home/mobile_index')
				   	//->addData('locations_qty',$this->model_Locations->where('enabled',1)->count())
				   	->render();
	}
	
	function exception()
	{
		return redirect()->to(site_url())->with('error',$this->createMessage('System Exception/Error please contact support','danger'));
	}
	
        function lastpallets($qty=10,array $columns=[])
        {
            $columns=count($columns) < 1 ? ['reference','operator','supplier','sorder','customer'] : $columns;
            $table=$this->model_Pallet_Pallet->table;
            $columns[]='mhdate';
            $data=$this->model_Pallet_Pallet->builder()
                                            ->orderBy('pid DESC')
                                            ->limit($qty)
                                            ->select(implode(',', $columns))
                                            ->join('movements_history','`movements_history`.`mhtype`=0 AND `movements_history`.`mhref`=`'.$table.'`.`reference`','LEFT')
                                            ->where('Length(customer) >',1)
                                            ->where('status >=',0)
                                            ->get()->getResultArray();
            $table = new \CodeIgniter\View\Table(['table_open'=>'<table class="table table-sm">']);
            foreach($data as $row)
            {
               if (array_key_exists('mhdate', $row))
               {
                   $row['mhdate']= convertDate($row['mhdate'], 'DB', 'd M Y H:i');
               }
               if (array_key_exists('reference', $row))
               {
                  $row['reference']= url_tag(url('Pallets','pallet',[ $row['reference']]),$row['reference'],['class'=>'p-0 text-dark']);
               }
               $table->addRow($row); 
            }
            return $table->generate();
        }
        
	function deletetile($id)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		$model=$this->model_Dash;
		if ($model!=null)
		{
			if ($model->where('did',$id)->delete())
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.general.msg_delete_ok','success'));
			}else
			{
				return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
			} 
		}
		return redirect()->to($refurl)->with('error',$this->createMessage('system.errors.msg_delete_no','danger'));
	}
	
	function logout()
	{
		return $this->auth->logout();
	}
	
	function pageNotFound()
	{
		return $this->view->setFile('errors/html/error_404')->render();
	}
}