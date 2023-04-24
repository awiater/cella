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

class Attendance extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'index'=>       AccessLevel::edit,
                'records'=>     AccessLevel::edit,
                'fetchdata'=>   AccessLevel::edit,
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'items'=>'Attendance/AttendanceItem',
		'users'=>'Attendance/AttendanceUser'
	];
	
	/**
	 * Array with controller method remaps ($key is fake function name and $value is actuall function name)
	 */
	public $remaps=
        [
            'index'=>'records'
        ];
        
        function records($mode='clockings',$startdate='now')
        {
            $start=formatDate(formatDate($startdate,'startOfWeek','Ymd'),'+ 1 day','Ymd');
            $end=formatDate($start,'+ 4 days','Ymd');
            
            return $this->view->setFile('Attendance/records_list')
                        ->addData('start',$start)
                        ->addData('end',$end)
                        ->addData('users',$this->model_Users->getForForm('att_empid','att_name',FALSE,null, ['enabled'=>1]))
                        ->addData('records',$this->model_Items->getDetailsForDateRange($start,$end))
                        ->addData('url',url($this,'records',['clockings','-date-']))
                        ->addData('url_fetch',url($this,'fetchdata',[],['refurl'=> current_url(FALSE,TRUE)]))
                        ->addDataTableScript()
                        ->render();
        }
        
        function fetchdata()
        {
            $settings=$this->model_Settings->get('attendance.*');
            if ($this->model_Items->fetchDataFromClock($settings['att_clock_ip'],$settings['att_clock_clear'],$settings['att_clock_dateformat'],$settings['att_clock_port']))
            {
                return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('attendance.msg_fetch_ok','success'));
            }        
            return redirect()->to($this->getRefUrl())->with('error',$this->createMessage('attendance.error_fetch','danger'));
        }
        
}