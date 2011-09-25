<?php

defined ('_JEXEC') or die();

jimport('joomla.event.plugin');

class plgSystemWebfonts extends JPlugin {

  protected $_onBeforeCompileHead = array('fontscom.php' => 'fontscom'); 
  protected $_active = array();

  /* Othersystem events can be initiated by creating a method
     signature mapped to the desired files in the same manner as this
     one */

  public function onBeforeCompileHead(){
    $this->_initHandlers('_onBeforeCompileHead');
    $this->_fireActive();
  }

  protected function _fireActive(){
    foreach($this->_active as $plugin){
      $plugin->execute();
    }
  }

  protected function _initHandlers($event){
    foreach($this->$event as $filename => $handler){
      if(file_exists(dirname(__FILE__) . "/handlers/$filename")){
	include_once(dirname(__FILE__) . "/handlers/$filename");
	$className = 'PluginWebfonts' . ucfirst($handler);
	if(class_exists($className)) $this->_active[] = new $className;
      }
    }
  }

}


interface PluginWebfonts {

  public function execute();

}