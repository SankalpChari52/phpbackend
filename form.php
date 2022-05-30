<?php 

class Form extends API{
    public function __construct(){
        parent::__construct();
        
    }

    public function create(){
        try{
            $errors = [];
            if(!isset($this->request_data["name"])) {
                
                $errors[]['name'] = ["message"=>"name field is required"];
    
            } else if(!preg_match("/^[A-Za-z\\- \']+$/", $this->request_data["name"])) {
    
                $errors[]['name'] = ["message"=>"name field should not contain any numbers or special charactor"];
            }
    
            if(!isset($this->request_data["email"])) {
                $errors[]["email"] = ["message"=>"Email field is required"];
    
            } else if(!filter_var($this->request_data["email"], FILTER_VALIDATE_EMAIL)) {
                $errors[]["email"] = ["message"=>"Email format is incorrect"];
            }
    
            if(!isset($this->request_data["dob"])) {
                $errors[]["dob"] = ["message"=>"DOB Field is required"];
            }
    
            if(!isset($this->request_data["city"])) {
                $errors[]["city"] = ["message"=>"City Field is required"];
            }
    
            if(!isset($this->request_data["pin"])) {
                $errors[]["pin"] = ["message"=>"Pincode Field is required"];
            }
    
            if(count($errors)>0){
                $this->returnResponse(BAD_REQUEST, $errors);  
            }
    
            $this->token = $this->generateToken();
            $jsonPostFields = "[{
              \"fields\":[
                {\"element_name\": \"name\",\"value\": \"".$this->request_data["name"]."\"},
                {\"element_name\": \"email\",\"value\": \"".$this->request_data["email"]."\"},
                {\"element_name\": \"dob\",\"value\": \"".$this->request_data["dob"]."\"},
                { \"element_name\": \"city\", \"value\": \"".$this->request_data["city"]."\" },
                { \"element_name\": \"pincode\", \"value\": \"".$this->request_data["pin"]."\"}
              ]
            }]";
            $RECORDURL = "https://loadapp.iformbuilder.com/exzact/api/v60/profiles/".$this->PROFILE_ID."/pages/".$this->PAGE_ID ."/records";
            $response = $this->sendRequest($RECORDURL, $jsonPostFields, $isPost = true);
            $this->returnResponse(SUCCESS_RESPONSE, $response);
        }catch(Exception $e){
            $this->returnResponse(INTERNAL_ERROR, ["message"=>$e->getMessage()]);
        }
       
    }

    public function get(){
        try{
            $this->token = $this->generateToken();
            $url = 'https://loadapp.iformbuilder.com/exzact/api/v60/profiles/' . $this->PROFILE_ID . '/pages/' . $this->PAGE_ID . '/feed?FORMAT=JSON';
            $response = $this->sendRequest($url, '', false);

            if(count($response)>0){
                $this->returnResponse(SUCCESS_RESPONSE, $response);
            }else{
                $this->returnResponse(EMPTY_RESPONSE, $response);
            }
        }catch(Exception $e){
            $this->returnResponse(INTERNAL_ERROR, ["message"=>$e->getMessage()]);
        }
      
    }

}