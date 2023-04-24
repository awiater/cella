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
 
namespace CELLA\Models\Attendance;

use \CELLA\Helpers\MovementType;
use \CELLA\Helpers\Strings as Str;
use \CELLA\Helpers\Arrays as Arr;

class AttendanceItemModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Users table name
	 * 
	 * @var string
	 */
	protected $table='attedance_items';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
        protected $primaryKey = 'atiid';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['ati_empid','ati_empid','ati_date','ati_start','ati_end','ati_comm'];
	
	protected $validationRules =[];
	
	protected $validationMessages = [];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'atiid'=>       ['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'ati_empid'=>	['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'ati_date'=>	['type'=>'VARCHAR','constraint'=>'25','null'=>FALSE],
		'ati_start'=>	['type'=>'VARCHAR','constraint'=>'4','null'=>FALSE],
		'ati_end'=>	['type'=>'VARCHAR','constraint'=>'4','null'=>FALSE],
                'ati_comm'=>    ['type'=>'VARCHAR','constraint'=>'250','null'=>FALSE],
	];
        
        /**
         * Fetch data from clocking machine and save in database
         * 
         * @param string $clockIP
         * @param bool   $clearData
         * @param int    $clockPort
         * @param string   $clockDateFormat
         * 
         * @return boolean
         */
        function fetchDataFromClock(string $clockIP,bool $clearData=FALSE,string $clockDateFormat='Y-m-d H:i:s',int $clockPort=4370)
        {
            $users=$this->getModel('AttendanceUser')->getForForm('att_empid','*',FALSE,null,['enabled'=>1]);
            $zk = new \CELLA\Libraries\ZKLibrary($clockIP, $clockPort);
            $zk->connect();
            $zk->disableDevice();
            $data=$zk->getAttendance();
            if ($clearData)
            {
                $zk->clearAttendance();
            }
            $zk->enableDevice();
            $zk->disconnect();
            $arr=[];
            
            foreach($data as $record)
            {
                if (array_key_exists($record[1], $users))
                {
                    $date=convertDate($record[3],$clockDateFormat, 'Ymd');
                    $time=convertDate($record[3],$clockDateFormat, 'Hi');
                    $id=$record[1].$date;
                    
                    
                    if (array_key_exists($id, $arr))
                    {
                        $arr[$id]['ati_end']=$time;
                    }else
                    {
                        $arr[$id]=
                        [
                            'ati_empid'=>$record[1],
                            'ati_date'=> $date,
                            'ati_comm'=>'test',//Fetched From Clock on: '.formatDate(),
                            'atiid'=>$id,
                            'ati_start'=>$time
                        ];
                    }
                    $record=$this->where('atiid',$id)->first();
                    if (is_array($record) && array_key_exists('ati_start', $record) && strlen($record['ati_start']) > 0)
                    {
                        $arr[$id]['ati_end']=$time;
                        unset($arr[$id]['ati_start']);
                        $this->save($arr[$id]);
                    }else
                    {
                        $this->builder()->set($arr[$id])->insert();
                    }
                   
                }
            }
            //dump($arr);exit;
            return TRUE;
        }
        
        /**
         * Returns array with items data for given date range
         * 
         * @param string $start
         * @param string $end
         * 
         * @return array
         */
        function getDetailsForDateRange($start,$end)
        {
            $arr=[];
            foreach($this->filtered(['ati_date >='=>$start,'ati_date <='=>$end])->find() as $record)
            {
                $arr[$record['att_name']][$record['ati_date']]=$record;
            }
            return $arr;
        }
        
        function filtered(array $filters = [], $orderby = null, $paginate = null, $logeduseraccess = null, $Validation = TRUE) 
        {
            $filters['enabled']=1;
            return $this->getView('vw_attedance_items')->filtered($filters, $orderby, $paginate, $logeduseraccess, $Validation);;
        }
       
}

/*
vw_attedance_items
SELECT 
`ai`.`atiid`,
`au`.`att_name`,
`au`.`att_start`,
`au`.`att_finish`,
`ai`.`ati_empid`, 
`ai`.`ati_date`, 
`ai`.`ati_start`,
(if (`ai`.`ati_start` > `au`.`att_start`,1,0)) as `att_latestart`,
(if (`ai`.`ati_end` < `au`.`att_finish`,1,0)) as `att_earlyfinish`,
`ai`.`ati_end`,
(if(`ai`.`ati_start` >= `au`.`att_finish` OR (`ati_end` <> null AND (`ati_end`*1) < '1'),0,1)) as `enabled`
FROM `attedance_items` as `ai`
LEFT JOIN attedance_users as `au` ON `au`.`att_empid`=`ai`.ati_empid;
*/
