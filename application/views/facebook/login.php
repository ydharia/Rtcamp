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

        .wrapper {
            width: 30vw;
            margin: 25vh auto;
            border: 1px solid #eee;
            background: #fcfcfc;
            padding: 0 20px 20px;
            box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        input[type="text"] {
            border: 0px;
            border-bottom: 2px solid gray;
            width: 96%;
            padding: 0.7em;
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


        @media only screen and (max-width: 600px) {
            .wrapper {
                width: 80vw;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <h1>Login</h1>
    
    <p>
        <input type="text" name="" placeholder="Username" >
    </p>

    <p>
        <input type="text" name="" placeholder="Password" >
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
