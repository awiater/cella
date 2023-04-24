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

use CELLA\Helpers\Strings as Str;

use Config\Services;
use CodeIgniter\I18n\Time;

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

  
/**
 * A convenience method to translate a string or array of them and format
 * the result with the intl extension's MessageFormatter.
 *
 * @param string      $line
 * @param array       $args
 * @param string|null $locale
 *
 * @return string
 */
function lang(string $line, array $args = [], string $locale = null)
{
	$line=$line==null ? '' : $line;
		return config('APP')->parseLngVars ? Services::language($locale)->getLine($line, $args) : $line;
}

/**
	 * Grabs the current RendererInterface-compatible class
	 * and tells it to render the specified view. Simply provides
	 * a convenience method that can be used in Controllers,
	 * libraries, and routed closures.
	 *
	 * NOTE: Does not provide any escaping of the data, so that must
	 * all be handled manually by the developer.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options Unused - reserved for third-party extensions.
	 *
	 * @return string
	 */
	function view(string $name, array $data = [], array $options = [])
	{
		/**
		 * @var CodeIgniter\View\View $renderer
		 */
		$renderer = Services::renderer();

		$saveData = config(View::class)->saveData;

		if (array_key_exists('saveData', $options))
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}
		
		if (Str::startsWith($name,'@'))
		{
			$name=parsePath($name);
		}
		
		if (Str::startsWith($name,'#'))
		{
			$name=substr($name, 1);
			return $renderer->setData($data, 'raw')
						->renderString($name, $options, $saveData);
		}
		
		return $renderer->setData($data, 'raw')
						->render($name, $options, $saveData);
	}
 
/**
 * Create url from array
 * 
 * @param  array $arr
 * @return string
 */ 
function url_from_array(array $arr)
{
	if (!array_key_exists('controller', $arr))
	{
		return null;
	}
	$controller=$arr['controller'];
	$action=array_key_exists('action', $arr)?$arr['action']:null;
	$params=array_key_exists('args', $arr)?$arr['args']:[];
	return url($controller,$action,$params);
}

/**
 * Create url
 * 
 * @param  string $controller
 * @param  string $action
 * @param  array  $params
 * @param  array  $get
 * @param  bool   $encode
 * @return string
 */
function url($controller,$action=null,array $params=[],array $get=[],$encode=FALSE)
{
		
		if (!is_string($controller) && is_object($controller))
		{
			$controller=get_class($controller);
		}else
		if ($controller==null)
		{
			return null;
		}else
		if ($controller=='$')
		{
			$controller=current_url();
			goto generate_params;
		}else
		if ($controller=='<')
		{
			$controller=previous_url();
			goto generate_params;
		}else
		if ($controller=='@')
		{
			$controller=site_url();
			goto generate_params;
		}else
		if (Str::startsWith(strtolower($controller),'http'))
		{
			goto generate_params;
		}else
		if (Str::startsWith($controller,'/'))
		{
			$controller=site_url($controller);
			goto generate_params;
		}
		
		$controller=str_replace('\\', chr(47), $controller);
		
		if (Str::contains($controller,chr(47)))
		{
			$controller=strtolower(substr(strrchr($controller, chr(47)),1));
		}
		
		if ($action!=null)
		{
			array_unshift($params,$action);
		}
		array_unshift($params,$controller);
		
		$controller=strtolower(site_url($params));
		
		generate_params:
		if ($get!=null && is_array($get) && count($get)>0)
		{
			array_walk($get,function(&$value,$key){$value=$key.'='.$value;});
			$controller.=(Str::contains($controller,'?') ? '&' : '?').implode('&',$get);
		}
		
		return $encode ? base64url_encode($controller) : $controller;
}

function loged_user($field=null)
{
  return service('auth')->getLogedUserInfo($field);
}


function current_url(bool $returnObject = FALSE,$hashed=FALSE)
{
	$uri=$_SERVER['REQUEST_URI'];
	$baseURL=config('App')->baseURL;
	if (strpos($uri, $baseURL)===FALSE)
	{
		if ($uri=='/')
		{
			$uri=$baseURL;
		}else
		{
			$uri=$baseURL.$uri;
		}
		
	}
	$uri=reduce_double_slashes($uri);
	if ($returnObject)
	{
		$uri = clone Services::request()->uri;
		$uri= $returnObject ? $uri : (string) $uri->setQuery('');
	}
	return $hashed&&!$returnObject?base64url_encode($uri):$uri;
}


/**
 * Returns a href (url) html tag
 * 
 * @param  String $href  Url path (href)
 * @param  String $text  Url display text
 * @param  String $args  Custom url arguments
 * @return String A href tag (url)
 */
function url_tag($href,$text,array $args=[])
{
	
	$properties=['target','id','class','aria-haspopup','style','title'];
	if (!array_key_exists('class', $args))
	{
		$args['class']='btn btn-link';
	}
	$str='<a';
	if ($href!=null && strlen($href)>0)
	{
		$str.=' href="'.$href.' "';
	}

	foreach ($args as $key => $value) 
	{
		if (in_array($key, $properties) || Str::startsWith($key,'data-'))
		{
			$str.=' '.$key.'="'.$value.'"';
		}
		
	}
	return $str.'>'.$text.'</a>';
}

/**
 * Returns FontAwesome tag
 * 
 * @param  string $iconName     Name of FontAwesome icon
 * @param  string $htmlTagBody  Value to put in between html tags
 * @param  string $FaTag        Fontawesome start tag
 * @param  string $iconName     Html tag which will be contain icon (default is <i>)
 * @return string
 */
function html_fontawesome($iconName,$htmlTagBody=null,$FaTag='fa fa-',$htmlTag='i')
{
	$iconName=$FaTag.$iconName;
	return '<'.$htmlTag.' class="'.$iconName.'">'.$htmlTagBody.'</'.$htmlTag.'>';
}

/**
 * Url safe base64 string encoding
 * 
 * @param  String $data String to be encoded
 * @return String
 */
function base64url_encode($data)
{
	return Str::base64url_encode($data);
}

/**
 * Url safe base64 string decoding
 * 
 * @param  String $data String to be decoded
 * @return String
 */
function base64url_decode($data)
{
	return Str::base64url_decode($data);
}

/**
 * Return formated date string
 * 
 * @param  mixed  $date     Date integer or word now for now time.
 * @param  bool   $targetDB Determine if date will be saved in db
 * @param  string $format   Format which will be used to format date (if not saved to db)
 * 
 */
function formatDate($date='now',$targetDB=TRUE,$format='YmdHi')
{
	if ($date=='now')
	{
		$date=time();
	}
	$format=$format==null ? 'YmdHi' : $format;
	if (is_array($date) && count($date)==2 && $targetDB=='diff')
	{
		if (!is_string($date[1]) || !is_string($date[1]))
		{
			return null;
		}
		$date[0]=Time::createFromFormat($format,$date[0]);
		$date[1]=Time::createFromFormat($format,$date[1]);
		return $date[1]->difference($date[0]);
	}else
	if (is_string($targetDB))
	{
		if (is_string($date))
		{
			$date=Time::createFromFormat($format,$date);
		}else
		{
			$date=Time::now();
		}
		
		$int=(int)filter_var($targetDB,FILTER_SANITIZE_NUMBER_INT);
		$funct='add';
		$aa=FALSE;
		if (Str::startsWith($targetDB,'-'))
		{
			$int=$int*(-1);
			$aa=TRUE;
			$funct='sub';
		}
		if ($targetDB=='dayofWeek')
		{
			return $date->getDayOfWeek();
		}else
		if ($targetDB=='daysInMonth')
		{
			return \cal_days_in_month(CAL_GREGORIAN, $date->getMonth(), $date->getYear());
		}else
		if ($targetDB=='startOfWeek')
		{
			$funct='subDays';
			$int=$date->getDayOfWeek();
			$int=$int-1;
		}else
		if (Str::contains($targetDB,'week'))
		{
			$int=$int*7;
			$funct.='Days';
		}else
		if (Str::contains($targetDB,'day'))
		{
			$funct.='Days';
		}else
		if (Str::contains($targetDB,'second'))
		{
			$funct.='Seconds';
		}else
		if (Str::contains($targetDB,'minute'))
		{
			$funct.='Minutes';
		}else
		if (Str::contains($targetDB,'hour'))
		{
			$funct.='Hours';
		}else
		if (Str::contains($targetDB,'month'))
		{
			$funct.='Months';
		}else
		if (Str::contains($targetDB,'year'))
		{
			$funct.='Years';
		}
		
		$date=$date->{$funct}($int);
		func_end:
		return $date->toDateTime()->format($format);//date($format,$date);
	}else
	if ($targetDB)
	{
		if (is_int($date))
		{
			return date($format,$date);
		}else
		if (is_a($date, 'DateTime'))
		{
			return $date->format($format);
		}else
		{
			return null;
		}
		
	}else
	{
		
		$date=\DateTime::createFromFormat($format,$date);
		if ($date!=FALSE)
		{
			return $date->format($format);
		}else
		{
			return null;
		}
	}
}

/**
 * Convert given string to formated date string
 * 
 * @param  mixed  $date      Date string
 * @param  string $formatIn  Format of given date string
 * @param  string $formatOut Format which will be used to format date (for db format use DB or null)
 * 
 */
function convertDate($date,$formatIn,$formatOut)
{
	$formatOut=strtoupper($formatOut)=='DB'||$formatOut==null?'YmdHi':$formatOut;
	$formatOut=$formatOut=='ISO8601' ? \DateTime::ATOM : $formatOut;
	$formatIn=strtoupper($formatIn)=='DB' ? 'YmdHi' : $formatIn;
	
	if ($formatIn=='ISO8601')
	{
		$date=new \DateTime($date);
	}else
	{
		$date=\DateTime::createFromFormat($formatIn,$date);
	}
	
	if ($date!=FALSE)
	{
		return $date->format($formatOut);
	}else
	{
		return null;
	}
}

/**
 * Returns array with week days names
 * 
 * @param  $locale String representation of locale (ie en)
 * 
 * @return array
 */
function getWeekDaysNames($locale=null)
{
	if ($locale!=null)
	{
		setlocale(LC_TIME, $locale);
	}
	$days=[];
	$today = ( 86400 * (date("N")) );
	for( $i = 0; $i < 7; $i++ ) 
	{
    	$days[] = strftime('%A', time() - $today + ($i*86400));
	}
	return $days;
}

/**
 * Protect resource file by system
 * 
 * @param  string $url      Absolute or Not path to file
 * @param  bool   $base64   Determine if file link will be protected (FALSE) or base64 URI created (TRUE, only for images/videos)
 * @param  bool   $fullLink Determine if full link or just id will be returned
 * 
 * @return string
 */
function protected_link($url,$base64=FALSE,$fullLink=TRUE)
{
  $url=parsePath($url);
	if ($base64)
	{
		return Str::resourceToBase64($url);
	}
	$url=str_replace(config('App')->baseURL, FCPATH, $url);
	$url=base64url_encode(\Config\Services::encrypter()->encrypt($url));
	return $fullLink ? url('Media/MediaController','getfile',['id'=>$url]):$url;
}


function loadModuleFromString($string)
{
	if (!Str::contains($string,'::'))
	{
		return null;
	}
	$string=explode('::', $string);
	if (Str::contains($string[1],'@'))
	{
		$string[1]=explode('@', $string[1]);
		$string[2]=explode(',',$string[1][1]);
		$string[1]=$string[1][0];
	}else
	{
		$string[2]=null;
	}
	return loadModule($string[0],$string[1],$string[2]);
}

/**
 * Load Controller (and call method with params)
 * 
 * @params  string $controller Controller name or full class name
 * @params  string $method     Controller method name
 * @params  array  $params	   Params which will be passed to controller method
 * 
 * @return mixed
 */
function loadModule($controller,$method=null,array $params = [])
{
	if (Str::contains($controller,'::'))
	{
		$controller=explode('::', $controller);
		$action=$controller[1];
		$controller=$controller[0];
		if (Str::contains($action,'/'))
		{
			$action=explode('/', $action);
			$method=$action[0];
			unset($action[0]);
			$params=array_merge($params,$action);
		}else
		{
			$method=$action;
		}
	}	
		
	if (Str::endsWith($controller,'Model'))
	{
		$controller=model($controller);
		goto method_set;
	}
		
	if (!Str::startsWith($controller,'\CELLA\Controllers'))
	{
		$controller='\\CELLA\\Controllers\\'.$controller;
	}
	
	if (!class_exists($controller))
	{
		throw new Exception(lang('system.errors.load_module_no_class'), 1);		
	}
	$controller=new $controller();
	
	if (method_exists($controller, 'initController'))
	{
		$controller->initController(Services::request(), Services::response(), Services::logger());
	}
	
	method_set:
	$output=$controller;
	if ($method!=null)
	{
		if (! method_exists($controller, $method))
		{
			throw new Exception(lang('system.errors.load_module_no_method'), 1);
		}
		
		$refMethod  = new ReflectionMethod($controller, $method);
		$paramCount = $refMethod->getNumberOfParameters();
		$refParams  = $refMethod->getParameters();
		
		$output=null;
		if ($paramCount === 0)
		{
			if (count($params)>0)
			{
				throw new Exception(lang('system.errors.load_module_no_params'), 1);
			}
			$output = $controller->{$method}();
		}else
		if ($paramCount<count($params))
		{
			throw new Exception(lang('system.errors.load_module_no_params'), 1);
		}else
		{
			$output = call_user_func_array([$controller,$method], $params);
		}
	}
	
	return $output;
}
	/**
	 * Parse given path to full website or server path
	 * 
	 * @param  bool $direct If true server path will be used instead of website url
	 * 
	 * @return string 
	 */
	function parsePath($path,$direct=FALSE)
	{
		$baseURL=$direct ? FCPATH : config('App')->baseURL;
		$repl=
		[
			'@vendor'=>'@assets/vendor/',
			'@template'=>'@assets/template/',
			'@storage'=>'@writable/',
			'@views'=>'@cella/Views/',
			'@temp'=>FCPATH . 'writable/temp/',
			'@app'=>realpath(config('Paths')->appDirectory),
			'@'=>$baseURL,
			'://'=>':#',
			'//'=>'/',
			':#'=>'://'
		];
		return str_replace(array_keys($repl),array_values($repl), $path);
	}
	
	 /**
	 * Create html message container
	 * 
	 * @param  String $message Message text (if prefix with @ it will be used as language tag name)
	 * @param  String $type    Type of message (danger,info,success)
	 * @param  mixed  $encode  Determine if html code is base64 (or base64url) encoded
	 * @return String
	 */
	 function createErrorMessage($message,$type='info',$encode=FALSE)
	 {
	 	$message=is_array($message)?$message:[$message];
		$result='';
		$View=service('viewRenderer',FALSE);
		foreach ($message as $value) 
		{
			$result.=$View->setData(['msg'=>lang($value),'type'=>$type])->render(parsePath('@views/errors/html/exception',TRUE));
		}
		if ($encode)
		{
			$result=base64_encode($result);
		}else
		if ($encode=='url')
		{
			$result=base64url_encode($result);
		}
	 	return $result;
	 }
	 
	 function getData($object,$arg=null)
	 {
	 	if (is_array($object))
		{
			if (array_key_exists($arg, $object))
			{
				return $object[$arg];
			}else
			if(is_string($arg) && Str::contains($arg,'.'))
			{
				return dot_array_search($arg,$object);	
			}else
			{
				return null;
			}
			
		}else
		if (is_object($object) && property_exists($object, $arg))
		{
			return $object->{$arg};
		}else
		if (is_string($object) && $arg==null)
		{
			if (!empty($$object))
			{
				return $$object;
			}else
			{
				return null;
			}
		}
		
	 }
	 
	 function createDefaultAvatar(string $text = 'DEV',array $bgColor = [255, 255, 255],array $textColor = [0, 0, 0],int $fontSize = 340,int $width = 600,int $height = 600,string $font = '@vendor/fonts/myfont.ttf') 
	 {
    	$font=parsePath($font,TRUE);
        $image = @imagecreate($width, $height)
            or die("Cannot Initialize new GD image stream");
		
		if (Str::contains($text,' '))
		{
			$stext='';
			foreach (explode(' ', $text) as  $value) 
			{
				$stext.=substr($value,0,1);
			}
			$text=$stext;
		}else
		{
			$text=substr($text, 0,1);
		}
		
		if (strlen($text)>3)
		{
			$text=substr($text, 0,3);
			$fontSize=240;
		}else
		if (strlen($text)>2)
		{
			$fontSize=240;
		}
		
        imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);

        $fontColor = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

        $textBoundingBox = imagettfbbox($fontSize, 0, $font, $text);

        $y = abs(ceil(($height - $textBoundingBox[5]) / 2));
        $x = abs(ceil(($width - $textBoundingBox[2]) / 2));

        imagettftext($image, $fontSize, 0, $x, $y, $fontColor, $font, $text);
		
		ob_start(); // Let's start output buffering.
    	imagejpeg($image); //This will normally output the image, but because of ob_start(), it won't.
    	$contents = ob_get_contents(); //Instead, output above is saved to $contents
		ob_end_clean();
		imagedestroy($image);
        return base64_encode($contents);
    }
	
	/**
	 * Generates barcode from data
	 * 
	 * @param  string $data
	 * @param  array  $options
	 * @param  bool   $imgTag
	 * @return string
	 */
	function GenerateBarcode($data,array $options=[],$imgTag=TRUE,array $imgTagArgs=[])
	{
		$options['st']=FALSE;
		$data=service('BarcodeGenerator')->generateBase64($data,$options);
		return $imgTag ? img($data,FALSE,$imgTagArgs) : $data;
	}
	 

function dump($data,$echo=TRUE)
{
	$arr=debug_backtrace();
	$str='';
	if ((!defined(ENVIRONMENT)||(defined(ENVIRONMENT) && ENVIRONMENT=='production')) && loged_user('username')!='sadmin')
	{
		throw new Exception("Try to dump data in production ".$arr[0]['file'].' at '.$arr[0]['line'], 1);		
	}
	
	if (is_array($arr)&&count($arr)>0)
	{
		$str= '<p>'.$arr[0]['file'].' at '.$arr[0]['line'].'</p><br>';
	}
	
	if (is_array($data)||is_object($data))
	{
		if ($echo)
		{
			echo $str.'<pre>';print_r($data); '</pre>';
		}else
		{
			return $str.'<pre>'.print_r($data,TRUE).'</pre>';
		}
		
	}else
	{
		var_dump($data);
	}
	
	
}



