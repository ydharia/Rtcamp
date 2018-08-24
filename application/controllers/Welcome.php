<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 
	 public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('asia/kolkata');
		$this->load->library('crontab');
	}
	 
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	public function crontest()
	{
		$myfile = fopen("cron/newfile.txt", "w") or die("Unable to open file!");
		$txt = date("h:i:s a")."\n";
		$txt.= json_encode($this->session->userdata());
		//$txt = print_r($this->session->userdata())."\n";
		fwrite($myfile, $txt);
		
		fclose($myfile);
	}
	
	public function addcron()
	{
		$this->crontab->add_job('* * * * *', 'cronjob.php');
	}
	
	public function removecron()
	{
		$this->crontab->remove_job('* * * * *', 'cronjob.php');
	}
	
	public function sessionprint()
	{
		echo "<pre>";
		print_r($this->session->userdata());
		//return json_encode($_SESSION);
	}
	
	public function sessiondataprint()
	{
		$mysess = json_decode($this->input->post("mysession"), true);
		//print_r($mysess);
		$_SESSION = $mysess;
		//$_SESSION = $mysess;
		print_r($this->session->userdata());
		
		//return $this->input->post("mysession");
	}
	
}
