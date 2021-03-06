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
		if ( ! $this->facebook->is_authenticated() && $this->router->fetch_method() != "index")
		{
			redirect(base_url()."myfacebook/");
		}
		if ($this->input->ip_address() == $_SERVER['SERVER_ADDR'])
		{
			$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			$url = $url_array[0];	
			$config = array("client_id"=>"CLIENT_ID", "client_secret"=>"CLIENT_SECRET", "redirect_uri"=>$url);
			$this->load->library("google_drive", $config);
		}
		$this->setUserSession();
		$this->setOldTask();
	}
	
	public function googleLogin()
	{
		$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$url = $url_array[0];		
		$config = array("client_id"=>"CLIENT_ID", "client_secret"=>"CLIENT_SECRET", "redirect_uri"=>$url);		
		$this->load->library('google_drive', $config);
		redirect(base_url()."myfacebook/albums");
	}
	
	public function setCronSession()
	{
		if ($this->input->ip_address() == $_SERVER['SERVER_ADDR'])
		{
			$dirname = "cron";
			try
			{
				$map = directory_map($dirname, 1);       
				if (empty($map))
				{
					$this->crontab->remove_job('* * * * *', 'cronjob.php');
				} else
				{
					$myfile = fopen($dirname."/".$map[0], "r") or die("Unable to open file!");
					$cronSession = fread($myfile, filesize($dirname."/".$map[0]));
					fclose($myfile);
					$_SESSION = json_decode($cronSession, TRUE);
				}
		 	} catch (Exception $exx)
		 	{
		 		return false;
		 	}
		}		
	}
	
	public function setOldTask()
	{
		$dirname = "cron/".$this->session->userdata("userid").".txt";
		if (file_exists($dirname))
		{
			$myfile = fopen($dirname, "r") or die("Unable to open file!");
			$cronSession = fread($myfile, filesize($dirname));
			fclose($myfile);
			$allSession = json_decode($cronSession, TRUE);
			if (array_key_exists("albumList", $allSession))
			{
				$_SESSION["albumList"] = $allSession["albumList"];
			}
		} else
		{
			$_SESSION["albumList"] = array();
		}
		
	}
	
	public function setCronTask($albums)
	{
		if ( ! $this->session->userdata("albumList"))
		{
			$_SESSION["albumList"] = array();
		}
		
		foreach ($albums as $ab)
		{
			if ($ab != "")
			{
				if ( ! in_array($ab, $this->session->userdata("albumList")))
				{
					$_SESSION["albumList"][] = $ab;
				}
			}
		}
		
		if ($this->session->userdata("shdwbx.gdrive.access_token") && $this->session->userdata("fb_access_token"))
		{
			if (file_exists("cron/".$this->session->userdata("userid").".txt")) 
			{
				$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
				$cronSession = fread($myfile, filesize("cron/".$this->session->userdata("userid").".txt"));
				fclose($myfile);
				
				$allSession = json_decode($cronSession, TRUE);
				if (array_key_exists("photono", $allSession))
				{
					$_SESSION["photono"] = $allSession["photono"];
				}
			} else
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
			if ( ! $this->session->userdata("uname"))
			{
				$user = $this->facebook->request('get', '/me?fields=id,name,first_name,last_name,email,picture,albums{count,name,picture}');
				if ( ! isset($user['error']))
				{
					$usersess = array("uname"=>ucfirst($user["first_name"]).ucfirst($user["last_name"]), "userid"=>$user["id"], "userimage"=>$user["picture"]["data"]["url"]);
					$this->session->set_userdata($usersess);
				} else
				{
					print_r($user);
					exit();
				}
			}
		}
	}
	
	public function cronMoveAlbums()
	{
		if (count($this->session->userdata("albumList")) > 0)
		{
			$startTime = time();
			foreach ($this->getAllAlbumList()["albums"]["data"] as $list)
			{
				if ($list["id"] == $_SESSION["albumList"][0])
				{
					$albums["count"] = $list["count"];
					$albums["name"] = $list["name"];
					$albums["id"] = $list["id"];
				}
			}
			$albumName = $albums["name"];
			$albumId = $_SESSION["albumList"][0];
			$images = $this->getAllImage($albumId);
			if ( ! $this->session->userdata("photono"))
			{
				$photono = $this->session->set_userdata("photono", 1);
				$i = 1;
			} else
			{
				$photono = $this->session->userdata("photono");
				$i = $photono;
			}

			$finish = 0;
			while ((time() < ($startTime + 50)) && $albums["count"] >= $i)
			{
				$this->moveAlbumPhoto($albumName, $albumId, $images["album"]["data"][$i - 1]["source"], $i);
				$i++;
				if ($albums["count"] == $i - 1)
				{
					$i = 1;
					$finish = 1;
					break;
				} else
				{
					$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "r") or die("Unable to open file!");
					$cronSession = fread($myfile, filesize("cron/".$this->session->userdata("userid").".txt"));
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
			$cronSession = fread($myfile, filesize("cron/".$this->session->userdata("userid").".txt"));
			fclose($myfile);
			$allSession = json_decode($cronSession, TRUE);
			$_SESSION = $allSession;
			if ($finish == 1)
			{
				$allSession["albumList"] = array_splice($allSession["albumList"], 1);
				$_SESSION["albumList"] = $allSession["albumList"];
			}
			$this->session->set_userdata("photono", $i);
			$myfile = fopen("cron/".$this->session->userdata("userid").".txt", "w") or die("Unable to open file!");
			$txt = json_encode($this->session->userdata())."\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		} else
		{
			unlink("cron/".$this->session->userdata("userid").".txt");
		}
	}
	
	public function moveAlbumPhoto($albumName, $albumId, $source, $i)
	{
		if ($this->session->userdata("uname"))
		{
			$albumName = $albumName."_".$albumId;
			$folderName = "facebook_".$this->session->userdata("uname")."_albums";
			$folder = $this->findFolder($folderName);
			if (empty($folder))
			{
				echo "create fb folder";
				$folderId = $this->newDirectory($folderName);
			} else
			{
				$folderId = $folder[0]->id;
			}
	
			$albumFolder = $this->findFolder($albumName);
			if ( ! empty($albumFolder))
			{
				$albumFolderId = $albumFolder[0]->id;
			} else
			{
				echo "create album folder";
				$albumFolderId = $this->newDirectory($albumName, $folderId);
			}
			
			$parameters = explode("/", explode("?", $source)[0]);
			$img = end($parameters);
			$ext = explode(".", $img);
			$ext = end($ext);				
			$imgName = $i.".".$ext;
			$mimeType = "image/jpeg";
			$this->google_drive->newFile($imgName, "", $mimeType, $source, $albumFolderId, "");
		}
	}
	 
	public function index()
	{ 
		if ( ! $this->facebook->is_authenticated())
		{			
			$this->load->view('facebook/login');
		} else
		{
			redirect(base_url()."myfacebook/albums");
		}
	}

	public function albums()
	{
		$data['albums'] = array();
		$data['albums'] = $this->getAllAlbumList();
		$this->load->view('facebook/albums', $data);
	}
	
	public function getAllAlbumList()
	{
		$dirname = "fbdata/".$this->session->userdata("userid")."/allAlbums.txt";
		$path = "fbdata/".$this->session->userdata("userid");
		if (file_exists($dirname)) 
		{
			$myfile = fopen($dirname, "r") or die("Unable to open file!");
			$allAlbums = fread($myfile, filesize($dirname));
			fclose($myfile);
			$data = json_decode($allAlbums, TRUE);
		} else
		{
			if ( ! file_exists($path)) 
			{
				mkdir($path, 0777, true);
			}
			$data = $this->facebook->request('get', '/me?fields=id,name,first_name,last_name,email,picture,albums{id,count,name,picture}');
			$allAlbums = fopen($dirname, "w") or die("Unable to open file!");
			$txt = json_encode($data)."\n";
			fwrite($allAlbums, $txt);
			fclose($allAlbums);
		}
		return $data;
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
		$dirname = "fbdata/".$this->session->userdata("userid")."/".$albumId.".txt";
		$path = "fbdata/".$this->session->userdata("userid");
		if (file_exists($dirname)) 
		{
			$myfile = fopen($dirname, "r") or die("Unable to open file!");
			$allAlbums = fread($myfile, filesize($dirname));
			fclose($myfile);
			$data = json_decode($allAlbums, TRUE);
		} else
		{
			$data['album'] = array();
			$albums = $this->facebook->request('get', '/'.$albumId.'?fields=photos.limit(100){images,id},name');
			$newdata = array();
			if (!isset($album['error']))
			{
				$data['album'] = $albums;
				$newdata['album']["name"] = $albums["name"];
			}
			
			try {
				if (isset($data["album"]["photos"]["paging"]["next"]))
			  	{
			  		$nextUrl = $data["album"]["photos"]["paging"]["next"];
			  		
			  	} else {
			  		$nextUrl = "";
			  	}
				  
				while ($nextUrl) {
				  	$nextData = $this->nextAlbumData($nextUrl);
				  	
				  	if(isset($nextData["paging"]["next"]))
				  	{
				  		$nextUrl = $nextData["paging"]["next"];
				  	} else {
				  		$nextUrl = "";
				  	}
				  	
					$data['album']['photos']["data"] = array_merge($data['album']['photos']['data'], $nextData['data']);
				}
				
				foreach ($data['album']['photos']["data"] as $album)
				{
					$newdata["album"]["data"][] = array("source"=>$album["images"][0]["source"],"id"=>$album["id"]);
				}
				
				if (!file_exists($path)) {
					mkdir($path, 0777, true);
				}
				  
				$allAlbums = fopen($dirname, "w") or die("Unable to open file!");
				$txt = json_encode($newdata)."\n";
				fwrite($allAlbums, $txt);
				fclose($allAlbums);
			} catch (Exception $e) {
				echo "catch";
		    	}
		  }
		return $data;
	}

	public function nextAlbumData($nextUrl)
	{
		$nextData = json_decode(file_get_contents($nextUrl), true);		 
		return $nextData;		
	}

	private function downloadAlbum($albumId, $albumName, $source, $i = 0)
	{
		try
		{
			$foldername = "facebook_".$this->session->userdata("uname")."_".$albumName."_".$albumId;
			$path = "albums/".$this->session->userdata("userid")."/albums/".$foldername;
			if ( ! file_exists($path)) 
			{
				mkdir($path, 0777, true);
			}		
			$parameters = explode("/", explode("?", $source)[0]);
			$img = end($parameters);
			$ext = explode(".", $img);
			$ext = end($ext);
			copy($source, $path."/".$i.".".$ext);
			return true;
		} catch (Exceptin $ex)
		{
			return false;
		}
		
	}

	public function zipping($type = "")
	{
		$albumName = $this->input->post("albumName");
		$albumId = $this->input->post("albumId");
		if ($type == "all")
		{
			$foldername = "Facebook_".$this->session->userdata('uname')."_All_albums";
		} elseif ($type == "multiple")
		{
			$foldername = "Facebook_".$this->session->userdata('uname')."_Selected_albums";
		} elseif ($type == "single")
		{
			if ($albumName != "" || $albumId != "")
			{
				$foldername = "Facebook_".$this->session->userdata("uname")."_".$albumName."_".$albumId;
			} else
			{
				exit();
			}
		} else
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
				if ( ! $dir_handle)
				{
					return false;
				}
				while ($file = readdir($dir_handle)) 
				{
					if ($file != "." && $file != "..") 
					{
						if ( ! is_dir($dirname."/".$file))
						{
							 unlink($dirname."/".$file);
						} else
						{
							$this->delete_directory($dirname.'/'.$file);
						}
					}
				}
				 closedir($dir_handle);
				 rmdir($dirname);
				 return true;
		 	} else
		 	{
				return false;
		 	}
	 	} catch (Exception $exx)
	 	{
	 		return false;
	 	}
	}

	public function downloadSingle()
	{	
		if ($this->downloadAlbum($this->input->post("albumId"), $this->input->post("albumName"), $this->input->post("source"), $this->input->post("photono")))
		{
			$data["error"] = "";
			$data["status"] = 1;
		} else
		{
			$data["status"] = 0;
			$data["error"] = "Something wrong";
		}
		$data["picno"] = $this->input->post("photono");
		echo json_encode($data);	
	}
	
	public function moveSingle()
	{
		$this->crontab->add_job('* * * * *', 'cronjob.php');
		$this->setCronTask(array($this->input->post("albumId")));
	}

	public function moveAll()
	{
		$albums = $this->getAllAlbumList();
		foreach ($albums["albums"]["data"] as $ab)
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

	public function removeFolder($folderId)
	{
		$this->google_drive->removeFolder($folderId);
	}

	public function newDirectory($dirName, $parent = null)
	{
		return $this->google_drive->newDirectory($dirName, $parent, "")->id;
	}

	public function findFolder($folder)
	{
		$filter = array("title='".$folder."'", "mimeType='application/vnd.google-apps.folder'");
		if ($this->google_drive->getFiles(null, $filter)["files"][0]->labels->trashed != 1)
		{
			return $this->google_drive->getFiles(null, $filter)["files"];
		} else
		{
			return null;
		}
	}
	
	public function cancledownload()
		{
		$this->delete_directory("albums/".$this->session->userdata("userid"));
		$data["status"] = "canceled";
		echo json_encode($data);
		}

		public function cancleupload()
		{
		unlink("cron/".$this->session->userdata("userid").".txt");
		$data["status"] = "canceled";
		echo json_encode($data);
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
