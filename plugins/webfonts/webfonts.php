<?php

defined ('_JEXEC') or die();

jimport('joomla.event.plugin');

/* 
   This class essentially dispatches the same system events 
   to multiple Web Fonts vendor specific implementations in 
   the handlers folder 
*/

class plgSystemWebfonts extends JPlugin {

  protected $_onBeforeCompileHead = array('fontscom.php' => 'fontscom');
  protected $_onAfterRender = array('fontscom.php' => 'fontscom'); 
  protected $_active = array();

  public function onBeforeCompileHead(){
    $this->_initHandlers('_onBeforeCompileHead');
    $this->_fireActive('onBeforeCompileHead');
  }

  public function onAfterRender(){
    $this->_initHandlers('_onAfterRender');
    $this->_fireActive('onAfterRender');
  }

  protected function _fireActive($evt){
    foreach($this->_active as $plugin){
      $plugin->$evt();
    }
  }

  protected function _initHandlers($event){
    foreach($this->$event as $filename => $handler){
      if(file_exists(dirname(__FILE__) . "/handlers/$filename")){
	include_once(dirname(__FILE__) . "/handlers/$filename");
	$className = 'PluginWebfonts' . ucfirst($handler);
	if(class_exists($className)) $this->_active[] = new $className();
      }
    }
  }

}
