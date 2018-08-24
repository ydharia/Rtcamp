<?php
session_start();
$url_array = explode('?', 'https://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI']);
$url = $url_array[0];

require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';



$client = new Google_Client();

$client->setClientId('962527986048-fko0boarhk09sclfu0igldkgj1po0p8d.apps.googleusercontent.com');
$client->setClientSecret('By-esciDrlc7omI-_pVmHZag');
$client->setRedirectUri($url);
$client->setScopes(array('https://www.googleapis.com/auth/drive',
'https://www.googleapis.com/auth/drive.file',
'https://www.googleapis.com/auth/plus.login',
'https://www.googleapis.com/auth/userinfo.profile',
'https://www.googleapis.com/auth/userinfo.email',
'https://www.googleapis.com/auth/plus.me'
));
$google_oauthV2 = new Google_Oauth2Service($client);
$driveService = new Google_DriveService($client);

if (isset($_GET['code'])) {
    $_SESSION['accessToken'] = $client->authenticate($_GET['code']);
    $_SESSION['token'] = $client->getAccessToken();
    //$token = json_decode($_SESSION['accessToken'])->access_token;
    //$client->setAccessToken($token);
   
    header('location:'.$url);
exit;
} elseif (!isset($_SESSION['accessToken'])) {
	$client->authenticate();
}

if (isset($_SESSION['token'])) {
//echo $_SESSION['token'];
    $client->setAccessToken($_SESSION['token']);


$file = new Google_DriveFile();
$file->setTitle("core folder");
$file->setMimeType('application/vnd.google-apps.folder');



$createdFile = $driveService->files->insert($file, array(
    'mimeType'=>'application/vnd.google-apps.folder'
));

print_r($createdFile["id"]);

$permission = new Google_Permission();

$permission->setValue('');
$permission->setType('anyone');
$permission->setRole('reader');

$driveService->permissions->insert($createdFile["id"], $permission);

print_r($createdFile);

        
}




try
{
	if($client->getAccessToken())
	{
		$gpUserProfile = $google_oauthV2->userinfo->get();
		$gpUserData = array(
		        'oauth_provider'=> 'google',
		        'oauth_uid'     => $gpUserProfile['id'],
		        'first_name'    => $gpUserProfile['given_name'],
		        'last_name'     => $gpUserProfile['family_name'],
		        'email'         => $gpUserProfile['email'],
		        'gender'        => $gpUserProfile['gender'],
		        'locale'        => $gpUserProfile['locale'],
		        'picture'       => $gpUserProfile['picture'],
		        'link'          => $gpUserProfile['link']
		    );
		    print_r($gpUserData);
	}
	else
	{
	echo "else";
	}

}
catch(Exception $exx)
{
 print_r($exx);
}

$files= array();
$dir = dir('files');
while ($file = $dir->read()) {
    if ($file != '.' && $file != '..') {
        $files[] = $file;
    }
}


$dir->close();
if (!empty($_POST)) {
    $client->setAccessToken($_SESSION['accessToken']);
    $service = new Google_DriveService($client);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file = new Google_DriveFile();
    foreach ($files as $file_name) {
        $file_path = 'files/'.$file_name;
        $mime_type = finfo_file($finfo, $file_path);
        $file->setTitle($file_name);
        $file->setDescription('This is a '.$mime_type.' document');
        $file->setMimeType($mime_type);
        $service->files->insert(
            $file,
            array(
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type
            )
        );
    }
    finfo_close($finfo);
    //header('location:'.$url);exit;
}
include 'index.phtml';