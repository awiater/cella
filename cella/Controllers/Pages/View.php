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

  
namespace CELLA\Controllers\Pages;

use CELLA\Helpers\Strings as Str;
use CELLA\Helpers\Arrays as Arr;
use CELLA\Helpers\UserInterface;

class View
{
	/**
	 * Holds all view data
	 * @var array 
	 */
	protected $viewData;
	
	/**
	 * Path to view file
	 * @var string
	 */
	protected $viewFile;
	
	/**
	 * Session shared instance
	 * @var
	 */
	protected $session;
	
	/**
	 * Controller class
	 * @var 
	 */
	public $controller;
	
	/**
	 * Determines if view is chached
	 * @var bool
	 */
	 public $iscached=TRUE;
	
	/**
	 * View Mode
	 * HTML - normal template mode
	 * JSON - JSON text mode
	 * TEXT - Only text mode (not using view file and not using tempalte)
	 * HTXT - Template only text mode (just not using view file)
	 * @var
	 */
	protected $mode='HTML';
	
	/**
	 * CELLA Page helper library
	 */
	public function __construct($controller,$iscached=TRUE)
	{
		helper('form');
		helper('html');
		$this->usetemplate=TRUE;
		$this->controller=$controller;
		$this->iscached=$iscached;
		if (is_object($controller) && property_exists($controller, 'session'))
		{
			$this->session=$controller->session;
		}else
		{
			$this->session=\Config\Services::session();
		}
		
		$config=include(parsePath('@template/config.php',TRUE));
		
		/*Add Script*/
		$this->addScript('jquery','@vendor/jquery/jquery.min.js');
		$this->addScript('jqueryui','@vendor/jquery/jquery-ui.min.js');
		$this->addScript('popper','@vendor/jquery/popper.js');
		$this->addScript('bootstrap','@vendor/bootstrap/js/bootstrap.bundle.min.js');
		$this->addScript('boostrapswitch','@vendor/bootstrap/js/bootstrap-switch-button.js');
		if (array_key_exists('scripts', $config))
		{
			foreach (is_array($config['scripts']) ? $config['scripts'] : [$config['scripts']] as $key => $value) 
			{
				$this->addScript($key,$value);
			}	
		}
		/*Add CSS*/
		$this->addCss('boostrap','@vendor/bootstrap/css/bootstrap.min.css');
		$this->addCss('boostrapswitch','@vendor/bootstrap/css/bootstrap-switch-button.min.css');
		$this->addCss('fontawesome','@vendor/fontawesome/css/all.min.css');
		$this->addCss('jquery-ui','@vendor/jquery/jquery-ui.min.css');
		$this->setTitle(config('APP')->APPName);
		if (array_key_exists('css', $config))
		{
			foreach (is_array($config['css']) ? $config['css'] : [$config['css']] as $key => $value) 
			{
				$this->addCss($key,$value);
			}	
		}
		/*Add base data*/
		$this->addData('metadata',[]);
		$this->addData('buttons',[]);
		$this->addData('fields',[]);
		$this->addData('_vars',[]);
		$config['index']=parsePath('@template/index.php',TRUE);
		$this->addData('_template',$config,TRUE);
		$this->addData('currentView',$this);
		$this->addData('_ismobile',$this->ismobile());
		
		$systemSettings=model('Settings/SettingsModel')->get('system.*');
		$systemSettings['app']=config('APP');		
		$this->addData('config',$systemSettings,TRUE);
		$this->addData('_User',service('auth',FALSE)->getLogedUserInfo(),TRUE);
		$this->setFile('System/blank');
		$item=debug_backtrace();
		helper('array');
	}	
	
	/**
	 * Determine if current viewport is on mobile
	 * 
	 * @return bool
	 */
	 
	 function ismobile($checkinterface=TRUE)
	 {
	 	if ($checkinterface)
		{
			return loged_user('interface')==1;
		}
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	 }
	 
	 
	 function getSkeleton(array $data=[])
	 {
	 	$data=array_merge($data,$this->getViewData());
		return view('System/Elements/skeleton',$data);
		
	 }
	 
	/**
	 * Add CSS link and Script link for boostrap time picker
	 * 
	 * @return \CELLA\Helpers\Pages\View
	 */
	function addTimePickerScript()
	{
		return $this->addScript('bootstrap-datetimepicker1','@templates/vendor/bootstrap/js/moment.min.js')
				    ->addScript('bootstrap-datetimepicker','@templates/vendor/bootstrap/js/bootstrap-datetimepicker.min.js')
				    ->addCss('bootstrap-datetimepicker','@templates/vendor/bootstrap/css/bootstrap-datetimepicker.min.css');
	}
	
	
	function addChartObject($type,$name,array $data,array $args=[])
	{
		
		$types=['bar','pie'];
		$charts=$this->getViewData('_chartobject');
		$charts=is_array($charts) ? $charts : [];
		$chart=['name'=>$name];
		$chart['data']=$data;
		$this->session->set('_chartdata',$data);
		$chart['labels']=[];
		$chart['data']=[];
		$chart['type']=in_array(strtolower($type), $types) ? strtolower($type) : 'bar';
		
		$chart=$chart+$args;
		if (!array_key_exists('legend', $chart))
		{
			$chart['legend']=true;
		}
		
		if (!array_key_exists('multivalue', $chart))
		{
			$chart['multivalue']=true;
		}
		
		if (!array_key_exists('defcolor', $chart))
		{
			$chart['defcolor']='red';
		}
		
		if (array_key_exists('title', $chart))
		{
			$chart['title']=lang($chart['title']);
		}else{
			$chart['title']='';
		}

		if (!array_key_exists('datalabels', $chart))
		{
			$chart['datalabels']=['display'=>'false'];
		}
		
		if (array_key_exists('colors', $chart) && is_array($chart['colors']) && !$chart['multivalue'])
		{
			$colors=array_keys($data);
			$colors=array_fill(0, count($colors), $chart['defcolor']);
			$colors=array_combine(array_keys($data), $colors);
			$chart['colors']=array_merge($colors,$chart['colors']);
		}else
		{
			$chart['colors']=$chart['defcolor'];
		}
		
		foreach ($data as $key => $value) 
		{
			$chart['labels'][]=$key;
			if (is_array($value))
			{
				if (Arr::getType($value)=='ASSOC')
				{
					foreach ($value as $skey => $svalue) 
					{
						$skey=str_replace('_', ' ', $skey);
						if (is_numeric($svalue))
						{
							if ($chart['multivalue'])
							{
								$chart['data'][$skey][]=$svalue;
							}else
							{
								$chart['data'][$skey]=$svalue;
							}
							
						}
						
					}
				}else
				{
					$chart['data'][$key]=$value;
				}
			}else
			if (is_numeric($value))
			{
				$chart['data'][$key]=$value;
				if ($chart['multivalue']==TRUE)
				{
					if (!is_array($chart['data'][$key]))
					{
						$chart['data'][$key]=[];
					}
					$chart['data'][$key][]=$value;
				}else
				{
					$chart['data'][$key]=$value;
				}
			}
		}
		$chart['labels']=is_array($chart['labels']) ? '["'.implode('","',$chart['labels']).'"]' : $chart['labels'];
		$chart['object']=view('System/Elements/chart',$chart);
		$charts[$name]=$chart;
		$this->addScript('chart.js','@vendor/chartjs/Chart.min.js')
		     ->addScript('chart.plugin.js','@vendor/chartjs/Chart.plugin.labels.min.js')
			 ->addScript('chart.plugin.js','@vendor/chartjs/jspdf.umd.min.js')
			 ->addData('_chartcolors',json_decode(file_get_contents(parsePath('@vendor/chartjs/colorcodes.json',TRUE)),TRUE))
			 ->addData('_chartobject',$charts)
			 ;
		if (array_key_exists('datalabels', $chart))
		{
			$this->addScript('chartjs-plugin-datalabels','@vendor/chartjs/chartjs-plugin-datalabels.min.js');
		}
		return $this;
	}
	
	function getChartObject($name,$field='object')
	{
		$name=$this->getViewData('_chartobject.'.$name);
		if ($field==null)
		{
			return $name;
		}
		return is_array($name) && array_key_exists($field, $name) ? $name[$field] : $name;
	}
	
	/**
	 * Insert path to CSS file into view data container
	 * 
	 * @param  string $tag  Name of CSS file (or token used later in view file)
	 * @param  string $path Path to CSS file
	 * @return CELLA\Libraries\View
	 */
	public function addCss($tag,$path)
	{
		$path=parsePath($path);
		$this->viewData['css'][$tag]=$path;
		return $this;
	}
	
	/**
	 * Returns all CSS tags
	 * 
	 * @return string;
	 */
	function getCss()
	{
		$string='';
		foreach ($this->viewData['css'] as $path) 
		{
			$string.=link_tag($path).PHP_EOL;
		}
		return $string;
	}
	
	/**
	 * Add custom data to view data container
	 * 
	 * @param  string $tag           Name token used later in view file
	 * @param  mixed  $value         Value of data
	 * @param  bool   $valueAsObject Determines if $value will be treated as object (only if originaly is array)
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function addData($tag,$value,$valueAsObject=FALSE)
	{
		if (is_array($value) && $valueAsObject)
		{
			$value=json_decode(json_encode($value));
		}
		if ($tag=='[]')
		{
			$this->viewData=$value;
		}else
		if ($tag==null)
		{
			$this->viewData[]=$value;
		}else
		{
			$this->viewData[$tag]=$value;
		}
		return $this;
	}
	
	/**
	 * Set data to view container
	 * 
	 * @param  array $data
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function setViewData(array $data,$merge=FALSE)
	{
		if ($merge)
		{
			foreach ($data as $key => $value) 
			{
				if ($key=='scripts')
				{
					foreach ($value as $scriptskey => $scriptsvalue) 
					{
						if(is_array($scriptsvalue))
						{
							$this->addScript($scriptskey,$scriptsvalue['src']);
						}else
						{
							$this->addCustomScript($scriptskey,str_replace(['<script>','</script>'], '', $scriptsvalue),FALSE);
						}
					}
					
				}else
				if (array_key_exists($key, $this->viewData) && is_array($this->viewData[$key]))
				{
					$this->viewData[$key]=$this->viewData[$key]+$value;
				}else
				{
					$this->viewData[$key]=$value;
				}
			}
		}else
		{
			$this->viewData=$data;
		}
		
		
		return $this;
	}
	
	/**
	 * Insert path to script file into view data container
	 * 
	 * @param  string $tag  Name of script file (or token used later in view file)
	 * @param  string $path Path to script file
	 * @return CELLA\Libraries\View
	 */
	public function addScript($tag,$path,array $args=[])
	{
		$path=parsePath($path);
		foreach ($args as $key => $value) 
		{
			$args[$key]=$this->parsePath($value);
		}
		$args['src']=$path;
		$this->viewData['scripts'][$tag]=$args;
		return $this;
	}
	
	/**
	 * Returns all CSS tags
	 * 
	 * @return string;
	 */
	function getScripts()
	{
		$string='';
		foreach ($this->viewData['scripts'] as $key=>$args) 
		{
			
			if (is_array($args) && array_key_exists('src', $args))
			{
				$string.=script_tag($args).PHP_EOL;
			}else
			{
				$string.=$args.PHP_EOL;
			}
			
		}
		return $string;
	}
	
	/**
	 * Insert custom script into view data container
	 * 
	 * @param  string $tag      Name of script file (or token used later in view file)
	 * @param  string $body     Script body
	 * @param  bool   $docReady Determine if script body will be enclosed in document ready function
	 * @return CELLA\Libraries\View
	 */
	public function addCustomScript($tag,$body,$docReady=FALSE)
	{
		if ($docReady)
		{
			$body='$(document).ready(function(){'.$body.'})';
		}
		$this->viewData['scripts'][$tag]='<script>'.$body.'</script>';
		return $this;
	}
	
	/**
	 * Disable main template (mark to not use template)
	 * 
	 * @return CELLA\Libraries\View
	 */
	 public function templateDisable()
	 {
	 	$this->usetemplate=FALSE;
		return $this;
	 }
	 
	 /**
	 * Enable main template (mark to use template)
	 * 
	 * @return CELLA\Libraries\View
	 */
	 public function templateEnable()
	 {
	 	$this->usetemplate=TRUE;
		return $this;
	 }
	 
	 /**
	 * Determine if template is mark as disabled (not to use)
	 * 
	 * @return Bool
	 */
	 public function isTemplateEnabled()
	 {
	 	return $this->usetemplate;
	 }
	 
	 
	 public function addFlashData($key,$defaultData=null)
	 {
	 	if (is_array($this->session->getFlashdata()) && array_key_exists($key, $this->session->getFlashdata()))
		{
			
			if (!array_key_exists($key, $this->viewData) || (array_key_exists($key, $this->viewData) && strlen($this->viewData[$key])<1))
			{
				$data=$this->session->getFlashdata($key);
				$this->addData($key,is_string($data)?lang($data):$data);
			}
			
		}else
		if ($defaultData!=null)
		{
			$this->addData($key,$defaultData);
		}
		return $this;
	 }
	 
	 /**
	  * Add pagination to view data
	  * 
	  * @param  mixed  $type      Model instance
	  * @param  mixed  $type      Type of pagination links
	  * @param  string $groupName Links group name
	  * @return CELLA\Libraries\View
	  */
	 public function addPagination($model,$type='default',$groupName='default',$viewTag='pagination')
	 {
	 	$type=$type==null?'default':$type;
		$groupName=$groupName==null?'default':$groupName;
	 	if ($model->pager!=null)
		{
	 		if (is_array($type)&&Arr::KeysExists(['page','perPage','total'],$type))
			{
				$this->viewData[$viewTag]=$model->pager->makeLinks($type['page'], $type['perPage'], $type['total']);
			}else
			if(is_string($type)&&$type=='simple')
			{
				$this->viewData[$viewTag]=$model->pager->simpleLinks($groupName);
			}else
			{
				$this->viewData[$viewTag]=$model->pager->links($groupName);
			}
		}
		return $this;
	 }
	 
	 public function addBreadcrumbsFromPage(array $page)
	 {
	 	$crumbs=[];
	 	if (array_key_exists('pageparent', $page)&&array_key_exists('pagename', $page))
		{
			$crumbs['@home']=config('App')->baseURL;
			$page['pageparent']=model('Menu/PageGroupModel')->find($page['pageparent']);
			if ($page['pageparent']!=null)
			{
				$crumbs[ucwords($page['pageparent']['name'])]=url('Menu/MenuController','groupdashboard',['id'=>base64url_encode($page['pageparent']['name'])]);
			}
			$crumbs[ucwords($page['pagename'])]=url('/'.$page['pagename'],null);
		}
		
		return $this->addBreadcrumbs($crumbs);
	 }
	 
	/**
	 * Add breadcrumb variable to view data
	 * 
	 * @param  array $crumbs Crumbs array (if empty crumbs will be generate automaticly)
	 * @return CELLA\Libraries\View
	 */
	 public function addBreadcrumb($text,$url)
	 {
	 	$text=lang($text);
	 	$crumbs=$this->getViewData('_breadcrumb_items');
		$crumbs=is_array($crumbs) ? $crumbs : [];
	 	if (!array_key_exists($text, $crumbs))
		{
			$crumbs[$text]=$url;
		}
		
		if (count($crumbs)>0)
		{
			$this->addData('_breadcrumb_items',$crumbs);
		}
		return $this;
	 }
	 
	 public function getBreadcrumbs($class=null,$auto=FALSE)
	 {
		$crumbs=$this->getViewData('_breadcrumb_items');
		if (is_array($crumbs) && count($crumbs)>0)
		{
			$breadcrumb=new \CELLA\Libraries\Breadcrumb();
			$breadcrumb->addMany($crumbs);
			return $breadcrumb->render($class);
		}else
		if ($auto)
		{
			$breadcrumb=new \CELLA\Libraries\Breadcrumb();
			return $breadcrumb->buildAuto($class);
		}		
		return null;
	 }
	
	/**
	 * Setting view path
	 * 
	 * @param  string $fileName Path to view file
	 * @return CELLA\Libraries\View
	 */
	public function setFile($fileName)
	{
		//echo $fileName."<br>" ;
		$this->viewFile=parsePath($fileName,TRUE);
		return $this->addHelpContent($fileName);
	}
	
	/**
	 * Sets page meta title value
	 * 
	 * @param  string $value
	 * @return CELLA\Libraries\View
	 */
	public function setTitle($value)
	{
		$this->viewData['metadata']['title']=$value;
		
		return $this;
	}
	
	function getTitle()
	{
		$str=$this->getViewData('metadata.title');
		$cfg=$this->getViewData('config');
		return strip_tags(is_string($str) ? $str : $cfg->app->APPName);
	}
	
	function getPageTitle()
	{
		$title=$this->getViewData('_vars.pagetitle');
		return is_array($title) ? '' : $title;
	}
	
	function setPageTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		$this->viewData['_vars']['pagetitle']=lang($title,$tags);
		return $this;
	}
	
	/**
	 * Sets page meta description value
	 * 
	 * @param  string $value
	 * @return CELLA\Libraries\View
	 */
	public function setDescription($value,$join=FALSE)
	{
		if (!$join)
		{
			$this->viewData['metadata']['description']=$value;
		}else
		{
			if (strlen($this->viewData['metadata']['description'])<1)
			{
				$this->viewData['metadata']['description']='';
			}
			$this->viewData['metadata']['description'].=' '.$value;
		}
		return $this;
	}
	
	
	
	/**
	 * Sets page meta keywords value
	 * 
	 * @param  mixed $value
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function setKeywords($value,$join=FALSE)
	{
		$value=is_array($value)?implode(',',$value):$value;
		if ($join)
		{
			if (strlen($this->viewData['metadata']['keywords'])<1)
			{
				$this->viewData['metadata']['keywords']='';
			}
			$this->viewData['metadata']['keywords'].=','.$value;
		}else
		{
			$this->viewData['metadata']['keywords']=$value;
		}
		return $this;
	}

	
	/**
	 * Returns linked to page view path
	 * 
	 * @return string
	 */
	public function getFile()
	{
		return $this->viewFile;
	}
	
	/**
	 * Return view data as array
	 * 
	 * @return Array
	 */
	public function getViewData($key=null)
	{
		$this->addFlashData('error');		
		return $key!=null ? dot_array_search($key,$this->viewData) : $this->viewData;
	}
	
	/**
	 * Returns menu (ul) html section
	 */
	public function getHTMLMenu($menu,$class=null,$onlyLinks=FALSE)
	{
		return loadModule('Menu','htmlmenu',[$menu,$class,$onlyLinks]);
	}
	
	/**
	 * Returns given view file body
	 * 
	 * @param string $viewName  View file name
	 * @param array  $ViewData  Data   
	 * @param bool   $mergeData Determines if given data will be merged with original view data   
	 * 
	 * @return string
	 */
	public function includeView($viewName,$ViewData=null,$mergeData=FALSE)
	{
		$ViewData=is_array($ViewData) ? ($mergeData ? array_merge($this->getViewData(),$ViewData) :$ViewData) : $this->getViewData();
		
		$ViewData['currentView']=$this;
		return view($viewName,$ViewData);
	}
	
	/**
	 * Add text data used in TEXT and HTXT modes
	 * 
	 * @param string $text
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function addTextData($text)
	{
		if (is_string($text) && is_numeric($text))
		{
			$this->addData('_textdata',$text);
		}
		return $this;
	} 
	
	/**
	 * Sets view mode 
	 * 
	 * @param string $mode See $mode property
	 */
	public function setViewMode($mode='HTML')
	{
		$this->mode=$mode;
		return $this;
	}
	
	/**
	 * Add references to jspreadsheet script
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function addJSpreadsheet()
	{
		return $this->addScript('jquery.csv','@vendor/jspreadsheet/jexcel.js')
					->addScript('jquery.jexcel','@vendor/jspreadsheet/jsuites.js')
					->addCss('jquery.jexcel','@vendor/jspreadsheet/jsuites.css')
					->addCss('jquery.jexcel','@vendor/jspreadsheet/jexcel.css');
	}
	
	/**
	 * Add references to datatable scripts and css
	 * 
	 * @param  string $tableID
	 * @param  Array  $options
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function addDataTableScript($tableID=null,array $options=[])
	{
		$this->addScript('datatables.min.js','@vendor/datatables/datatables.min.js');
		if ($tableID!=null)
		{
			if (count($options) < 1)
			{
				$options=['searching'=>'false','ordering'=>'false'];
			}
			$sOptions=[];
			foreach ($options as $key => $value) 
			{
				if (in_array($key, ['dom']))
				{
					goto add_value;
				}else
				if ($key=='buttons' && is_array($value))
				{
					$value=json_encode($value);
				}else
				{
					$key="'".$key."'";
				}
				add_value:
				$sOptions[]=$key.":".$value;
			}
			$body="";
			foreach (is_array($tableID) ? $tableID : [$tableID] as $value) 
			{
				$body.='var '.$value."=$('#".$value."').DataTable({".implode(',', $sOptions)."});".PHP_EOL;
			}
			
			$this->addCustomScript('dataTables.ini',$body,TRUE);
		}
		return $this->addCss('datatables.min.css','@vendor/datatables/datatables.min.css')
					->addCss('dataTables.bootstrap4.min.css','@vendor/datatables/dataTables.bootstrap4.min.css');
	}
	
	public function addPDFMakeScript()
	{
		return $this->addScript('html2canvas.min.js','@vendor/jspdf/html2canvas.js')
					->addScript('jspdf','@vendor/jspdf/jspdf.umd.min.js');
	}
	
	public function addPDFJSScripts()
	{
		return $this->addScript('pdf.min.js','@vendor/jspdf/pdf.min.js');
	}
	
	/**
	 * Add path to help content view
	 * 
	 * @param mixed $item Path or array with controller and action
	 * 
	 * @return CELLA\Libraries\View
	 */
	public function addHelpContent($item)
	{
		if (is_array($item) && count($item)==2)
		{
			$item=array_values($item);
			$controller=$item[0];
			if (!is_string($controller) && is_object($controller))
			{
				$controller=Str::afterLast(get_class($controller),'\\');
			}
			$item=$controller.'/'.$item[1];
		}
		if (!is_string($item))
		{
			return $this;
		}
		
		$item=str_replace([' ','/'], '_', strtolower($item));
		$item=parsePath($item,TRUE);
		if (!file_exists($item))
		{
			$item=parsePath('@app/Language/'.(config('APP')->defaultLocale).'/helpfiles/'.$item,TRUE);
		}
		if (file_exists($item.'.php'))
		{
			$item=['file'=>$item.'.php','mode'=>'view'];
		}else
		if (file_exists($item.'.pdf'))
		{
			$item=['file'=>$item.'.pdf','mode'=>'pdf'];
		}else
		{
			return $this;
		}
		return $this->addData('_helpcontent',$item);
	}
	
	/**
	 * 
	 */
	public function isHelpObjectEnabled()
	{
		return array_key_exists('_helpcontent',$this->viewData);
	}
	
	public function getHelpObject($mode=null,array $args=[])
	{
		$_helpcontent=$this->getViewData('_helpcontent');
		
		if ($_helpcontent!=null && is_array($_helpcontent) && Arr::KeysExists(['mode','file'],$_helpcontent) && file_exists($_helpcontent['file']))
		{
			$_helpcontent['content']=base64_encode(view($_helpcontent['file']));
			
			if ($mode=='view')
			{
				if (!array_key_exists('button_class', $args))
				{
					$args['button_class']='btn-info';
				}
				$data=['args'=>$args,'content'=>$_helpcontent];
				return view('System/helpcontent',$data);
			}else
			if (array_key_exists($mode, $_helpcontent))
			{
				return $_helpcontent[$mode];
			}else
			{
				return $_helpcontent;
			}
			
		}
	}
	
	/**
	 * Render view
	 * 
	 * @param string $mode Optional view mode (see $mode property)
	 * 
	 * @return string
	 */
	public function render($mode='html',$stop=TRUE)
	{
		
		if ($this->getViewData('_helpcontent')!=null)
		{
			$this->addPDFJSScripts();
		}
		$this->addData('_refurl',array_key_exists('refurl', $_GET) ? base64url_decode($_GET['refurl']) : previous_url());
	 	$engine=service('viewRenderer',TRUE);
		$mode=strtolower($mode);
		$systemSettings=$this->getViewData('config');
		$options=$systemSettings->cache;
		if ($this->iscached && $options!=0)
		{
			$options=['cache' => $options, 'cache_name' => base64_encode($viewName)];
		}else
		{
			$options=[];
		}
		
		$viewName=parsePath($this->getFile(),TRUE);
		
		$data=$this->getViewData();
		
		if ($mode=='plainhtml')
		{
			$data['_content']=$engine->setData($data)->render($viewName,$options);
			if ($stop)
			{
				echo $engine->setData($data)->render('System/Elements/skeleton',$options);exit;
			}else
			{
				return $engine->setData($data)->render('System/Elements/skeleton',$options);
			}
		}else
		if ($mode=='html')
		{
			$data['_content']=$engine->setData($data)->render($viewName,$options);
		}else
		if ($mode=='json')
		{
			return $this->controller->response->setJSON($data);
		}else
		if ($mode=='text')
		{
			if ($stop)
			{
				echo $engine->setData($data)->render($viewName,$options);exit;
			}else
			{
				return $engine->setData($data)->render($viewName,$options);
			}
			
		}else
		if ($mode=='htxt')
		{
			if (array_key_exists('_textdata', $data))
			{
				$data=$data['_textdata'];
			}else
			{
				$data=json_encode($data);
			}
			$data['_content']=$engine->setData($data)->render(parsePath('@views/System/text',TRUE),$options);
		}else
		{
			return '';
		}
		$viewName=parsePath('@template/index.php',TRUE);
		if (loged_user('interface')==UserInterface::mobile)
		{
			$viewName=parsePath('@template/mobile.php',TRUE);
		}
		echo $engine->setData($data)->render($viewName,$options);exit;	
	}
	
	private function parseTagAttr(array $attr)
	{
		$result='';
		foreach ($attr as $key => $value) 
		{
			$result.=$key.'="'.$value.'" ';
		}
		return $result;
	}
}

