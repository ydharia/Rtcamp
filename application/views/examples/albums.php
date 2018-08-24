<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link href="<?php echo base_url('assets/css/myStyle.css')?>" rel = "stylesheet" type = "text/css"  />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="download-file" id="download-file">
            <div class="zip-div">
                <div align="right" >
                    <i class="fa fa-close" id="closeDiv" onclick="closeDownload();" style="font-size: 20px;"></i>
                </div>
                <div class="zip-title">
                    Download
                </div>
                <div class="progress-bar-div">
                    <div class="progress-bar-out" id="progress-bar-out">
                        <div class="progress-bar-in" id="progress-bar-in">
                            
                        </div>
                        <div id="progress-bar-in-counter" style="padding: 10px;">
                            
                        </div>
                    </div>
                </div>

                <div class="zip-title" style="margin-top: 1%;">
                    Download Album Progress
                </div>
                <div class="progress-bar-div">
                    <div class="progress-bar-out-album">
                        <div class="progress-bar-in-album" id="progress-bar-in-album">
                            
                        </div>
                        <div id="progress-bar-in-album-counter" style="padding: 10px;">
                            
                        </div>
                    </div>
                </div>

                <div class="download-button" id="download-button">
                    
                </div>
            </div>
    </div>

    <div class="download-file" id="driveFiles" style="height: auto;" >
            <div class="zip-div" style="height: auto;" >
                <div align="right" >
                    <i class="fa fa-close" id="closeDiv" onclick="$('#driveFiles').hide();" style="font-size: 20px;"></i>
                </div>
                <div class="zip-title">
                    Google Drive
                </div>
                <div class="progress-bar-div">
                    Your Album Start To Backup in Your Google Drive.
                </div>
            
                <div class="download-button" id="download-button">
                    
                </div>
            </div>
    </div>
    <div class="bg"></div>  
    <div class="header">
            <div class="profile">
                <div class="profileName">
                        
                    <span><img src="<?=$this->session->userdata("userimage")?>" align="center" ><?=$this->session->userdata("uname")?> 
                        <a href="<?=base_url().'example/logout'?>" title="Logout" class="logout">
                            <i class="fa fa-power-off"></i>
                        </a>
                    </span>
                </div>
            </div>
        
            <div class="menu">
                <div class="mobileMenu">
                    <i class="fa fa-bars fa-times" style="color: white;"></i>
                </div>
                <ul>
                    <li>   
                        <a href="javascript:;" onclick="download('all',event)" title="Download Selected Albums" class="downloadAll">
                            <i class="fa fa-download"></i> ALL
                        </a>                 
                    </li>
                    <li>   
                        <a href="javascript:;" title="Backup Selected Albums" class="downloadSelectedAlbum" >
                            <i class="fa fa-download"></i> SELECTED
                        </a>                 
                    </li>
                    <?php
				    if($this->session->userdata("shdwbx.gdrive.access_token"))
				    {
				    ?>
                    <li>   
                        <a href="javascript:;" title="Download All Albums" class="downloadAllAlbum">
                            <i class="fa fa-google"> </i> DRIVE ALL
                        </a>                 
                    </li>
                    <li>   
                        <a href="javascript:;" title="Backup All Albums" class="driveAllAlbum">
                            <i class="fa fa-google"> </i> DRIVE SELECTED
                        </a>
                    </li>
                    <?php
                	}
                	else
                	{
                		?>
                		<li>   
	                        <a href="<?php echo base_url()."example/googleLogin";?>" title="Download All Albums" class="downloadAllAlbum">
	                            <i class="fa fa-google"> </i> DRIVE ALL
	                        </a>                 
	                    </li>
	                    <li>   
	                        <a href="<?php echo base_url()."example/googleLogin";?>" title="Backup All Albums" class="driveAllAlbum">
	                            <i class="fa fa-google"> </i> DRIVE SELECTED
	                        </a>
	                    </li>
                		<?php
                	}
                    ?>
                </ul>
            </div>
        </div>
        <div class="container">

		<!-- <a style="color:white;" class="pull-right" href="http://myfoodstore.in/policies">privacy policy</a> -->
	
    <?php
    if($this->session->userdata("shdwbx.gdrive.access_token"))
    {
    ?>
        <div class="grid-container">                
            <?php 
            if(count($albums["albums"]) > 0)
            {
            foreach($albums["albums"]["data"] as $album) { ?> 
                <?php if($album["count"] != 0) { ?>    
                    <a href="javascript:;">
                        <div class="grid-item" >  
                            <div class="gridData" >                                                 
                                <div class="gridItemImg" style="background-image: url('<?php print_r($album["picture"]["data"]["url"]); ?>')" onclick="window.location.href='<?php echo base_url('example/album?album='.$album['id'].'&albumName='.$album['name'])?>'"  >
                                </div>
                                <div class="gridItemInfo">
                                    <span class="imgName" >
                                        <label>
                                            <input type="checkbox" class="selectedAlbums" value="<?=$album['id']?>"> 
                                            <i class="fa fa-square-o" aria-hidden="true"></i> 
                                            <span class="name"> <?php print_r($album["name"].' ('.$album["count"].')'); ?> </span>
                                        </label>
                                    </span>
                                    <span class="imgIcons">
                                        <i class="fa fa-download" onclick="download('single',event,'<?=$album['id']?>')" aria-hidden="true" style="border-right: 1px solid;" ></i>
                                        <i class="fa fa-play" onclick="window.location.href='<?php echo base_url('example/albumPlay?album='.$album['id'])?>'" aria-hidden="true" style="border-right: 1px solid;"  ></i>
                                        <i class="fa fa-google" onclick="moveAlbum('<?=$album['id']?>','<?=$album['name']?>',event)" aria-hidden="true" ></i>                                     
                                    </span>
                                </div>
                            </div>
                        </div>                                 
                    </a>

                <?php } ?>
            <?php } ?>
            <?php } ?>
        </div>
 
    <?php
    }
    else
    {
    	?>
        <div class="grid-container">                
            <?php foreach($albums["albums"]["data"] as $album) { ?> 
                <?php if($album["count"] != 0) { ?>    
                	<a href="javascript:;">
                        <div class="grid-item" >  
                            <div class="gridData" >                                                 
                                <div class="gridItemImg" style="background-image: url('<?php print_r($album["picture"]["data"]["url"]); ?>')" onclick="window.location.href='<?php echo base_url('example/album?album='.$album['id'].'&albumName='.$album['name'])?>'"  >
                                </div>
                                <div class="gridItemInfo">
                                    <span class="imgName" >
                                        <label>
                                            <input type="checkbox" class="selectedAlbums" value="<?=$album['id']?>"> 
                                            <i class="fa fa-square-o" aria-hidden="true"></i> 
                                            <span class="name"> <?php print_r($album["name"].' ('.$album["count"].')'); ?> </span>
                                        </label>
                                    </span>
                                    <span class="imgIcons">
                                        <i class="fa fa-download" onclick="download('single',event,'<?=$album['id']?>')" aria-hidden="true" style="border-right: 1px solid;" ></i>
                                        <i class="fa fa-play" onclick="window.location.href='<?php echo base_url('example/albumPlay?album='.$album['id'])?>'" aria-hidden="true" style="border-right: 1px solid;"  ></i>
                                        <i class="fa fa-google" onclick="window.location.href='<?php echo base_url()."example/googleLogin";?>'" aria-hidden="true" ></i>                                     
                                    </span>
                                </div>
                            </div>
                        </div>                                 
                    </a>
                <?php } ?>
            <?php } ?>
        </div>
  	
    	<?php
    }
    ?>
    	   </div>


           
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous"></script>

    <script src="<?php echo base_url('assets/js/myScript.js')?>" ></script>
</body>
</html>