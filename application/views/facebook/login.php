<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Facebook Login</title>

    <style>
        html {
            overflow: hidden;
        }

        body {
            padding: 0;
            margin: 0;
            font-family: Helvetica, Sans-serif;
            font-size: 16px;
            color: #333;
            line-height: 1.5;
            overflow: hidden;
        }
        
        .bg {
	    background-image: url("https://rtcampyash.myfoodstore.in/assets/images/bgimg.jpg");    
	    height: 100%; 
	    background-position: center;
	    background-repeat: no-repeat;
	    background-size: cover;
	    position: fixed;
	    width: 100%;
	    z-index:-1;
	}

        .wrapper {
            width: 400px;
            margin: 25vh auto;
            border: 1px solid #eee;
            background: #fcfcfc4f;
            padding: 0 20px 20px;
            box-shadow: 3px 3px 10px 1px black;
            overflow: hidden;
        }

        input[type="text"], input[type="password"] {
            border: 0px;
            border-bottom: 2px solid gray;
            width: 96%;
            padding: 0.7em;
            font-size: 1em;
            background-color: #ffffff94;
            box-shadow: 2px 2px 6px black;
            outline: none;
        }


        h1, h3 {
            text-align: center;
        }

        .login {
            text-align: center;
        }

        .btn {
            border: none;
            background: #2F5B85;
            color: #fff;
            font-size: 18px;
            padding: 6px 2px;
            margin: 20px auto;
            cursor: pointer;
            width: 100%;
            transition: background .6s ease;
        }

        .btn > a {
            text-decoration: none;
            color: white;
        }

        .btn:hover {
            background: #2f5b85c7;
        }

 	.btnPolicies > a {
	    text-decoration: none;
	    color: #2f5b85;
	    font-weight: bold;
	}

        @media only screen and (max-width: 1000px) {
            .wrapper {
                width: 70vw;
            }
        }
    </style>
</head>
<body>
<div class="bg"></div> 
<div class="wrapper">

    <h1>Login</h1>
    
    <p>
        <input type="text" name="" placeholder="Username" >
    </p>

    <p>
        <input type="password" name="" placeholder="Password" >
    </p>
    
    <?php if (!$this->facebook->is_authenticated()) { ?>

        <div class="login">
            <div class="btn">
                <a href="<?php echo $this->facebook->login_url(); ?>">Login</a>
            </div>
            <div class="btn">
                <a href="<?php echo $this->facebook->login_url(); ?>">Login With Facebook</a>
            </div>
            <div class="btnPolicies" style="text-align: right;">
                <a href="http://myfoodstore.in/policies">privacy policy</a>   
            </div>         
        </div>

    <?php } ?>
    
   

</div>

</body>
</html>
