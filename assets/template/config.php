<?php
/*
 *  This file is part of Vacatio LMS  
 * 
 * 
 *  @version: 1.1					
 *	@author Artur W				
 *	@copyright Copyright (c) 2020 All Rights Reserved				
 *
 *  @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 
 return [
 	'css'=>
 		[
 			'@template/css/adminlte.min.css',
 			'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback',
 			'@template/css/cella.css',
 			'@template/css/scheduler.css',
 		],
 	'scripts'=>
 		[
 			'@template/js/adminlte.min.js',
 			'@template/js/cella.js',
 		],
 	'submenuroutetpl'=>'["-menuame-",{"dropdown":{"text":"-menutext-","icon":"-menuimage-"},"ul":"nav-treeview","image":"fas fa-long-arrow-alt-right fa-sm mr-1"}]'
 ];
