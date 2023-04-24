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

class Strings
{
	/**
	 * Returns class name without namespace
	 * 
	 * @param  String $class Full class name
	 * @return String        Class name without namespace
	 */
	static function classShortName($class)
	{ 
		if(is_object($class))
		{
			$class=get_class($class);
		}
		return substr(strrchr($class,'\\'),1); 
	}
	
	
	static function before($haystack, $needle) 
	{
		$length = strlen($haystack)-strlen($needle); 
		$pos = strpos($haystack, $needle);
		return substr($haystack,0,$pos); 
	}
	
	static function afterLast($haystack, $needle)
	{
		$haystack=explode($needle, $haystack);
		$needle=count($haystack)-1;
		return $haystack[$needle];
	}
	
	static function after($haystack, $needle) 
	{
		$length = strlen($haystack)-strlen($needle); 
		$pos = strpos($haystack, $needle)+strlen($needle);
		return substr($haystack,$pos); 
	}
	
	/**
	 * Convert image/video from url to base64 URI
	 * 
	 * @param  string $image Path to file
	 * 
	 * @return string
	 */
	static function resourceToBase64($image)
	{
		$image_src=str_replace(config('App')->baseURL, FCPATH, $image);
		if (!file_exists($image_src))
		{
			return $image;
		}
		$imageData = base64_encode(file_get_contents($image_src));
		return 'data: '.mime_content_type($image_src).';base64,'.$imageData;
	}
	
	/**
	 *  Create unique id
	 *  
	 *  @return String
	 */
	static function createUID($maxlength=null)
	{
		$return=base64_encode(uniqid());
		if (is_numeric($maxlength))
		{
			$maxlength=$maxlength>strlen($return)?strlen($return):$maxlength;
		}else
		{
			$maxlength=strlen($return);
		}
		$return=str_replace('=', '', $return);
		return substr($return,0, $maxlength);
	}
	
	/**
	 * Url safe base64 string encoding
	 * 
	 * @param  String $data String to encode
	 * @return String
	 */
	static function base64url_encode($data) 
	{
  		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
	
	/**
	 * Url safe base64 string decoding
	 * 
	 * @param  String $data String to decode
	 * @return String
	 */
	static function base64url_decode($data) 
	{
  		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}
	
	
	/**
	 * Check if string starts with given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string start with given characters, FALSE otherwise
	 */
	static function startsWith($haystack, $needle) 
	{ 
		$length = strlen($needle); 
		return (substr($haystack, 0, $length) === $needle); 
	} 
	
	/**
	 * Check if string ends with given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string ends with given characters, FALSE otherwise
	 */
	static function endsWith($haystack, $needle) 
	{ 
		$length = strlen($needle); 
		if ($length == 0) { return true; } 
		return (substr($haystack, -$length) === $needle); 
	}
	
	/**
	 * Check if string contains given characters
	 * 
	 * @param  String $haystack String to check
	 * @param  String $needle   Characters to use
	 * @return Boolean          TRUE if string contains given characters, FALSE otherwise
	 */
	static function contains($haystack,$needle)
	{
		$needle=is_array($needle)?$needle:[$needle];
		foreach ($needle as $value) 
		{
			if (strlen($value)<1)
			{
				return FALSE;
			}
			if (is_string($haystack) && is_string($value))
			{
				return strpos($haystack, $value) === FALSE ? FALSE : TRUE;
			}else
			{
				return FALSE;
			}	
		}
		return TRUE;
	}
	
	/**
	 *  Determine if given string is valid JSON
	 * 
	 *  @param  String  $haystack String to check
	 *  @return Boolean           TRUE if given string is valid JSON or FALSE otherwise
	 */
	static function isJson($haystack)
	{
		if (!is_string($haystack))
		{
			return false;
		}
		json_decode($haystack);
 		return (json_last_error() == JSON_ERROR_NONE);
	}
}
?>