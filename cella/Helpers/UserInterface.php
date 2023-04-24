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

class UserInterface
{
	
	 const full=0;
	 
	 const mobile=1;
	
	static function getNames()
	{
	  return
    [
      lang('system.general.interface_full'),
      lang('system.general.interface_mobile')
    ];
	}
	 
}