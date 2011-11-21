<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die();

class BBRemoteClient {

  protected $_location = null;
  protected $_buffer = '';

  public function __construct($location){
    $this->_location = $location;
  }

  public function get(){
    $method = $this->_determineMethod();
    if(method_exists($this, $method)) $this->_buffer = $this->$method();
    return $this->_buffer;
  }

  protected function _Fopen(){
    return file_get_contents($this->_location);
  }

  protected function _Curl(){
    $c = curl_init($this->_location);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $content = curl_exec($c);
    curl_close($c);
    return $content;
  }

  protected function _determineMethod(){
    if(ini_get('allow_url_fopen')) return '_Fopen';
    if(function_exists('curl_init')) return '_Curl';
    return false;
  }

  public function __toString(){
    return $this->get();
  }

}