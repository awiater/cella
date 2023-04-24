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
if (!function_exists('parseDayData'))
{ 
	function parseDayData($data,$weekdays_names,$dateFrom,$col)
	{
		$col=strval($col);
		return str_replace(['$dayName','$dayDate','$dayRawDate','/n'], 
    					[
    						array_key_exists($col, $weekdays_names) ? $weekdays_names[$col] : '',
    						convertDate(formatDate($dateFrom,'+ '.$col.' days'),'DB','d M Y'),
    						convertDate(formatDate($dateFrom,'+ '.$col.' days'),'DB','Ymd'),
							'<br>'
    						 
    					]
    					, $data);
	}
}