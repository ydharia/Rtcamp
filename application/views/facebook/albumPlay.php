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
<body id="fullscreen" style="overflow: hidden;">
    <div class="bg"></div>      
        <div class="sider" id="sider" > 
            <?php foreach ($album["data"] as $img) { ?>                
                <div class="side" >
                    <img src="<?php echo $img['source']; ?>" alt="">
                </div>
            <?php } ?>
        </div>

        <div class="siderFooter">
            <div class="siderBtn">
                <center>
                    <button class="play" onclick="openFullscreen()" >
                        <i class="fa fa-television" aria-hidden="true"></i>
                    </button>
                    <button class="prev" onclick="prevSide()" >
                        <i class="fa fa-angle-up" aria-hidden="true"></i>
                    </button>            
                    <button class="play" onclick="playSider(event)" >
                        <i class="fa fa-play" aria-hidden="true"></i>
                    </button>                            
                    <button class="next" onclick="nextSide()" >
                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                    </button>
                    

                </center>
            </div>
        </div>        
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"
    integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
    crossorigin="anonymous"></script>
    <script src="<?php echo base_url('assets/js/myScript.js')?>" ></script>
    <script>
        window.onload = function () { 
            // playSider();
        }
    </script>
    
</body>
</html>