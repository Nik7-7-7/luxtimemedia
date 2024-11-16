<?php

class pixabay {
	
	public $ch;
	public $api_key;
	
	function __construct($ch, $api_key){
		$this->ch = $ch;
		$this->api_key = $api_key;
	}
	
	function get_images($keyword , $page = 0) {
		 echo ' Finiding for: '. $keyword;
		 
		 $url = 'https://pixabay.com/api/?key='.$this->api_key.'&q=' . urlencode( wp_automatic_trim( $keyword ) ) . '&image_type=photo&per_page=20' ; 
		 		
		 
		 		//pagination ?pagi=2
		 		if($page !== 0 ) $url.= '&page=' . wp_automatic_trim($page);
		 
		 		echo '<br>Loading ' . $url ;
		 		
		 //curl get
		 $x='error';
		 curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		 curl_setopt($this->ch, CURLOPT_URL, wp_automatic_trim($url));
		 $exec=curl_exec($this->ch);
		 $x=curl_error($this->ch);

		 if(wp_automatic_trim($exec) == ''){
		 	echo '<-- Error: empty reply from PixaBay side ' . $x;
		 	return false;
		 }
		 
		 $json = json_decode($exec);
		
		 if(! isset($json->total)){
		 	echo '<-- Not expected output, JSON does not contain total number...' . $exec ;
		 	return false;
		 }else{
		 	echo '<br>PixaBay has '. $json->total. ' images for the keyword:'. $keyword;
		 }
		 
		   
		 return $json->hits;
		 
	}
}