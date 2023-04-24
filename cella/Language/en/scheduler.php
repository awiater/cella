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
 
 return 
 [
	'predel'=>
		[
			'mainmenu'=>'Pre Deliveries',
			'editpage'=>'Pre Delivery Details',
			'card_title'=>'Delivery',
			'card_tooltip'=>'Creates Pre Delivery',
			'new'=>'New Job',
			'reference'=>'Job Code',
			'owner'=>'Supplier',
			'duein'=>'Due In',
			'status'=>'Status',
			'page'=>'Pre Deliveries',
			'pallets_qty'=>'Pallets QTY',
			'box_qty'=>'QTY of Boxes',
			'editbtn'=>'Edit details',
			
			'checkbtn'=>'Receive',
			'checkform_title'=>'Pre Delivery Check Form',
			'checkform_msg'=>'Please provide your login details to confirm delivery arrival',
			'username'=>'Username',
			'pass'=>'Password',
			'attach'=>'Attach',
			'attached'=>'Attached Paperwork'
		],
	'collections'=>
		[
			'card_title'=>'Collection',
			'card_tooltip'=>'Creates Order Collection ',
			'infobtn'=>'Show All details',
			
		],
	'boards'=>
		[
			'mainmenu'=>'Scheduler',
			'weekdays'=>['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
			'collections'=>'Collections',
			'deliveries'=>'Deliveries',
			'editcardbtn'=>'Edit Details',
			'new_card_modal_title'=>'Add New Card',
			'newcardbtn_tooltip'=>'Add New Card',
			'prevbtn_tooltip'=>'Previous',
			'todaybtn_tooltip'=>'Today',
			'nexttn_tooltip'=>'Next',
			'week'=>'Week',
		],
		
	'orders'=>
		[
			'orderbtn'=>'Request Dims',
			'delbtn'=>'- Scheduler',
			'orderbread'=>'Link Jobs',
			'setformtitle'=>'Link Jobs Customers to Deliveries',
			'colref'=>'Job Code',
			'newcoldue'=>'Job Due',
			'colstatus'=>'Status',
			'newcolqty'=>'Pallets Qty',
			'colowner'=>'Customer',
			'coldel'=>'Linked Jobs',
			'deliverytitle'=>'Avalaiable Suppliers Jobs',
			'inboard_col'=>'Req Dims',
		],
	'cards'=>
		[
			'priority'=>'Change Priority',
			'priority_modal_title'=>'Change Priority',
			'priority_modal_jobcode'=>'Job Reference',
			'priority_modal_priority'=>'Priority',
			'priority_modal_priority_list'=>['Secondary','Priority'],
			'priority_modal_okbtn'=>'Save',
			'priority_modal_cncbtn'=>'Cancel',
			'invoice'=>'Mark as Invoiced',
			'tpledit_title'=>'Edit Card Template',
			
		],	
	
	'settings'=>
		[
			'tabname'=>'Scheduler',
			'boardname'=>'Default Board',
			'boardname_tooltip'=>'Choose default board from list',
			'boardnameslist'=>'Avaliable Boards',
			'tabfile'=>'Advanced',
			'colcfg'=>'Board Columns Settings',
			'view'=>'Board Date Control Format',
			'view_list'=>['Week in Year','Start & End Week Day'],
			'tabhome'=>'General',
			'name'=>'Board Name',
			'weekdays'=>'Allocated Days',
			'weekdays_tooltip'=>'Determines how many days (columns) per week board cover (1-7)',
			'rowqty'=>'Max Rows Qty',
			'rowqty_tooltip'=>'Determines board rows quantity per page',
			'cardheight'=>'Default Card Height',
			'cardheight_tooltip'=>'Determines minimum card height (in px)',
			'usedate'=>'Date Controls',
			'usedate_tooltip'=>'Determines if dates controls are visible in board',
			'enabled'=>'Is Active',
			'access'=>'Default Access Level',
			'flag'=>'Order',
			'autorefresh'=>'Data Auto refresh',
			'autorefresh_tooltip'=>'Determines boards data auto refresh interval in seconds (0 no refresh)',
			'boardseditor_title'=>'Edit Board Data',
			'boardseditor_okbtn'=>'Save',
			'boardseditor_cncbtn'=>'Cancel',
			'boardcardscolors'=>'Avalaiable Cards Colors',
			'boardcardstpls'=>'Cards Templates',
			'view_file'=>'Board Template',
			'show_expired'=>'Mark Expired Cards',
			'show_expired_tooltip'=>'Determines if expired (pass due date) cards are show as red',
		],	
	'errors'=>
		[
			'delivery_overdue'=>'!!! This delivery is over due !!!',
			'erroruserpassjob'=>'Invalid login data',
			'errordeljobcode'=>'Invalid Job Code. Contact administrator with error code "errordeljobcode"',
			'errorboardid'=>'Invalid Board. Contact administrator with error code "errorboardid"',
			'errornojobsselected'=>'Please select at least 1 job from list',
			'jobsremoved'=>'Selected Jobs removed from Scheduler sucessfully',
		]
];
