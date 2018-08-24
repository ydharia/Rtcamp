<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Facebook App details
| -------------------------------------------------------------------
|
| To get an facebook app details you have to be a registered developer
| at http://developer.facebook.com and create an app for your project.
|
|  facebook_app_id               string   Your facebook app ID.
|  facebook_app_secret           string   Your facebook app secret.
|  facebook_login_type           string   Set login type. (web, js, canvas)
|  facebook_login_redirect_url   string   URL tor redirect back to after login. Do not include domain.
|  facebook_logout_redirect_url  string   URL tor redirect back to after login. Do not include domain.
|  facebook_permissions          array    The permissions you need.
|  facebook_graph_version        string   Set Facebook Graph version to be used. Eg v2.6
|  facebook_auth_on_load         boolean  Set to TRUE to have the library to check for valid access token on every page load.
*/

//$config['facebook_app_id']              = '232436484074589';
$config['facebook_app_id']              = '224429468110401';
//$config['facebook_app_secret']          = '48841e9af1983444b5af1df39f402cc1';
$config['facebook_app_secret']          = '6ec7b4790db1e26dc96337ae8ea2161f';
$config['facebook_login_type']          = 'web';
$config['facebook_login_redirect_url']  = 'myfacebook/albums';
$config['facebook_logout_redirect_url'] = 'myfacebook/';
$config['facebook_permissions']         = array('public_profile', 'email', 'user_birthday', 'user_age_range', 'user_gender', 'user_friends', 'user_location', 'user_photos');
$config['facebook_graph_version']       = 'v3.1';
$config['facebook_auth_on_load']        = TRUE;
