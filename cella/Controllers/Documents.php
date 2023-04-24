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

  
namespace CELLA\Controllers;

use \CELLA\Helpers\AccessLevel;
use \CELLA\Helpers\Arrays as Arr;
use \CELLA\Helpers\Strings as Str;

class Documents extends BaseController
{
	
	/**
	 * Array with function names and access levels from which they can be accessed
	 * view=11 | state=33 | modify=55 | edit=77 | create=99 | delete=121 | settings=143
	 * @var Array
	 */
	protected $access=
	[
		'template'=>AccessLevel::view,
		'templates'=>AccessLevel::settings,
		'item'=>AccessLevel::settings,
		'downloadfile'=>AccessLevel::view
	];
	
	/**
	 * Array with function names and linked models names
	 */
	public $assocModels=
	[
		'document'=>'Documents/Document'
	];
	
	function index()
	{
		return redirect()->to(site_url());
	}
	
	function templates()
	{
		$this->setTableView()
			 ->setData('document',null,TRUE,null,['type'=>'TPLS'])
			 ->setPageTitle('system.documents.tpls_page')
			 ->addColumn('system.documents.tpls_name','name',TRUE)
			 ->addColumn('system.documents.tpls_desc','desc',TRUE,[],'len:80')
			 ->addColumn('system.documents.tpls_access','access',FALSE,$this->model_Auth_UserGroup->getAccessForForm())
			 ->addColumn('system.documents.tpls_enabled','enabled',FALSE,[lang('system.general.no'),lang('system.general.yes')])
			 ->addEditButton('system.documents.tpls_editbtn','item',null,'btn-primary','fa fa-edit')
			 
			 ->addBreadcrumb('system.documents.tpls_breadindex','/')
			 
			 ->addEnableButton()
			 ->addDisableButton()
			 ->addDeleteButton()
			 ->addNewButton('item/new&type=TPLS');//['action'=>'item','params'=>['new'],'get'=>['type'=>'TPLS']]
		return $this->view->render();
	}
	
	function item($record=null)
	{
		if ($record==null)
		{
			return redirect()->to(url($this));
		}
		$id=$record;
		$type=$this->request->getGet('type');
		$type=$type==null ? 'FILE' : $type;
		//$record=is_array($record) && count($record)>0 ? $record[0] :$record;
		$file='';
		if ($record=='new')
		{
			$record=array_combine($this->model_Document->allowedFields, array_fill(0, count($this->model_Document->allowedFields), ''));
			$record[$this->model_Document->primaryKey]='';
			$record['type']=$type;
			$record['path']=$type=='TPLS' ? '@views/Documents/Templates/%name%.php' : '';
		}else
		{
			if (!is_array($record) || (is_array($record) && count($record)<1))
			{
				$record=$this->model_Document->find($record);
			}
			
			$record['path']=parsePath($record['path'],TRUE);
			if (file_exists($record['path']))
			{
				$file=file_get_contents($record['path']);
			}else
			{
				$file='';
			}
			
			$type=$record['type'];
		}
		
		$record=$this->getFlashData('_postdata',$record);
		
		if (!is_array($record) || (is_array($record) && count($record)<1))
		{
			return redirect()->to(url($this))->with('error',$this->createMessage('system.pallets.stack_id_error','danger'));
		}
		
		return $this->setFormView('Documents/edit',FALSE)
					->setFormTitle('{0}',[str_replace('@','',$record['path'])])
                    ->setPageTitle('system.documents.tpls_edit')
					->setFormAction($this,'save',['document'],['refurl'=>current_url(FALSE,TRUE)])
					->setFormCancelUrl($this,$type=='TPLS' ? 'templates' : 'files')
					->setFormArgs(['class'=>'body-full'],['did'=>$record['did'],'refurl_ok'=>url($this,$type=='TPLS' ? 'templates' : 'files')])
					
					->addBreadcrumb($type=='TPLS' ? 'system.documents.tpls_breadindex' : 'index',$type=='TPLS' ? url($this,'templates') : '/')
					->addBreadcrumb($record['name'],'/')
					
					->addInputField('system.documents.tpls_name','name',$record['name'],['required'=>'true','maxlength'=>50])
					->addInputListField('system.documents.tpls_type','type',$record['type'],['FILE'=>'FILE','TPLS'=>'TPLS'],['required'=>'true','maxlength'=>50,'type'=>$type=='TPLS' ? 'hidden':'text'])
					->addInputField('system.documents.tpls_path','path',$record['path'],['required'=>'true','type'=>$type=='TPLS' ? 'hidden':'text'])
					->addAcccessField('system.documents.tpls_access',$record['access'],'access',[],['required'=>'true'])
					->addYesNoField('system.documents.tpls_enabled',$record['enabled'],'enabled',['required'=>'true'])
					->addTextAreaField('system.documents.tpls_desc','desc',$record['desc'],['rows'=>'2','cols'=>'3'])
					->addTextAreaField('system.documents.tpls_dataact','dataact',$record['dataact'],['rows'=>'2','cols'=>'3'])
					->addDropDownField('system.documents.tpls_pdfor','orientation',lang('system.documents.tpls_pdfor_list'),$record['orientation'],[])
					->addCodeEditor('system.documents.tpls_file','text',$record['text'],[])
					->addData('_formview_card_class','body-full')
					->render();
	}
	
	/**
	 * Save data to database
	 * 
	 * @param string $type
	 */
	function save($type,$post=null)
	{
		$post=$this->request->getPost();
		if (is_array($post) && array_key_exists('path', $post) && array_key_exists('name', $post))
		{
			$post['path']=str_replace('%name%', $post['name'], $post['path']);
		}
		return parent::save($type,$post); 
	}

	function _after_save($type,$post,$refurl,$refurl_ok)
	{
		if (is_array($post) && array_key_exists('path', $post) && array_key_exists('file', $post))
		{
			file_put_contents(parsePath($post['path'],TRUE), $post['file']);
		}
		return TRUE;
	}
	
	function template($data,$ispdf=TRUE)
	{
		$refurl=$this->request->getGet('refurl');
		$refurl=$refurl==null ? site_url() : base64url_decode($refurl);
		
		if (is_array($data) && Arr::hasStringKeys($data))
		{
			$data=[$data];
		}else
		if (is_string($data))
		{
			$data=[['template'=>$data]];
		}else
		if (!is_array($data))
		{
			error:
			return redirect()->to($refurl)->with('error',$this->createMessage('system.documents.tpls_dataact_error','danger'));
		}
		
		$files=[];
		$config=$this->model_Settings->get('*');
		$orientation=0;
		foreach ($data as $key=>$file) 
		{
			if (!is_array($file))
			{
				goto error;
			}
			if (!array_key_exists('template', $file))
			{
				goto error;
			}
			if (Str::contains($file['template'],':'))
			{
				$file['template']=Str::before($file['template'],':');
			}
			$file['template']=$this->model_Document->where('name',$file['template'])->first();
			$orientation=$file['template']['orientation'];
			if (strlen($file['template']['dataact']) > 0)
			{
				$file['template']['dataact']=json_decode($file['template']['dataact'],TRUE);
				if (!is_array($file['template']['dataact']))
				{
					goto error;
				}
				if (array_key_exists('refurl', $_GET))
				{
					unset($_GET['refurl']);
				}
				$file['data']=loadModule($file['template']['dataact']['controller'],$file['template']['dataact']['action'],$_GET);
			}
			//
			$file['autoprint']=1;
			$file['config']=$config;
			$file['maxpages']=array_key_exists('maxpages', $file) ? $file['maxpages'] :count(array_key_exists('data', $file) ? $file['data'] : $data);
			$file['page']=array_key_exists('page', $file) ? $file['page'] : $key+1;
			$tpl=$file['template'];
			if (array_key_exists('text', $file['template']))
			{
				$tpl='#'.$file['template']['text'];
			}
			unset($file['template']);
			
			$files[]=view($tpl,array_key_exists('data', $file) ? $file['data'] : $file);//
		}
		
		$file=view('Documents/basetemplate',['content'=>$files,'config'=>$config]);
		if ($ispdf)
		{
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml($file);
			$dompdf->setPaper('A4', $orientation==1 ? 'landscape' : 'portrait');
        	$dompdf->render();
			$dompdf->stream('document.pdf',['Attachment'=>FALSE]);exit(0);	
		}
		return $file;		 
	}
	
	function emaildocument($data)
	{
		if (!is_array($data) || (is_array($data) && !array_key_exists('file', $data)))
		{
			return FALSE;
		}
		
		if (!array_key_exists('email', $data))
		{
			return FALSE;
		}else
		{
			$data['email']=is_array($data['email']) ? $data['email'] : [$data['email']];
		}
		
		$file=$this->model_Document->where('name',$data['file'])->first();
		
		if (!is_array($file))
		{
			return FALSE;
		}
		$file=parsePath($file['path'],TRUE);
		$config=$this->model_Settings->get('*');
		
		$file=view($file,$data);
		
		$email = \Config\Services::email();
		foreach ($data['email'] as $value) 
		{
			$email->setTo($value);
		}
		$email->setSubject(array_key_exists('subject', $data) ? $data['subject'] : lang('system.documents.email_subject'));
		$email->setMessage($file);
		
		return $email->send();
	}
	
	function getcsv(array $data,$name,$firsRecordColumns=FALSE)
	{
		$name=$name.'_'.formatDate().'.csv';
		if ($firsRecordColumns && count($data)>0 && is_array($data[0]))
		{
			array_unshift($data,array_keys($data[0]));
		}
		$csvFile=parsePath('@temp/'.$name);
		$file=fopen($csvFile, 'w');
		foreach ($data as $lines) 
		{
			if (is_array($lines))
			{
				fputcsv($file, $lines);
			}
    		
		}
		fclose($file);
		$file = new \CodeIgniter\Files\File($csvFile);
		header('Content-Disposition: attachment; filename="' .$name.'"');
        $this->response->setHeader('Content-Length: '. $file->getSize(),'');
		$this->response->setHeader('Content-Type','application/octet-stream');
		ob_clean();
		flush();
		readfile($csvFile);
		unlink($csvFile);
	}
	
	function getcsvfile(array $data,$name,$firsRecordColumns=FALSE)
	{
		$name=$name.'_'.formatDate().'.csv';
		if ($firsRecordColumns && count($data)>0 && is_array($data[0]))
		{
			array_unshift($data,array_keys($data[0]));
		}
		$csvFile=parsePath('@temp/'.$name);
		$file=fopen($csvFile, 'w');
		foreach ($data as $lines) 
		{
			if (is_array($lines))
			{
				fputcsv($file, $lines);
			}
    		
		}
		fclose($file);
		$file = new \CodeIgniter\Files\File($csvFile);
		$file_direct=parsePath('@assets/files/temp/'.$file->getFileName(),TRUE);
		$file=parsePath('@assets/files/temp/'.$file->getFileName());
		
		rename($csvFile,$file_direct);
		return $file;
	}
	function createUrl($source,$fileName='_filename')
	{
		$_SESSION[$fileName]=$source;
		return url($this,'getfile',[$fileName]);
	}
	
	function getfile($source)
	{
		if (array_key_exists($source, $_SESSION))
		{
			$source=$_SESSION[$source];
		}
		header("Content-type: ".mime_content_type($source));
		readfile($source);exit;
	}
	
	function downloadfile($source,$fileName=null)
	{
		if (array_key_exists($source, $_SESSION))
		{
			$source=$_SESSION[$source];
		}
		
		$file = new \CodeIgniter\Files\File($source);
		
		$fileName=$fileName==null?$file->getBasename():$fileName;
		
		header('Content-Disposition: attachment; filename="' .$fileName. '"');
        $this->response->setHeader('Content-Length: '. $file->getSize(),'');
		$this->response->setHeader('Content-Type','application/octet-stream');
		ob_clean();
		flush();
		readfile($source);
	}
	
	
}