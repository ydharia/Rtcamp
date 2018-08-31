<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link href="<?php echo base_url('assets/css/myStyle.css')?>" rel = "stylesheet" type = "text/css"  />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style type="text/css">
        .imagepreview{
            background-color: rgba(0,0,0,0.8);
            top:0;
            left: 0;
            width: 100%;
            height: 100%;
            position: fixed;
            display: none;
            z-index: 99999999;
        }
        .image-box{
	    background-color: transparent;
            
            margin-top: 10%;
            margin-bottom: 2%;
            margin-left: 5%;
            margin-right: 5%;
            width: auto;
            height: 500px;
            text-align: center; 
            border-radius: 10px;
        }
        
        .img-preview-box
        {
            opacity: 1;
            max-width:100%;
			max-height:450px;           
        }
    </style>
    <script type="text/javascript">
        function imgpreview(image) {
            document.getElementById("imagepreview").style.display = "block";
            $("#image-view").html();
            $("#image-view").html("<img src='"+image+"' class='img-preview-box' />");
        }
        function closePreview() {
            document.getElementById("imagepreview").style.display = "none";
        }

    </script>
</head>
<body >
    <div class="imagepreview" id="imagepreview">
        <div class="image-box" id="image-box">
            <div class="close-preview" align="right">
                <i class="fa fa-close" style="color:#fff;font-size:20px;padding: 10px;cursor:pointer;" onclick="closePreview();"></i>
            </div>
            <div id="image-view">
                
            </div>

        </div>
    </div>
    <div class="bg"></div>  
    <div class="header">
    	<div class="profile">
                <div class="profileName">
                    <span><img src="<?=$this->session->userdata("userimage")?>" align="center" ><?=$this->session->userdata("uname")?> 
                        <a href="<?=base_url().'myfacebook/logout'?>" title="Logout" class="logout">
                            <i class="fa fa-power-off"></i>
                        </a>
                    </span>
                </div>
            </div>
    	<div class="backDiv" onclick="location.href = '<?=base_url()?>';">
	        <div class="back">
	            <i class="fa fa-arrow-left" style="color: white;" ></i>
	        </div>        
	</div>
    
    </div>
    <div class="container">
        <div class="albumHeader">
            <?php echo $album["name"]; ?>
            <hr>
        </div>
        <div class="gridContainer" style="color:white;" style="grid-template-columns: auto auto auto auto;" >           
            <?php foreach($album["data"] as $img) { ?>                
                <a href="#" onclick="imgpreview('<?php print_r($img["source"]); ?>');"> 
                    <div class="grid-item" >  
                        <div class="gridData" style="background: transparent;" >                                                 
                            <div class="gridItemImg" style="background-image: url('<?php print_r($img["source"]); ?>')" >
                            </div>                          
                        </div>
                    </div>                                 
                </a>               
            <?php } ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous"></script>

    <script src="<?php echo base_url('assets/js/myScript.js')?>" ></script>
</body>
</html>