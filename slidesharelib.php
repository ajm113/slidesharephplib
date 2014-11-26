<?php

/*
/*
 *          DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
                    Version 2, December 2004 

 Copyright (C) 2004 Sam Hocevar <sam@hocevar.net> 

 Everyone is permitted to copy and distribute verbatim or modified 
 copies of this license document, and changing it is allowed as long 
 as the name is changed. 

            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 

  0. You just DO WHAT THE FUCK YOU WANT TO.
 */

/**
 * Description of slidesharelib
 *
 * @author Andrew McRobb
 * @description Communicates to SlideShare's API Service
 * @documentation http://www.slideshare.net/developers/documentation
 */
class slidesharelib {
    private $m_key = "";    /* Your API KEY  */
    private $m_secret = ""; /* Your API SECRET  */
    
    /* EVERYTHING ELSE BELOW DOES NOT NEED TO BE CHANGED  */
    
    private $m_base_url = "https://www.slideshare.net/api/";
    private $m_api_ver = "2";
    private $m_curl_handler = NULL;
    private $m_agent_name = "Andrew's SlideShare PHP Library";
    private $m_use_array = TRUE;
    
    
    public $last_http_response = 0;
    
    function __construct($key = "", $secret = "") {
        $this->init($key, $secret);
    }
    
    function __destruct() {
        $this->clearAll();
    }
    
    protected function init($key = "", $secret = "")
    {
        if(!$this->_is_curl_installed())
        {
            throw new Exception('Curl is not installed! Please read the url: <a href="http://curl.haxx.se/libcurl/php/">http://curl.haxx.se/libcurl/php/</a>');
        }
        
        if(!empty($key))
        {
            $this->m_key = $key;
        }
        
        if(!empty($secret))
        {
            $this->m_secret = $secret;
        }
        
        //Ensure they key and secret isn't empty...
         if(empty($this->m_key) || empty($this->m_secret))
        {
            throw new Exception('Please enter in your key and secret into init or class. <a href="http://www.slideshare.net/developers/applyforapi">Dont have a key or secret?</a>');
        }       
        
        
        //Initalize curl...
        if(!$this->m_curl_handler)
        {
            $this->m_curl_handler = curl_init();
        }
        
        curl_setopt($this->m_curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->m_curl_handler, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($this->m_curl_handler, CURLOPT_USERAGENT, $this->m_agent_name);
        curl_setopt($this->m_curl_handler, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->m_curl_handler, CURLOPT_HEADER, 0);
    }
    
    //Ensure we have curl enabled on the server.
    private function _is_curl_installed() 
    {
        if  (in_array  ('curl', get_loaded_extensions())) 
        {
            return TRUE;
        }
        
        return FALSE;
    }
    
    //Dont need to call this since this auto destructs...
    public function clearAll()
    {        
        //Can we delete?
        if($this->m_curl_handler)
        {
            curl_close($this->m_curl_handler);
            $this->m_curl_handler = NULL;
        }
    }
    
    //Adds auth headers to data so user doesn't have to.
    protected function generate_auth()
    {
        $data['api_key'] = $this->m_key;
        $data['ts'] = time();
        $data['hash'] = sha1($this->m_secret.$data['ts']);
        
        return $data;
    }
    
    public function use_stdobject($enabled = FALSE)
    {
        $this->m_use_array = $enabled;
    }
    
    
    private function xml_to_object($xml)
    {
    
        $deXml = simplexml_load_string($xml);
        $deJson = json_encode($deXml);
        $xml_array = json_decode($deJson, $this->m_use_array);
        return $xml_array;
    }

    //Calls the requesting function of the webserver. I.E $function = "get_slideshow" returns slideshow 
    // Enter array in data of parameters. I.E ["username" => "myName", "password" => "password" => "myPassword"]
    public function call($function, $data = NULL)
    {
        $direction_url = $this->m_base_url.$this->m_api_ver.'/'.$function;
        curl_setopt($this->m_curl_handler, CURLOPT_URL, $direction_url);
        
        $idata = $this->generate_auth();
        
        if(is_array($data))
        {   
           $idata = array_merge($idata, $data);
        }
        
        $fields_string = "";
        foreach($idata as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        
        if(count($idata) > 0)
        {
            curl_setopt($this->m_curl_handler, CURLOPT_POST, count($idata));
            curl_setopt($this->m_curl_handler, CURLOPT_POSTFIELDS, $fields_string);
        }
        
        $result = curl_exec($this->m_curl_handler);
        $this->last_http_response = curl_getinfo($this->m_curl_handler, CURLINFO_HTTP_CODE);
        
        return $this->xml_to_object($result);
    }
}

?>
