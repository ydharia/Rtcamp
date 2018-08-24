<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct() 
    {
        parent::__construct();
        
		$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$url = $url_array[0];		
		$config = array("client_id"=>"962527986048-fko0boarhk09sclfu0igldkgj1po0p8d.apps.googleusercontent.com","client_secret"=>"By-esciDrlc7omI-_pVmHZag","redirect_uri"=>$url);		
        $this->load->library('email');
        $this->load->library('google_drive',$config);
    }
	
	public function index()
	{
		$this->load->view('welcome_message');
	}
	public function getUser()
	{
		print_r($this->google_drive->getUser());
	}
	
	public function isReady()
	{
		print_r($this->google_drive->isReady());	
	}
	
	public function newDirectory()
	{
		echo "<pre>";
		print_r($this->google_drive->newDirectory("sub folder","19akPsPhV00Btp7gEfrYoUipv_C0v9Oiu",""));
	}
	
	public function getFiles()
	{
		echo "<pre>";
		print_r($this->google_drive->getFiles());
	}

}
