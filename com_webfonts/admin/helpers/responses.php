<?php

defined("_JEXEC") or die();

interface WebfontsResponse {

  public function wasSuccessful();

  public function getMessage();

}

class ResponseFontscom implements WebfontsResponse {

  protected $_response;
  protected $_context = array();
  protected $_base = null;

  public function __construct($response, $context = array(), $base = 'Accounts'){
    $this->_response = json_decode($response);
    $this->_context = $context;
    $this->_base = $base;
  }

  public function wasSuccessful(){
    $message = $this->_getBase();
    $key = $this->_getMessageKey($message);
    if($message->$key === "Success") return true; 
    return false;
  }

  public function getMessage(){
    $message = $this->_getBase();
    $key = $this->_getMessageKey($message);
    if(array_key_exists($message->$key, $this->_context)){
      return $this->_context[$message->$key];
    }
  }

  /* Inconsistent responses from API */
  protected function _getMessageKey(&$message){
    return (property_exists($message, 'Message')) ? 'Message' : 'message';
  }

  public function __get($property){
    $base = $this->_getBase();
    if(property_exists($base, $property))
      return $base->$property;
    return false;
  }

  protected function _getBase(){
    $b = $this->_base;
    if($b) return $this->_response->$b;
    return $this->_response;
  }

}

class WebfontsMockResponse {

  public $message = '';

  public function __construct($msg){
    $this->message = $msg;
  }

}
