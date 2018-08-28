# Rtcamp Facebook Challenge

### Live Demo :

Url : https://rtcampyash.myfoodstore.in

In this Application user login with facebook and see all albums which is uploaded to facebook, user can see all there album photos in full screen slider, download the albums and move thre albums to google drive easily.

## Facebook Login:

User Login to Facebook. Asking for permission of name, email, photos. Application retrieve all the Albums of the Login User.

## Facebook Album Slider:

Album's will be display with a thumbnail and name. whenever user click the Play button then Preview that Album Photos in a Fullscreen Slideshow.

## Facebook Album Download:

#### Download Single Album

A 'Download icon' is Putting on Each Album thumbnail whenever the User click  on that button then in the background process all the photos of that album is Put on folder and create the zip of that Album with the name of Album name and Album id.

#### Download Multiple Albums

A checkbox is displayed in each album. when the user click the 'Download Selected icon' then All the selected Album put on the Folder and creating the zip in background.

#### Download All Albums

'Download All icon' link also Available to dowanload all Album put on the Folder and creating the zip in background.

* Download process : 

while Download Process is not Completing the Progressbar show you current downloading photo of your album.

## Facebook Albums To Google Drive :

#### Google Login : 

User Login to Google. Asking for permission of user profile, email and manage files and folder of google drive which is created by this application. Application retrieve all permission grant.

If user is Sign into Google for the First Time for moving the album into the Google Drive. After that Google can not Take Any Permission for Moving the Album to the google drive.

#### Move Single Album to Google Drive :

A 'Google icon' Link is display on Each Album thumbnail and when the User click that link the Album photos is move on the Google Drive.

#### Move Selected albums to Google Drive :

Using the checkbox user can select multiple Album and click the 'Drive Selected' then all the Selected Album move to the Google drive of the User.

#### Move All Albums to Google Drive :

'Drive All' It can Move all the Albums to the Google Drive.


* **How Google Drive Moving Work.**

When user click on move album(s) then new Cron job is added to server.
moving work is assigned to **_Cron job_** so user can **logout or shutdown device without waiting for complete moving process of albums.**
when all albums are moved to google drive then **_cron job_ is removed from server.**

# Technologies

### Platforms : PHP

### Framework : Codeignitor

### Scripting Language : JQuery, JQuery AJAX, JavaScript

### Library Used :

[Facebook PHP SDK](https://github.com/darkwhispering/facebook-sdk-codeigniter)

[Google Drive API](https://github.com/uchilaka/CI_GoogleDrive)

[Crontab](https://github.com/biozshock/crontab)


## Setup / Install : 

### 1. Facebook setup.

open -> application/config/facebook.php

edit 2 lines with your facebook_app_id and facebook_app_secret

    $config['facebook_app_id']              = 'APP_API_KEY';
    $config['facebook_app_secret']          = 'API_SECRET_KEY'; 

put your app id and app secret key
get app id [click here](https://developers.facebook.com/apps/) 

get APP_ID and APP_SECRET_KEY from 

1. Select your application
2. goto basic -> setting and get your keys.

### 2. Google Drive setup.

open -> application/controllers/Myfacebook.php
edit CLIENT_ID and CLIENT_SECRET in construct and googleLogin() function
        public function \__construct()
        {
            .....
            $config = array("client_id"=>"__CLIENT_ID__", "client_secret"=>"__CLIENT_SECRET__", "redirect_uri"=>$url);
            ....
        }
        public function googleLogin()
        {
            .....
            $config = array("client_id"=>"__CLIENT_ID__", "client_secret"=>"__CLIENT_SECRET__", "redirect_uri"=>$url);		
            .....
        }
    
put your CLIENT_ID and CLIENT_SECRET
get [CLIENT_ID and CLIENT_SECRET](https://console.cloud.google.com/apis/credentials)

put your website domain and Authorized redirect URIs to client id of your project in google conslole.
