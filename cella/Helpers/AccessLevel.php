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
 
namespace CELLA\Helpers;

class AccessLevel
{
	//view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	
	 const view='view';
	 
	 const state='state';
	
	 const modify='modify';
	 
	 const edit='edit';
	 
	 const create='create';
	 
	 const delete='delete';
	 
	 const settings='settings';
	 
	 const Levels=
	 	[
	 		'view'=>AccessLevel::view,
			'state'=>AccessLevel::state,
			'modify'=>AccessLevel::modify,
			'edit'=>AccessLevel::edit,
			'create'=>AccessLevel::create,
			'delete'=>AccessLevel::delete,
			'settings'=>AccessLevel::settings,
	 	];
	 
	 
}