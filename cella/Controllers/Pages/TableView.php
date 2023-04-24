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
use \CELLA\Helpers\AccessLevel;

class TableView extends View
{
	/**
	 * Model class
	 * @var \VLMS\Models\BaseModel
	 */
	private $_model;
	
	/**
	 * Array with table data
	 * @var array
	 */
	 private $_data=[];
	 
	 /**
	  * Table class
	  * @var \CodeIgniter\View\Table
	  */
	  private $_table;
	  
	  /**
	   * Table columns names (headers) when in mobile viewport
	   * @var array
	   */
	   private $_tbl_cols_mobile=[];
	   
	   /**
	    * Database table columns names
	    * @var Array
	    */
	    private $_data_cols=[];
	   
	   /**
	    * Table columns names (headers)
	    * @var
	    */
	    private $_tbl_cols=[];
		
		
		/**
		 * Defines button(s) for edit column
		 * @var array
		 */
		 private $_edit_column=[];
		 
		 /**
		  * Determines if enable button is visible
		  * @var bool/string
		  */
		  private $_enable_btn=FALSE;
		  
		  /**
		  * Determines if remove button is visible
		  * @var bool/string
		  */
		  private $_del_btn=FALSE;
		  
		  /**
		   * Filters fields
		   * @var Array
		   */  
		   private $_filters=[];
		   
		   /**
		    * Filters predefinded fields
		    * @var array
		    */
		   private $_filters_fixed=[];
		   
		   /**
		    * Table tag class
		    * @var Array
		    */
		    private $_table_class=[];
			
			/**
			 * Data primary key name
			 * @var string
			 */
			private $_dataPrimaryKey=null;
			
			/**
			 * Determines if is datable is enabled
			 * @var bool
			 */
			private $_datatable=FALSE;
			
			/**
			 * Determines if sorting of columns is enabled
			 * @var bool
			 */
			 private $_sorting=FALSE;
			
			
	
	public function __construct($controller,$iscached)
	{
		parent::__construct($controller,$iscached);
		$this->_table=new \CodeIgniter\View\Table();
		$this->setStrippedTable();
		$this->addData('_tableview_enable',0);
		$this->addData('_tableview_btns_routes',[]);
		
		
	}
	
	
	/**
	 * Get raw table data
	 * 
	 * @return Array
	 */
	function getTableData()
	{
		return $this->_data;
	}
	
	/**
	 * Set table as bootstrap datatable
	 * 
	 * @param  array $options Optional datatable options
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	function setAsDataTable(array $options=[])
	{
		if (!array_key_exists('searching', $options))
		{
			$options["'searching'"]='true';
		}
		if (!array_key_exists('ordering', $options))
		{
			$options["'ordering'"]='false';
		}
		if (!array_key_exists('paging', $options))
		{
			$options["'paging'"]='true';
		}
		if (!array_key_exists('dom', $options))
		{
			$options["dom"]="Bfrtip";
		}
		
		if (!Str::contains($options['dom'],"'"))
		{
			$options['dom']="'".$options['dom']."'";
		}
		
		return $this->addDataTableScript()
					->addData('_tableview_datatable',$options)
					->addData('_tableview_datatable_filter',array_key_exists('filter', $_GET) ? $_GET['filter'] : null);
	}
	
	/**
	 * Set table class as stripped (default, table-stripped)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setStrippedTable()
	 {
	 	$this->_table_class['table_class'][]='table-striped';
		return $this;
	 }
	 
	/**
	 * Set table class as dark (table-dark)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setDarkTable()
	 {
	 	$this->_table_class['table_class'][]='table-dark';
		return $this;
	 }
	 
	/**
	 * Set table as bordered (table-bordered)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setBorderedTable()
	 {
	 	$this->_table_class['table_class'][]='table-bordered';
		return $this;
	 }
	 
	 /**
	 * Enable tick boxes against table rows
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function addTickBox($hide=FALSE)
	 {
	 	$this->_enable_btn=TRUE;
		return $this;
	 }
	 
	 /**
	  * Sets table title
	  * 
	  * @param string $title
	  * 
	  * @return \VLMS\Libraries\Pages\TableView
	  */
	 function setTableTitle($title)
	 {
	 	return $this->addData('_tableview_card_title',lang($title));
	 }
	 
	
	function setPageTitle($title,$tags=[])
	{
		$tags=is_array($tags) ? $tags : [$tags];
		$title=lang($title,$tags);
		$this->viewData['_vars']['pagetitle']=$title;
		return $this->setTableTitle($title);
	} 
	 
	/**
	 * Set table as row hovered (table-hover)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setHoverRowsTable()
	 {
	 	$this->_table_class['table_class'][]='table-hover';
		return $this;
	 }
	 
	 /**
	 * Set table as small (table-sm)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setSmallTable()
	 {
	 	$this->_table_class['table_class'][]='table-sm';
		return $this;
	 }
	 
	/**
	 * Set custom tags and class on table elements (see codeignitier table class)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 public function setCustomTable(array $settings=[])
	 {
	 	$this->_table_class=$settings;
		return $this;
	 }
	
	/**
	 * Determine if custom view is used
	 * 
	 * @param  bool $enabled
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	function setCustomViewEnable($enabled=TRUE)
	{
		return $this->addData('_tableview_custom',$enabled);
	}
	
	/**
	 * Determines if tick box against each line (row) is visisible
	 * 
	 * @param  bool   $value
	 * @param  string $key
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	function setTickBox($value=TRUE,$key=null)
	{
		$this->_enable_btn=$value;
		$this->_dataPrimaryKey=$key;
		return $this;
	}
	
	/**
	 * Sets table data
	 * 
	 * @param  mixed  $model
	 * @param  string $filters
	 * @param  string $orderBy
	 * @param  mixed  $pagination Could be Integer or bool (False for no pagination, True for default)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function setData($model,$orderBy=null,$pagination=FALSE,$groupby=null,$defFilters=[])
	{
		
		if (is_subclass_of($model,'\VLMS\Models\BaseModel'))
		{
			$this->_model=$model;	
		}else
		if (is_string($model) && array_key_exists($model, $this->controller->assocModels))
		{
			$this->addData('_tableview_model',$model);
			$model=$this->controller->assocModels[$model];
			$model=Str::endsWith('Model',$model) ? $model : $model.'Model';
			$this->_model=model($model);
			$model=$this->_model;	
		}else
		if (!is_array($model))
		{
			goto error_model;
		}else
		{
			$model=array_values($model);
		}
		
		$this->_model=$model;
		$filters=[];
		if (!is_array($model))
		{
			$orderBy=(is_array($_GET) && array_key_exists('orderby', $_GET)) ? $_GET['orderby'] : $orderBy;
			if ($model==null)
			{
				error_model:
				$this->addData('_tableview_model','');
				throw new \Exception('Invalid model name');
			}
			if (is_array($orderBy))
			{
				foreach ($orderBy as $value) 
				{
					$model->orderBy($value);
				}
			}else
			{
				$model->orderBy($orderBy==null ? $model->primaryKey : $orderBy);
			}
		
			if ($groupby!=null && array_key_exists($groupby, $model->allowedFields))
			{
				$model->groupBy($groupby);
			}
		
			$filters=is_array($_GET) && array_key_exists('filtered', $_GET) ? $_GET['filtered'] :[] ;
		}
		
		if ($filters!=null || is_array($defFilters))
		{
			if ($filters!=null)
			{
				$filters=json_decode(base64url_decode($filters),TRUE);
				$this->addData('_tableview_filter_value',$filters['-value-']);
				unset($filters['-value-']);
			}else
			{
				$filters=[];
			}
			$filters=array_merge(is_array($defFilters) ? $defFilters : [] ,is_array($filters) ? $filters : []);
			
			if (!is_array($model))
			{
				$model=$model->filtered($filters);
			}else
			{
				$filters_keys=array_keys($filters);
				$filters=array_values($filters);
				if (is_array($filters_keys) && count($filters_keys) >0)
				{
					$model=array_deep_search($filters_keys[0],$model);
					$model=array_filter($model,function($v, $k){return $v==$filters[0];},ARRAY_FILTER_USE_BOTH);
				}
			}
			
		}
		
		if (!is_numeric($pagination) && $pagination==TRUE)
		{
			$pagination=config('Pager')->perPage;
		}
		
		
		if ($pagination!=FALSE)
		{
			if (!is_array($model))
			{
				$this->_data=$model->paginate($pagination);
				$this->addData('_tableview_pagination',$model->pager->links());
			}else
			{
				$page=1;
				if (array_key_exists('page', $_GET))
				{
					$page=$_GET['page'];
				}
				$page=$page==null ? 1 : $page;
				$max=$pagination;
				if (count($model) < ($max+$page) && $page>1)
				{
					$max=($max+$page)-count($model);
				}
				$this->_data=array_slice($model, $page-1<0 ? 0 : $page-1, $max);
				$pager = \Config\Services::pager();
				$pager=$pager->makeLinks($page, $pagination, count($model));
				$this->addData('_tableview_pagination',$pager);
			}
		}else
		{
			if(is_array($model))
			{
				$this->_data=$model;
			}else
			if (is_array($filters))
			{
				$this->_data=$model->find();
			}
			
		}
		
		$this->_edit_column=[];
		$this->_data_cols=is_array($this->_data) && count($this->_data)>0 ? array_keys($this->_data[0]) : $model->allowedFields;
		return $this;
	}
	
	/**
	 * Add column to table
	 * 
	 * @param  string $label
	 * @param  string $name
	 * @param  bool   $ismobile Determines if column will be visible in mobile view port
	 * @param  array  $list     Optional array with value labels
	 * @param  string $format   Optional format used to format cell value (if numerical strftime will be used, you can also use len:0 as format to substr value 0 is number of characters)
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addColumn($label,$name,$ismobile=FALSE,$list=[],$format=null)
	{
		//
		if ($list=='yesno')
		{
			$list=[lang('system.general.no'),lang('system.general.yes')];
		}else
		if ($list=='access')
		{
			$list=$this->controller->model_Auth_UserGroup->getAccessForForm();
		}
		if (!is_array($list))
		{
			$list=[];	
		}
		
		$label=lang($label);
		if (!in_array($name,$this->_data_cols))
		{
			throw new \Exception($name." is not valid column name", 1);		
		}
		$this->_tbl_cols[$name]=['label'=>$label,'list'=>$list,'format'=>$format,''];
		if ($ismobile)
		{
			$this->_tbl_cols_mobile[$name]=$this->_tbl_cols[$name];
		}
		return $this;
	}
	
	/**
	 * Enable filters
	 * 
	 * @param  string $baseUrl
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	 
	public function addFilters($method)
	{
		$method=$method=='index' ? null : $method;
		$this->addData('_tableview_filters_url',url($this->controller,$method));
		$this->_sorting=TRUE;
		$this->_filters['-value-']='%value%'; 		
		return $this;
	} 
	
	/**
	 * Add filters field to view
	 * 
	 * @param  string $key
	 * @param  string $value
	 * @param  string $filterLabel
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addFilterField($key,$value=null,$filterLabel=null)
	{
		$value=$value==null && $value!=0 && $value!=' ' ? '%value%' : $value;
		$value=$value==null ? '%value%' : $value;
		
		$column=explode(' ', $key);
		
		if (Str::startsWith($key,'||'))
		{
			$column=$column[1];
		}else
		{
			$column=$column[0];
		}
		
		if (!in_array($column,$this->_data_cols))
		{
			throw new \Exception($column." is not valid column name for filters", 1);	
		}
		
		if ($filterLabel!=null && $value!=null)
		{
			$this->_filters_fixed[$filterLabel]=$key.'='.$value;
		}else
		{
			$this->_filters[$key]=$value;
		}
		
		return $this;
	}
	
	/**
	 * Turn on/off sorting of columns
	 * 
	 * @param  bool $enable
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	function setSorting($enable=TRUE)
	{
		$this->_sorting=$enable;
		$this->addData('_tableview_filters_url',current_url());
		return $this;
	}
	
	/**
	 * Add disable button to header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addDisableButton($access=AccessLevel::view,$action='enable/null&enable=0')
	{
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		$this->_enable_btn=TRUE;
		return $this->addHeaderButton($action,'id_tableview_btn_disable','button','btn btn-outline-danger btn-sm tableview_def_btns','<i class="fa fa-eye-slash mr-1"></i>',lang('system.auth.profiles.enable_btn_no'),$access);
	}
	
	/**
	 * Add enable button to header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addEnableButton($access=AccessLevel::view,$action='enable/null&enable=1')
	{
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		$this->_enable_btn=TRUE;
		return $this->addHeaderButton($action,'id_tableview_btn_enable','button','btn btn-success btn-sm tableview_def_btns','<i class="fa fa-eye mr-1"></i>',lang('system.auth.profiles.enable_btn'),$access);
	}
	
	/**
	 * Make delete button visible in header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addDeleteButton($access=AccessLevel::delete,$action='delete/null')
	{
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		$this->_del_btn=TRUE;
		return $this->addHeaderButton($action,'id_tableview_btn_del','button','btn btn-danger btn-sm tableview_def_btns','<i class="fa fa-trash mr-1"></i>',lang('system.auth.profiles.del_btn'),$access);
	}
	
	/**
	 * Make delete button visible in header
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addNewButton($action='new/new',$access=AccessLevel::create)
	{
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		$this->_del_btn=TRUE;
		return $this->addHeaderButton($action,'id_tableview_btn_new','link','btn btn-primary btn-sm','<i class="fa fa-plus mr-1"></i>',lang('system.buttons.new'),$access);
	}
	
	private function parseAction($action)
	{
		if (is_array($this->_model))
		{
			$keys=array_keys($this->_model);
		}else
		{
			$keys=$this->_model->allowedFields;
		}
		$keys=Arr::ParsePatern($keys,'-value-');
		$url=Arr::toObject(['controller'=>'','action'=>'','args'=>[],'get'=>[]]);
		$url->controller=$this->controller;
		
		
		if (is_array($action) && array_key_exists('action',$action))
		{
			if (array_key_exists('get',$action) && is_array($action['get']))
			{
				$url->get=$action['get'];
			}
			$url->controller=array_key_exists('controller',$action) ? $action['controller'] : $url->controller;
			$url->args=array_key_exists('params',$action) ? $action['params'] : $url->args;
			$url->action=$action['action'];
			
			goto url_parse;
		}
		
		if (Str::contains($action,'&'))
		{
			$action=explode('&', $action);
			foreach($action as $key => $value)
			{
				if ($key>0)
				{
					$value=explode('=',$value);
					$url->get[$value[0]]=$value[1];
				}
			}
			$action=$action[0];
		}
		
		if (Str::contains($action,'::'))
		{
			$action=explode('::', $action);
			$url->controller=$action[0];
			$action=$action[1];
		}
		
		if (Str::contains($action,'/'))
		{
			$url->args=explode('/', $action);
			$action=$url->args[0];
			unset($url->args[0]);
			foreach ($url->args as $key => $value) 
			{
				if ($value=='null' || $value==null)
				{
					unset($url->args[$key]);
				}
			}
			
		}
		
		$url->action=$action;
		url_parse:
		$url->get['refurl']=current_url(FALSE,TRUE);
		return url($url->controller,$url->action,$url->args,$url->get);
	}
	
	/**
	 * Add new button to header section
	 * 
	 * @param  string $action
	 * @param  Int    $access
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addHeaderButton($action,$id=null,$type='button',$class='btn btn-secondary',$icon='button_icon',$text='button_text',$access=AccessLevel::view)
	{
		
		
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		
		$_tableview_btns=$this->getViewData('_tableview_btns');
		$_tableview_btns=is_array($_tableview_btns) ? $_tableview_btns :[];
		
		$_tableview_btns_routes=$this->getViewData('_tableview_btns_routes');
		$_tableview_btns_routes=is_array($_tableview_btns_routes) ? $_tableview_btns_routes :[];
		$name='header_'.count($_tableview_btns);
		
		$args=
		[
			'class'=>'mr-1 '.$class,
			'id'=>$id==null ? 'id_tableview_btn_'.$name : $id,
		];
		
		
		
		$args['content']=$icon.($this->ismobile() ? null :$text);
		
		if (is_array($action))
		{
			$dropdownitems=[];
			foreach ($action as $key => $value) 
			{
				$dropdownitems[]=
				[
					'text'=>lang($key),
					'href'=>$this->parseAction($value)
				];	 
			}
			$args['text']=$text;
			$args['dropdownitems']=$dropdownitems;
			$_tableview_btns[]='<div class="btn">'.view('System/Elements/dropdown',$args).'</div>';
			$_tableview_btns_routes[$id]=$args;
		}else
		{
			if ($action!=null)
			{
				$action=$this->parseAction($action);
			}
			
			
			if ($type=='button')
			{
				if ($action!=null)
				{
					$args['data-action']=$action;
				}
			
				$args['name']=$name;
				$type=form_button($args);
			
			}else
			if ($type=='link')
			{
				$type=url_tag($action,$args['content'],$args);
			}else
			if ($type=='link_newtab')
			{
				$args['target']='_blank';
				$type=url_tag($action,$args['content'],$args);
			}
			$args['route']=$action;
			if ($id!=null && strlen($id)>0)
			{
				$_tableview_btns_routes[$id]=$args;
				$_tableview_btns[$id]=$type;
			}else
			{
				$_tableview_btns[]=$type;
				$_tableview_btns_routes[]=$args;
			}
			
		}
		$this->addData('_tableview_btns',$_tableview_btns); 
		$this->addData('_tableview_btns_routes',$_tableview_btns_routes); 
		return $this;
	}
	
	/**
	 * Remove all header buttons
	 * 
	 * @param  bool $clearRoutes Determines if buttons routes will be deleted
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	function clearHeadersButtons($clearRoutes=TRUE)
	{
		$this->addData('_tableview_btns',[]);
		if ($clearRoutes)
		{
			$this->addData('_tableview_btns_routes',[]); 
		} 
		return $this;
	}
	
	/**
	 * Returns given header button details
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function getHeaderButton($id)
	{
		return $this->getViewData('_tableview_btns');
	}

/**
	 * Returns route for given header button
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function getHeaderButtonRoute($id)
	{
		return $this->getViewData('_tableview_btns_routes.'.$id);
	}
	
	/**
	 * Determines if  route for given header button exists
	 * 
	 * @param  mixed $id Button id
	 * 
	 * @return string
	 */
	function isHeaderButtonRoute($id)
	{
		$id=$this->getViewData('_tableview_btns_routes.'.$id);
		return !is_array($id) && strlen($id)>0;
	}
	
	/**
	 * Add button to edit edit column
	 * 
	 * @param  string $label
	 * @param  string $action Button action, if you want change controller use format controller::action, if you want to use different column as id add it to action prefixed by \
	 * @param  string $id
	 * @param  string $type
	 * @param  string $icon
	 * @param  array $args
	 * 
	 * @return \VLMS\Libraries\Pages\TableView
	 */
	public function addEditButton($label,$action,$id=null,$type="btn-primary",$icon='fa fa-edit',array $args=[],$access=AccessLevel::edit)
	{
		if (!$this->controller->auth->hasAccess($access))
		{
			return $this;
		}
		$label=lang($label);
		$name='edit_'.count($this->_edit_column);
		if (is_array($this->_model))
		{
			$key=array_keys($this->_model[0]);
			$urlid='-'.$key[0].'-';
		}else
		{
			$urlid='-'.$this->_model->primaryKey.'-';
		}
		
		$controller=$this->controller;
		
		if (Str::contains($action,'::'))
		{
			$action=explode('::', $action);
			$controller=$action[0];
			$action=$action[1];
		}
		
		if (Str::contains($action,'\\'))
		{
			$action=explode('\\', $action);
			$urlid='-'.$action[1].'-';
			$action=$action[0];
		}
		
		if ($action!=null && Str::startsWith($action,'http'))
		{
			$href=$action;
		}else
		if ($action!=null)
		{
			$href=url($controller,$action,[$urlid],['refurl'=>current_url(FALSE,TRUE)]);
			
		}else
		{
			$href='#';
		}
		$args=is_array($args) ? $args : [];
		$args['name']=$name;
		$type=!Str::contains($type,'btn-sm') && !Str::contains($type,'btn-lg') ? $type.' btn-sm' : $type;
		$args['class']='mr-1 btn '.$type;
		$args['id']=$id==null ? 'id_tableview_btn_'.$name : $id;
		$args['data-toggle']='tooltip';
		$args['data-placement']='left';
		$args['title']=$label;
		$args['data-urlid']=$urlid;
		$this->_edit_column[]=$action=='#' ? form_button('','<i class="'.$icon.'"></i>',$args) : url_tag($href,'<i class="'.$icon.'"></i>',$args);
		return $this;
	}
	
	/**
	 * Render view
	 * 
	 * @param string $mode do not use in this view
	 * 
	 * @return string
	 */
	public function render($mode='HTML',$stop = true)
	{
		if(is_array($this->getViewData('_tableview_datatable')))
		{
			$this->clearHeadersButtons(FALSE);
			//$this->_filters=[];
		}
		
		if (!array_key_exists('table_open', $this->_table_class))
		{
			$table_class=['table'];
			if (array_key_exists('table_class', $this->_table_class))
			{
				$table_class=array_merge($table_class,$this->_table_class['table_class']);
			}
			
			if (!array_key_exists('table_id', $this->_table_class))
			{
				$this->_table_class['table_id']='table_view_datatable';
			}
			
			$this->_table_class['table_open']='<table class="'.(implode(' ', $table_class)).'" id="'.$this->_table_class['table_id'].'">';
			
		}
		$this->addData('table_view_datatable_id',$this->_table_class['table_id']);
		
		if (!array_key_exists('heading_cell_start', $this->_table_class))
		{
			$this->_table_class['heading_cell_start']='<th scope="col">';
		}
		
		$this->_table->setTemplate($this->_table_class);
		
		if (count($this->_tbl_cols)<1)
		{
			$this->_tbl_cols=array_flip($this->_data_cols);
			
		}
		$_tableview_filters_url=$this->getViewData('_tableview_filters_url');
		
		$table_head=[];
		
		$primaryKey=0;
		if (is_array($this->_model))
		{
			$key=array_keys($this->_model[0]);
			$primaryKey=$key[0];
		}else
		{
			$primaryKey=$this->_model->primaryKey;
		}
		$primaryKey=$this->_dataPrimaryKey==null ? $primaryKey : $this->_dataPrimaryKey;
		if ($this->_enable_btn || $this->_del_btn)
		{
			$table_head[]=form_checkbox(['id'=>'id_tableview_sel_all','onclick'=>"$('input[name*=\'".$primaryKey."\']').prop('checked', this.checked);"]);
		}
		if ($this->ismobile())
		{
			$this->_tbl_cols=$this->_tbl_cols_mobile;
		}
		
		$this->addData('_tableview_filters_fixed',$this->_filters_fixed);
		
		foreach ($this->_tbl_cols as $key=>$value) 
		{
			if (is_array($value) && array_key_exists('label', $value))
			{
				if ($this->_sorting)
				{
					$value=$value['label'].url_tag(url($_tableview_filters_url,null,[],['orderby'=>$key]),'<i class="fas fa-caret-up"></i>',['class'=>'ml-1']);
					$value.=url_tag(url($_tableview_filters_url,null,[],['orderby'=>$key.' DESC']),'<i class="fas fa-caret-down"></i>',['class'=>'ml-1']);
				}else
				{
					$value=$value['label'];
				}
				$table_head[]=$value;			
			}else
			if (is_string($value))
			{
				$table_head[]=$value;
			}
		}
		if (is_array($this->_edit_column) && count($this->_edit_column)>0)
		{
			$table_head[]='';
		}
		
		
		$this->_table->setHeading($table_head);
		
		$table_data=[];
		foreach ($this->_data as $row) 
		{
			$id=$row[$primaryKey];
			
			$editbtns=implode('',$this->_edit_column);
			$nrow=$this->_tbl_cols;
			foreach ($row as $key=>$value)
			{
				$editbtns=str_replace('-'.$key.'-', $value, $editbtns);
				if (array_key_exists($key, $this->_tbl_cols) && $key!='_tableview_editcolumn')
				{
					$cfg=$this->_tbl_cols[$key];
					if (is_array($cfg['list']) && count($cfg['list'])>0 && array_key_exists($value, $cfg['list']))
					{
						$row[$key]=lang($cfg['list'][$value]);
					}
					
					if ($cfg['format']!=null && strlen($cfg['format']) > 0)
					{
						if (is_numeric($value))
						{
							$value=convertDate($value,'YmdHi',$cfg['format']);
							if ($value!=FALSE)
							{
								$row[$key]=$value;
							}	
						}else
						if (Str::startsWith($cfg['format'],'len:'))
						{
							$cfg['format']=Str::afterLast($cfg['format'],':');
							if (is_numeric($cfg['format']))
							{
								$row[$key]=substr($value, 0,$cfg['format'] > strlen($value) ? strlen($value) : $cfg['format'] );
							}
						}else
						if (Str::startsWith($cfg['format'],'lang'))
						{
							if ($value!=null && strlen($value) > 0)
							{
								$row[$key]=lang($value);
							}
						}else
						if (Str::startsWith($cfg['format'],'prg:'))
						{
							$cfg['format']=Str::afterLast($cfg['format'],':');
							if (is_string($value) && Str::contains($value,$cfg['format']))
							{
								$value=explode($cfg['format'],$value);
							}
							if (is_array($value) && count($value))
							{
								$color='warning';
								$w=0;
								if (intval($value[0]) > 0)
								{
									$w=(intval($value[0])*100)/intval($value[1]);
									if ($w<25)
									{
										$color='primary';
									}
									if ($w>50)
									{
										$color='primary';
									}
									if ($w>=90)
									{
										$color='success';
									}
								}
								$row[$key]='<div class="text-center w-100"><small>'.implode($cfg['format'][0],$value).'</small>';
								$row[$key].='<div class="progress w-75 mx-auto"><div class="progress-bar bg-'.$color;
								$row[$key].=' progress-bar-striped" role="progressbar" aria-valuenow="'.$value[0];
								$row[$key].='" aria-valuemin="0" aria-valuemax="'.$value[1];
								
								
								$row[$key].='" style="width: '.$w.'%">';
								$row[$key].='</div></div></div>';
							}
						}else
						if (Str::startsWith($cfg['format'],'rep:'))
						{
							$cfg['format']=explode(':',$cfg['format']);
							if (count($cfg['format']) > 2 )
							{
								$row[$key]=str_replace($cfg['format'][1], $cfg['format'][2], $value);
							}
							
						}
											
					}
					$nrow[$key]=$row[$key];
				}
			}
			if ($this->_enable_btn || $this->_del_btn)
			{
				array_unshift($nrow,form_checkbox(['value'=>$id,'name'=>$primaryKey.'[]']));
			}
			
			if (is_array($this->_edit_column) && count($this->_edit_column)>0)
			{
				$nrow[]=$editbtns;
			}
			
			$table_data[]=$nrow;
		}
		if (count($this->_filters)>0)
		{
			$this->addData('_tableview_filters',base64_encode(json_encode($this->_filters)));
		}
		$this->addData('_tableview_table',$this->_table->generate($table_data));
		if ($mode=='text')
		{
			return view($this->getFile(),$this->getViewData());
		}
		$this->addData('_tableview_data',$this->_data);
		
		return parent::render($mode,$stop);
	}
}