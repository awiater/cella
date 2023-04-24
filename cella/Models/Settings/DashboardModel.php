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
 
namespace CELLA\Models\Settings;


class DashboardModel extends \CELLA\Models\BaseModel 
{
	/**
	 * Settings table name
	 * 
	 * @var string
	 */
	protected $table='dashboards';
	
	/**
	 * Table primary key
	 * 
	 * @var string
	 */
    protected $primaryKey = 'did';
		
	/**
	 * Table fields
	 * 
	 * @var array
	 */
	protected $allowedFields=['board','name','header','text','icon','back','sql','row','type','dorder'];
	
	/**
	 * Fields types declarations for forge
	 * @var array
	 */
	protected $fieldsTypes=
	[
		'did'=>				['type'=>'INT','constraint'=>'36','auto_increment'=>TRUE],
		'board'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'name'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'text'=>			['type'=>'VARCHAR','constraint'=>'50','null'=>FALSE],
		'icon'=>			['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'back'=>			['type'=>'VARCHAR','constraint'=>'150','null'=>TRUE],
		'sql'=>				['type'=>'TEXT','null'=>TRUE],
		'row'=>				['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'type'=>			['type'=>'INT','constraint'=>'11','null'=>FALSE],
		'dorder'=>			['type'=>'INT','constraint'=>'11','null'=>FALSE],
		
	];
}