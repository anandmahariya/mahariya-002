<?php
ini_set('max_execution_time', 0);
ini_set("display_errors",1);
error_reporting(E_ALL);

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

session_start();

//Include config file
require_once 'config.php';
$app = new \Slim\Slim(array('debug'=>true,
							'log.enabled'=>true,
							'log.level'=> \Slim\Log::DEBUG,));

// Get request object
$app->get('/',function() use ($app) {
	$req = $app->request;
    $base_url = $req->getUrl()."".$req->getRootUri()."/";

    $api_url = $url = API_URL.'i';
    $data = _callUrl($api_url,'get');
    if($data){
    	$data = json_decode($data);
    	
    	//get domain and redirect options
    	$redirect = isset($data->redirect) ? $data->redirect : array();
	    $domains = isset($data->domain) ? $data->domain : array();
		$app->render('/admin/index.html',array('home'=>$base_url,'redirect'=>$redirect,'domains'=>$domains));
    }
});

//Create shorten 
$app->post('/',function() use ($app){
	$params = $app->request->params();
	if($params){
		$api_url = API_URL.'cs';
		$data = _callUrl($api_url,'post',$params);
		echo json_encode($data);
	}
	exit;
});

$app->get('/:query',function($key) use ($app) {
    if($key){
    	try{

    		$url = API_URL.'s/'.$key;
    		$data = _callUrl($url,'post',$_SERVER);	
				
			if($data){
				$json = json_decode($data);
				if($json->response->error != 0){
					$app->notFound();
				}else{

					$isXHR = $app->request->isAjax();
					$isXHR = $app->request->isXhr();

					//if the request in ajax then we assume please make this request direct
					$json->response->redirect = $isXHR ? 'direct' : $json->response->redirect; 

					switch($json->response->redirect){
						case 'direct' :
							header('Content-Type: text/html; charset=utf-8');
	                        header("HTTP/1.1 301 Moved Permanently"); 
	                        header("Connection: close", true);
	                        header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
  							header("Pragma: no-cache"); //HTTP 1.0
  							header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	                        header('location:'.$json->response->url);
                        	exit();
						break;
						case 'frame' :
							$variables = array('delay'=>5,
										   'url'=>$json->response->url,
										   'frame_url'=>'http://payal.coolpage.biz/fbcmp/index.php/admin/login');
							$app->render('/frame/frame.php',$variables);
						break;
						case 'splash' :
							$variables = array('delay'=>5,
											   'url'=>$json->response->url,
											   'frame_url'=>'http://payal.coolpage.biz/fbcmp/index.php/admin/login');
							$app->render('/frame/frame.php',$variables);
						break;
					}
				}
				exit;
			}
    	}catch(Exception $e){
            echo sprintf('%s',$e->getMessage());
        }
    }else{
    	$app->notFound();
    }
});

$app->notFound(function () use ($app) {
	$req = $app->request;
    $base_url = $req->getUrl()."".$req->getRootUri()."/";
    $app->render('/404/index.html',array('home'=>$base_url));
});

//helpers fucntions
function _callUrl($url,$type = 'GET',$data = null){
    $retVal = false;
    switch(strtolower($type)){
        case 'get' :
            $ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_URL,$url);
			$retVal = curl_exec ($ch);
			curl_close($ch);
            break;
        case 'post' :
			foreach ($data as $key => $value) {
				$post_items[] = $key.'='.$value; 
			}
			$formfields = implode ('&', $post_items);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_POST, count($data));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$formfields);
			curl_setopt($ch, CURLOPT_URL,$url);
			$retVal = curl_exec ($ch);
			curl_close($ch);
            break;
    }
    return $retVal;
}

function decrypt($encrypted_string){
	$encryption_key = md5(date('dmy'));
	$encrypted_string = base64_decode(str_replace(array("_P_", "_S_", "_E_"), array("+", "/", "="),$encrypted_string));
	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
	return $decrypted_string;
}

function encrypt($string){
	$encryption_key = md5(date('dmy'));
	$string = base64_decode(str_replace(array("_P_", "_S_", "_E_"), array("+", "/", "="),$string));
	$iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, $string, MCRYPT_MODE_ECB, $iv);
	return $encrypted_string;
}

//Rum slim server
$app->run();