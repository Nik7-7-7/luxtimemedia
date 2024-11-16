<?php
 
 //check if wp_verify_nonce exists 
 if(!function_exists('get_option')){
 	die('Permission denied 1');
 }

 //get option wp_automatic_secret
 $wp_automatic_secret = get_option('wp_automatic_secret','');

 //if secret is empty die
 if($wp_automatic_secret == ''){
 	die('Permission denied 2');
 }
  
 //validate nonce
 if (!isset($_GET['_wpnonce']) ){ 
    die('Permission denied 3');
}

//compare the nonce with the secret and if not match die
if($wp_automatic_secret != $_GET['_wpnonce']){
	die('Permission denied 4');
}


function curl_exec_follow( &$ch){

	$max_redir = 3;

	for ($i=0;$i<$max_redir;$i++){

		$exec=curl_exec($ch);
		$info = curl_getinfo($ch);

		
		if($info['http_code'] == 301 ||  $info['http_code'] == 302  ||  $info['http_code'] == 307 ){
				
			curl_setopt($ch, CURLOPT_URL, wp_automatic_trim($info['redirect_url']));
			$exec=curl_exec($ch);
				
		}else{
				
			//no redirect just return
			break;
				
		}


	}

	return $exec;

}

$link=$_GET['link'];//urldecode();
 
//verify if link is on form httpz://sweetheatm.FXEXPERTS.hop.clickbank.net using REGEX
if(preg_match('/httpz:\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+\.hop\.clickbank\.net/', $link) != 1){
	echo json_encode(array('status'=>'error','message'=>'Invalid link'));

	exit;

}


    $link=wp_automatic_str_replace('httpz','http',$link);
    //$link='http://ointmentdirectory.info/%E0%B8%81%E0%B8%B2%E0%B8%A3%E0%B9%81%E0%B8%AA%E0%B8%94%E0%B8%87%E0%B8%A0%E0%B8%B2%E0%B8%9E%E0%B8%99%E0%B8%B4%E0%B9%88%E0%B8%87-%E0%B8%97%E0%B8%AD%E0%B8%94%E0%B8%9B%E0%B8%A5%E0%B8%B2%E0%B9%80%E0%B8%9E';
    //  echo $link ;
    //exit ;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, wp_automatic_trim($link));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch,CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_REFERER, 'http://bing.com');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8');
    curl_setopt($ch,CURLOPT_MAXREDIRS, 5); // Good leeway for redirections.
    curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 0); // Many login forms redirect at least once.
    
    $exec=curl_exec_follow($ch);

    
    
    $res=array();
    //get the link 
    $curlinfo=curl_getinfo($ch);
    
     
	$original_link=$curlinfo['url'];
	$original_link=wp_automatic_str_replace("?hop=zzzzz",'',$original_link);
	$res['link']=$original_link;
	
	//get the title
	preg_match("/<title>(.*?)<\/title>/i",$exec,$matches );
	
	if(isset($matches[1])){
		$ret=$matches[1];
	}else{
		$ret='';
	}

	$res['title']=$ret;
	$res['status']='success';

	$ret = array();
	
	/*** a new dom object ***/
	$dom = new domDocument;
	
	/*** get the HTML (suppress errors) ***/
	@$dom->loadHTML($exec);
	
	/*** remove silly white space ***/
	$dom->preserveWhiteSpace = false;
	
	/*** get the links from the HTML ***/
	$text = $dom->getElementsByTagName('p');
	
	/*** loop over the links ***/
	foreach ($text as $tag)
	{
		$textContent = $tag->textContent;
	
		if(wp_automatic_trim($textContent) == '' || strlen($textContent) < 25 || stristr($textContent, 'HTTP') || stristr($textContent, '$')) continue;
		$ret[] = $textContent;
		
	}
	
	$res['text']=$ret;
	
	  echo json_encode($res);

	exit;
     
?>