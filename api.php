<?php 
class API{
    // keys
    private $CLIENT_SECRET    = 'beff4a8e9ceaf82248f9d16fbb330d8aa583018b';
    private $CLIENT_KEY       = 'edb7301fe6a5afe67aec9931ed6cc59d6a349d14';

    protected $method;
    protected $path;
    protected $request_data;
    protected $AUD_VALUE        = 'https://loadapp.iformbuilder.com/exzact/api/oauth/token';
    protected $PROFILE_ID       = '372151';
    protected $PAGE_ID          = '290839219';
    protected $token;

    public function __construct() {
            $this->method = $_SERVER['REQUEST_METHOD'];
			$this->path = explode("/",str_replace("/api/","",$_SERVER['REQUEST_URI']));
			$handler = fopen('php://input', 'r');
			$this->request_data = stream_get_contents($handler);
            $this->request_data = json_decode($this->request_data, true);
    }

    // return function 
    public function returnResponse($code, $data) {
        header("content-type: application/json");
        if(in_array($code, [SUCCESS_RESPONSE,EMPTY_RESPONSE])){
            $response['has_error'] = 1;
            $response['data'] = $data; 
        }else{
            $response['has_error'] = 0;
            $response['errors'] = $data; 
        }
        
        $response['status'] = $code;
        $response = json_encode($response);
        
        echo $response; 
        exit;
    }

    // check the method and call the api
    public function processApi(){
            $form = new Form();
            switch($this->method){
                case "GET":
                        $this->invokeMethod('Form',$form);
                    break;
                case "POST":
                        $this->invokeMethod('Form',$form);
                    break;
                default :
                    $this->returnResponse(METHOD_NOT_ALLOWED,["message"=>"Method not allowed"]);
            }
    }

    // invoke the method from the class to process request
    public function invokeMethod($class_name,$class_object){
        if(count($this->path)>1){
            $method_name = str_replace("-","_",$this->path[1]);
            $rMethod = new reflectionMethod($class_name, $method_name);
            if(!method_exists($class_object, $method_name)) {
                $this->returnResponse(NOT_FOUND, ["message"=>"API does not exist."]);
            }
            $rMethod->invoke($class_object);
        }else{
            $this->returnResponse(NOT_FOUND, ["message"=>"API does not exist."]);
        }
    } 

    // function to generate the token for iformbuilder 
    protected function generateToken(){
		$header = ['typ' => 'JWT','alg' => 'HS256'];
		$header = json_encode($header);	
		$header = base64_encode($header);
		$CLIENT_KEY = $this->CLIENT_KEY;
		$AUD_VALUE = $this->AUD_VALUE;
		$CLIENT_SECRET = $this->CLIENT_SECRET;
		$nowtime = time();
		$exptime = $nowtime + 599;
		
		$payload = "{
			\"iss\": \"$CLIENT_KEY\",
		   \"aud\": \"$AUD_VALUE\",
		  \"exp\": $exptime,
		  \"iat\": $nowtime}";	

		$payload = $this->base64urlEncode($payload);
		$signature = $this->base64urlEncode(hash_hmac('sha256',"$header.$payload",$CLIENT_SECRET, true));
		$assertionValue = "$header.$payload.$signature";
		$grant_type = "urn:ietf:params:oauth:grant-type:jwt-bearer";
		$grant_type = urlencode($grant_type);
		$postField= "grant_type=".$grant_type."&assertion=".$assertionValue;	
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_URL, $AUD_VALUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS,"$postField");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  "Content-Type: application/x-www-form-urlencoded",
		  "cache-control: no-cache"
		));
		$response = curl_exec($ch);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		curl_close($ch);
		$tokenArray = json_decode($response,true);
        return $token = $tokenArray['access_token'];

	}

    function base64urlEncode($data) { 
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 

     // general purpose function to request 3rd party data  
    function sendRequest($url, $fields, $isPost = true) {
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $this->token"
        ));

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }

}