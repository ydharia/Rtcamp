<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
class myfacebook extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('asia/kolkata');
		$this->load->library('user_agent');
		$this->load->helper('download');
		$this->load->helper('directory');
		$this->load->library('crontab');
		$this->setCronSession();
		$this->load->library('facebook');
		$this->load->helper('url');
		$this->load->library('zip');
		$this->load->helper('download');
		$this->load->library('email');
		if(!$this->facebook->is_authenticated() && $this->router->fetch_method() !="index")
		{
			redirect(base_url()."myfacebook/");
		}
		if($this->input->ip_address() == $_SERVER['SERVER_ADDR'])
		{
			$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$url = $url_array[0];	
			$config = array("client_id"=>"962527986048-fko0boarhk09sclfu0igldkgj1po0p8d.apps.googleusercontent.com","client_secret"=>"By-esciDrlc7omI-_pVmHZag","redirect_uri"=>$url);
			$this->load->library("google_drive",$config);
		}
		$this->setUserSession();
		$this->setOldTask();
    }
	
	public function googleLogin()
	{
		$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$url = $url_array[0];		
		$config = array("client_id"=>"CLIENT_ID","client_secret"=>"CLIENT_SECRET","redirect_uri"=>$url);		
		$this->load->library('google_drive',$config);
		redirect(base_url()."myfacebook/albums");
	}
	
	public function setCronSession()
	{
		if($this->input->ip_address() == $_SERVER['SERVER_ADDR'])
		{
			$dirname = "cron";
			try
			{
				$map = directory_map($dirname, 1);       
				if(empty($map))
				{
					$this->crontab->remove_job('* * * * *', 'cronjob.php');
				}
				else
				{
					$myfile = fopen($dirname."/".$map[0], "r") or die("Unable to open file!");
					$cronSession = fread($myfile,filesize($dirname."/".$map[0]));
					fclose($myfile);
					$_SESSION = json_decode($cronSession, TRUE);
				}
		 	}
		 	catch(Exception $exx)
		 	{
		 		return false;
		 	}
		}		
	}
	
	public function setOldTask()
	{
		$dirname = "cron/".$this->session->userdata("userid").".txt";
		if(file_exists($dirname))
		{
			$myfile = fopen($dirname, "r") or die("Unable to open file!");
			$cronSession = fread($myfile,filesize($dirname));
			fclose($myfile);
			$allSession = json_decode($cronSession, TRUE);
			if (array_key_exists("albumList", $allSession))
			{
				$_SESSION["albumList"] = $allSession["albumList"];
			}
		}
		else
		{
			$_SESSION["albumList"] = array();
		}
		
	}
	
	public function setCronTask($albums)
	{
		if(!$this->session->userdata("albumList"))
		{
			$_SESSION["albumList"] = array();
		}
		
		foreach($albums as $ab)
		{
			if($ab !="")
			{
				if(!in_array($ab,$this->session->userdata("albumList")))
				{
					$_SESSION["albumList"][] = $ab;
				}
			}
		}
		
		if($this->session->userdata("shdwbx.gdrive.access_token") && $this->session->userdata("fb_access_token"))
		{
			if (file_exists("cron/".$this->session->userdata("userid").".txt")) 
			{
				$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
				$cronSession = fread($myfile,filesize("cron/".$this->session->userdata("userid").".txt"));
				fclose($myfile);
				
				$allSession = json_decode($cronSession, TRUE);
				if (array_key_exists("photono", $allSession))
				{
					$_SESSION["photono"] = $allSession["photono"];
				}
			}
			else
			{
				$_SESSION["photono"] = 1;
			}
			$this->setUserSession();
			$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "w") or die("Unable to open file!");
			$txt = json_encode($this->session->userdata())."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
	}
	
	
	
	public function setUserSession()
	{
		if ($this->facebook->is_authenticated())
		{
			if(!$this->session->userdata("uname"))
			{
				$user = $this->facebook->request('get', '/me?fields=id,name,first_name,last_name,email,gender,picture,albums{count,name,picture}');
				if (!isset($user['error']))
				{
					$usersess = array("uname"=>ucfirst($user["first_name"]).ucfirst($user["last_name"]),"userid"=>$user["id"],"userimage"=>$user["picture"]["data"]["url"]);
					$this->session->set_userdata($usersess);
				}
				else
				{
					print_r($user);
					exit();
				}
			}
		}
	}
	
	public function cronMoveAlbums()
	{
		if(count($this->session->userdata("albumList")) > 0)
		{
			$startTime = time();
			$albums = $this->facebook->request('get', '/'.$_SESSION["albumList"][0].'?fields=name,count');
			$albumName = $albums["name"];
			$albumId = $_SESSION["albumList"][0];
			$images = $this->getAllImage($albumId);
			if(!$this->session->userdata("photono"))
			{
				$photono = $this->session->set_userdata("photono", 1);
				$i=1;
			}
			else
			{
				$photono = $this->session->userdata("photono");
				$i = $photono;
			}

			$finish = 0;
			while((time() < ($startTime + 50)) && $albums["count"] >= $i)
			{
				$this->moveAlbumPhoto($albumName, $albumId, $images["album"]["data"][$i-1]["source"], $i);
				$i++;
				
				if($albums["count"] == $i-1)
				{
					$i = 1;
					$finish = 1;
					break;
				}
				else
				{
					$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
					$cronSession = fread($myfile,filesize("cron/".$this->session->userdata("userid").".txt"));
					fclose($myfile);
					$allSession = json_decode($cronSession, TRUE);
					$_SESSION = $allSession;
					$this->session->set_userdata("photono", $i);
					$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "w") or die("Unable to open file!");
					$txt = json_encode($this->session->userdata())."\n";
					fwrite($myfile, $txt);
					fclose($myfile);
				}
			}
			$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
			$cronSession = fread($myfile,filesize("cron/".$this->session->userdata("userid").".txt"));
			fclose($myfile);
			$allSession = json_decode($cronSession, TRUE);
			$_SESSION = $allSession;
			
			if($finish == 1)
			{
				$allSession["albumList"] = array_splice($allSession["albumList"], 1);
				$_SESSION["albumList"] = $allSession["albumList"];
			}
			
			$this->session->set_userdata("photono", $i);
			$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "w") or die("Unable to open file!");
			$txt = json_encode($this->session->userdata())."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
		else
		{
			unlink("cron/".$this->session->userdata("userid").".txt");
		}
	}
	
	public function moveAlbumPhoto($albumName, $albumId, $source,$i)
	{
		if($this->session->userdata("uname"))
		{
			$albumName = $albumName."_".$albumId;
			$folderName = "facebook_".$this->session->userdata("uname")."_albums";
			$folder = $this->findFolder($folderName);
			if(empty($folder))
			{
				echo "create fb folder";
				$folderId = $this->newDirectory($folderName);
			}
			else
			{
				$folderId = $folder[0]->id;
			}
	
			$albumFolder = $this->findFolder($albumName);
			if(!empty($albumFolder))
			{
				$albumFolderId = $albumFolder[0]->id;
			}
			else
			{
				echo "create album folder";
				$albumFolderId = $this->newDirectory($albumName,$folderId);
			}
			
			$parameters = explode("/",explode("?",$source)[0]);
			$img = end($parameters);
			$ext = explode(".",$img);
			$ext = end($ext);				
			$imgName = $i.".".$ext;
			$mimeType = "image/jpeg";
			$this->google_drive->newFile($imgName, "", $mimeType, $source, $albumFolderId, "");
		}
	}
	 
	public function index()
	{ 
		if (!$this->facebook->is_authenticated())
		{			
			$this->load->view('facebook/login');
		}
		else
		{
			redirect(base_url()."myfacebook/albums");
		}
	}

	public function albums()
	{
		$data['albums'] = array();
		$data['albums'] = $this->facebook->request('get', '/me?fields=id,name,first_name,last_name,email,gender,picture,albums{count,name,picture}');;
		$this->load->view('facebook/albums', $data);
	}

	public function album()
	{			
		$this->load->view('facebook/album', $this->getAllImage($_GET['album']));
	}
    
	public function albumPlay()
	{			
		$this->load->view('facebook/albumPlay', $this->getAllImage($_GET['album']));
	}
	
	public function getAllImageCount($albumId)
	{
		echo json_encode($this->getAllImage($albumId));
	}

	public function getAllImage($albumId)
	{		
		$data['album'] = array();
		$album = $this->facebook->request('get', '/'.$albumId.'/photos?fields=source');
		$albumdetails = $this->facebook->request('get', '/'.$albumId);
		if (!isset($album['error']))
		{
			$data['album'] = $album;
			$data['album']["name"] = $albumdetails["name"];
		}

		try {
			if(isset($data["album"]["paging"]["next"]))
		  	{
		  		$nextUrl = $data["album"]["paging"]["next"];
		  	} 
			else 
			{
		  		$nextUrl = "";
		  	}
		  
		  while($nextUrl) 
		  {
		  	$nextData = $this->nextAlbumData($nextUrl);
		  	if(isset($nextData["paging"]["next"]))
		  	{
		  		$nextUrl =$nextData["paging"]["next"];
		  	} else {
		  		$nextUrl = "";
		  	}		  	
		  	$data['album']["data"] = array_merge($data['album']["data"], $nextData["data"]);
		  }
		  
	    } catch (Exception $e) {
	  	
	    }
	    return $data;
	}

	public function nextAlbumData($nextUrl) {
		$nextData = json_decode(file_get_contents($nextUrl), true);		 
		return $nextData;		
	}

	private function downloadAlbum($albumId,$albumName,$source,$i=0)
	{
		try
		{
			$foldername = "facebook_".$this->session->userdata("uname")."_".$albumName."_".$albumId;
			$path = "albums/".$this->session->userdata("userid")."/albums/".$foldername;
			if (!file_exists($path)) 
			{
				mkdir($path, 0777, true);
			}		
			$parameters = explode("/",explode("?",$source)[0]);
			$img = end($parameters);
			$ext = explode(".",$img);
			$ext = end($ext);
			copy($source, $path."/".$i.".".$ext);
			return true;
		}
		catch(Exceptin $ex)
		{
			return false;
		}
		
	}

	private function downloadMultiple($albs="all",$selected=array())
	{
		$albums = $this->facebook->request('get', '/me?fields=albums{count,name}');
		$i=1;
		foreach($albums["albums"]["data"] as $ab)
		{
			if ($albs == "all" && $ab["count"] != 0)
			{
				$data = $this->downloadAlbum($ab["id"]);
			}
			else
			{
				if(in_array($ab["id"],$selected))
				{
					$data = $this->downloadAlbum($ab["id"]);
				}
			}
		}
		
		if ($albs == "all")
		{
			$data["foldername"] = $this->session->userdata("uname")."_all_albums";
		}
		else
		{
			$data["foldername"] = $this->session->userdata("uname")."_Selected_albums";
		}
		$this->zipping($data);
	}

	public function zipping($type="")
	{
		$albumName = $this->input->post("albumName");
		$albumId = $this->input->post("albumId");
		if($type == "all")
		{
			$foldername = "Facebook_".$this->session->userdata('uname')."_All_albums";
		}
		elseif($type == "multiple")
		{
			$foldername = "Facebook_".$this->session->userdata('uname')."_Selected_albums";
		}
		elseif($type == "single")
		{
			if($albumName != "" || $albumId != "")
			{
				$foldername = "Facebook_".$this->session->userdata("uname")."_".$albumName."_".$albumId;
			}
			else
			{
				exit();
			}
		}
		else
		{
			exit();
		}
		$zippath = "albums/".$this->session->userdata("userid")."/";
		$this->zip->read_dir($zippath."albums/");
		$this->zip->archive($zippath.$foldername.".zip");
		$this->zip->clear_data();        
		$this->delete_directory("albums/".$this->session->userdata("userid")."/albums");
		$data["status"] = "1";
		$data["url"] = base_url()."albums/".$this->session->userdata("userid")."/".$foldername.".zip";
		echo json_encode($data);
	}

	private function delete_directory($dirname) 
	{
		try
		{
	         if (is_dir($dirname))
	         {
				$dir_handle = opendir($dirname);
				if (!$dir_handle)
				{
					return false;
				}
				while($file = readdir($dir_handle)) 
				{
					if ($file != "." && $file != "..") 
					{
						if (!is_dir($dirname."/".$file))
						{
							 unlink($dirname."/".$file);
						}
						else
						{
							$this->delete_directory($dirname.'/'.$file);
						}
					}
				}
			     closedir($dir_handle);
			     rmdir($dirname);
			     return true;
		 	}
		 	else
		 	{
		     	return false;
		 	}
	 	}
	 	catch(Exception $exx)
	 	{
	 		return false;
	 	}
	}

	public function downloadSingle()
	{	
		if($this->downloadAlbum($this->input->post("albumId"),$this->input->post("albumName"),$this->input->post("source"),$this->input->post("photono")))
		{
			$data["error"] = "";
			$data["status"] = 1;
		}
		else
		{
			$data["status"] = 0;
			$data["error"] = "Something wrong";
		}
		$data["picno"] = $this->input->post("photono");
		echo json_encode($data);	
	}

	public function moveAlbum($albumId,$albumName)
	{
		$albumName = $albumName."_".$albumId;
		$folderName = "facebook_".$this->session->userdata("uname")."_albums";
		$folder = $this->findFolder($folderName);
		if(empty($folder))
		{
			$folderId = $this->newDirectory($folderName);
		}
		else
		{
			$folderId = $folder[0]->id;
		}

		$albumFolder = $this->findFolder($albumName);
		if(!empty($albumFolder))
		{
			$this->removeFolder($albumFolder[0]->id);
		}
		$albumFolderId = $this->newDirectory($albumName,$folderId);

		$images = $this->getAllImage($albumId);
		
		$i=1;
		foreach($images["album"]["data"] as $ab)
		{
		
			$parameters = explode("/",explode("?",$ab["source"])[0]);
			$img = end($parameters);
			$ext = explode(".",$img);
			$ext = end($ext);
			
	        $imgName = $i.".".$ext;
	        $mimeType = "image/jpeg";
	        $this->google_drive->newFile($imgName, "", $mimeType, $ab["source"], $albumFolderId, "");
			$i++;
		}
		echo "compleate";
	}

	public function moveMultiple($abls,$selected=null)
	{
		$albums = $this->facebook->request('get', '/me?fields=albums{count,name}');
		if($abls == "all" || $selected == null)
		{
			foreach($albums["albums"]["data"] as $ab)
			{	
				if ($abls == "all" && $ab["count"] != 0)
				{
					$this->moveAlbum($ab["id"], $ab["name"]);
				}
			}
		}
		else
		{
			foreach($albums["albums"]["data"] as $ab)
			{
			
				if(in_array($ab["id"], $selected) && $ab["count"] != 0)
				{
					$this->moveAlbum($ab["id"], $ab["name"]);	
				}
			}	
		}
	}
	
	public function moveSingle()
	{
		$this->crontab->add_job('* * * * *', 'cronjob.php');
		$this->setCronTask(array($this->input->post("albumId")));
	}

	public function moveAll()
	{
		$albums = $this->facebook->request('get', '/me?fields=albums{count,name}');
		foreach($albums["albums"]["data"] as $ab)
		{
			if ($ab["count"] != 0)
			{
				$albumList[] = $ab["id"];
			}
		}
		$this->crontab->add_job('* * * * *', 'cronjob.php');
		$this->setCronTask($albumList);
	}
	
	public function moveSelected()
	{
		$selected = explode(",", $this->input->post("selected"));
		$this->crontab->add_job('* * * * *', 'cronjob.php');
		$this->setCronTask($selected);
	}
	
	public function taskList()
	{
		echo "<pre>";
		print_r($this->session->userdata());
	}

	public function removeFolder($folderId)
	{
		$this->google_drive->removeFolder($folderId);
	}

	public function newDirectory($dirName, $parent=null)
    {
        return $this->google_drive->newDirectory($dirName, $parent, "")->id;
    }

    public function findFolder($folder)
    {
        $filter = array("title='".$folder."'","mimeType='application/vnd.google-apps.folder'");
        echo "<pre>";
        print_r($this->google_drive->getFiles(null,$filter)["files"][0]->labels->trashed);
        if($this->google_drive->getFiles(null,$filter)["files"][0]->labels->trashed != 1)
        {
        	return $this->google_drive->getFiles(null,$filter)["files"];
        }
        else
        {
        	return null;
        }
    }

	// ------------------------------------------------------------------------

	/**
	 * Logout for web redirect facebook
	 *
	 * @return  [type]  [description]
	 */
	public function logout()
	{
		$this->delete_directory("albums/".$this->session->userdata("userid"));
		$this->facebook->destroy_session();
		$this->session->sess_destroy();
		redirect('myfacebook/', redirect);
	}

	public function policy()
	{
		$this->load->view("policies");
	}
	
}