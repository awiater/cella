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

class MovementType
{
	
	 const create=0;
	 
	 const move=1;
	
	 const merge=2;
	 
	 const delete=3;
	 
	 const receipt_complete=4; 
	 
	 const receipt_receiving=5;
	 
	 const receipt_full=6;
	 
	 const putaway=7;
	 
	 const order_inpick=8;
	 
	 const order_assign=9;
	 
	 const load_loading=10;
	 
	 const load_done=11;
	 
	 const order_picked=12;
	 
	 const order_picking=13;
	 
	 const stocktake=14;
	 
	 const status=15;
	 
	 const labels=16;
}