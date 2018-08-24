<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
class Example extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('asia/kolkata');
		$this->load->library('user_agent');
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
			redirect(base_url()."example/");
		}
		
		/*
		$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$url = $url_array[0];	
		$config = array("client_id"=>"962527986048-fko0boarhk09sclfu0igldkgj1po0p8d.apps.googleusercontent.com","client_secret"=>"By-esciDrlc7omI-_pVmHZag","redirect_uri"=>$url);
		$this->load->library('google_drive',$config);
		*/
	        $this->setUserSession();
 	      	$this->setOldTask();
	}
	
	public function googleLogin()
	{
		if(!$this->session->userdata("shdwbx.gdrive.access_token"))
		{
			$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$url = $url_array[0];		
			$config = array("client_id"=>"962527986048-fko0boarhk09sclfu0igldkgj1po0p8d.apps.googleusercontent.com","client_secret"=>"By-esciDrlc7omI-_pVmHZag","redirect_uri"=>$url);		
			$this->load->library('google_drive',$config);
		}
		redirect(base_url()."example/albums");
	}
	
	public function policy()
	{
		$this->load->view("policies");
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
			//$txt = print_r($this->session->userdata())."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
	}
	
	public function setUserSession()
	{
		$albums = array();
		if ($this->facebook->is_authenticated())
		{
			$albums = $this->facebook->request('get', '/me?fields=id,name,first_name,last_name,email,gender,picture,albums{count,name,picture}');
			if (!isset($albums['error']))
			{
				$data['albums'] = $albums;
				$usersess = array("uname"=>ucfirst($albums["first_name"]).ucfirst($albums["last_name"]),"userid"=>$albums["id"]);
				$this->session->set_userdata($usersess);
			}
			else
			{
				print_r($albums);
				exit();
			}
		}
		return $albums;
	}
	
	public function cronMoveAlbums()
	{
		if(count($this->session->userdata("albumList")) > 0)
		{
		$startTime = time();
			/*$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
			$cronSession = fread($myfile,filesize("cron/".$this->session->userdata("userid").".txt"));
			fclose($myfile);
			$allSession = json_decode($cronSession, TRUE);
			$_SESSION = $allSession;
		*/
			
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
				//$photono = $this->session->userdata("photono");
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
					//$txt = print_r($this->session->userdata())."\n";
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
			//$txt = print_r($this->session->userdata())."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
			
			
		}
		else
		{
			unlink("cron/".$this->session->userdata("userid").".txt");
			//$this->crontab->remove_job('* * * * *', 'cronjob.php');
		}
	}
	
	public function cktime()
	{
		$startTime = time();
		while((time() < ($startTime + 10)))
		{
			echo time().", ".($startTime+10)."<br>";
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
	
	 
	public function phpinfo()
	{
		phpinfo();
	}
	 
	public function index()
	{
		if (!$this->facebook->is_authenticated())
		{
			$this->load->view('examples/web');
		}
		else
		{
			redirect(base_url()."example/albums");
		}
	}

	
	public function web_login()
	{
		$this->load->view('examples/web');
	}


	public function albums()
	{
		$data['albums'] = array();
		$data['albums'] = $this->setUserSession();
		
		//$this->delete_directory("albums/".$this->session->userdata("userid"));		
		$this->load->view('examples/albums', $data);
	}

	public function album()
	{			
		$this->load->view('examples/album', $this->getAllImage($_GET['album']));
	}
	
	public function getAllImageCount($albumId)
	{
		$data['album'] = array();
		$album = $this->facebook->request('get', '/'.$albumId.'/photos?fields=source');
		if (!isset($album['error']))
		{
			$data['album'] = $album;
		}

		try {
			if(isset($data["album"]["paging"]["next"]))
		  	{
		  		$nextUrl = $data["album"]["paging"]["next"];
		  	} else {
		  		$nextUrl = "";
		  	}
		  
		  while($nextUrl) {
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
	    echo json_encode($data);
	    //return $data;
	}

	public function getAllImage($albumId)
	{
		$data['album'] = array();
		$album = $this->facebook->request('get', '/'.$albumId.'/photos?fields=source');
		if (!isset($album['error']))
		{
			$data['album'] = $album;
		}

		try {
			if(isset($data["album"]["paging"]["next"]))
		  	{
		  		$nextUrl = $data["album"]["paging"]["next"];
		  	} else {
		  		$nextUrl = "";
		  	}
		  
		  while($nextUrl) {
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
	    //echo json_encode($data);
	    return $data;
	}

	public function nextAlbumData($nextUrl) {
		$nextData = json_decode(file_get_contents($nextUrl), true);		 
		return $nextData;		
	}

	// ------------------------------------------------------------------------

	/**
	 * JS SDK login example
	 */
	public function js_login()
	{
		$this->load->view('examples/js');
	}

	// ------------------------------------------------------------------------

	/**
	 * AJAX request method for positing to facebook feed
	 */
	public function post()
	{
		header('Content-Type: application/json');

		$result = $this->facebook->request(
			'post',
			'/me/feed',
			['message' => $this->input->post('message')]
		);

		echo json_encode($result);
	}


	public function downloadAlbum($albumId,$i=0)
	{
		 $data["albumId"] = $albumId;
		 $albumsdetails = $this->facebook->request('get', '/'.$albumId.'/');
		// $album = $this->facebook->request('get', '/'.$albumId.'/photos?fields=source');
		// if (!isset($album['error']))
		// {
		// 	$data['album'] = $album;
		// }
		$foldername = "facebook_".$this->session->userdata("uname")."_".$albumsdetails["name"]."_".$albumsdetails["id"];
		
		$ab = $this->getAllImage($albumId)["album"]["data"][$i]["source"];
		$path = "albums/".$this->session->userdata("userid")."/albums/".$foldername;
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		
		$parameters = explode("/",explode("?",$ab)[0]);
		$img = end($parameters);
		$ext = explode(".",$img);
		$ext = end($ext);
		copy($ab, $path."/".$i.".".$ext);
		
		/*
		$foldername = "facebook_".$this->session->userdata("uname")."_".$albumsdetails["name"]."_".$albumsdetails["id"];
		$path = "albums/".$this->session->userdata("userid")."/albums/".$foldername;
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
			$i=1;

			foreach($album["album"]["data"] as $ab)
			{
				$parameters = explode("/",explode("?",$ab["source"])[0]);
				$img = end($parameters);
				$ext = explode(".",$img);
				$ext = end($ext);
				copy($ab['source'], $path."/".$i.".".$ext);
				$i++;
			}
		}
		$data["foldername"] = $foldername;
		$data["path"] = $path;
		
		*/
		
		//return $data;
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

			//testing ma time west ni karva mate
			// if($i == 2)
			// {
			// 	break;
			// }
			// $i++;
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

	private function zipping($data)
	{
		$zippath = "albums/".$this->session->userdata("userid")."/";
		$this->zip->read_dir($zippath."albums/");
	        $this->zip->archive($zippath.$data["foldername"].".zip");
	        // $this->zip->download('my_backup.zip');
	        $this->zip->clear_data();
        
		$this->delete_directory("albums/".$this->session->userdata("userid")."/albums");
	}


	private function delete_directory($dirname) 
	{
		try
		{
	         if (is_dir($dirname))
	         {
		           $dir_handle = opendir($dirname);
			     if (!$dir_handle)
			          return false;
			     while($file = readdir($dir_handle)) {
			           if ($file != "." && $file != "..") {
			                if (!is_dir($dirname."/".$file))
			                     unlink($dirname."/".$file);
			                else
		                     	$this->delete_directory($dirname.'/'.$file);
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
		sleep(1);
		$albumId = $this->input->post("albumId");
		$photono = $this->input->post("photono");
		//$albumId = "1169606213157432";
		$data = $this->downloadAlbum($albumId,$photono);
		
		//$this->zipping($data);

		//$data["message"] = "Save to server album success : ".$data["foldername"];

		echo json_encode($data);
	}
	

	public function downloadall()
	{
		if($this->input->post("albums") != "all")
		{
			$selected = explode(",", $this->input->post("selected"));
			$data = $this->downloadMultiple("selected",$selected);
		}
		else
		{
			$data = $this->downloadMultiple("all");
		}

		//$data = $this->downloadMultiple("all");
		
		$data["status"] = "Compleate";
		//$data["se"] = $selected[0];
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
		//$data["message"] = "compleate";
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
		//$this->moveAlbum($albumId,$albumName);
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
	 * Logout for web redirect example
	 *
	 * @return  [type]  [description]
	 */
	public function logout()
	{
		$this->delete_directory("albums/".$this->session->userdata("userid"));
		$this->facebook->destroy_session();
		redirect('example/', redirect);
	}


	public function isReady()
	{
		return $this->google_drive->isReady();	
	}
	
	
	public function getAllAlbumId()
	{
		$albums = $this->facebook->request('get', '/me?fields=albums{count,name}');
		foreach($albums["albums"]["data"] as $ab)
		{
			if($ab["count"] != 0)
			{
				$data["id"][] = $ab["id"];
				$data["name"][] = $ab["name"];
			}
		}
		echo json_encode($data);
	}
		
}