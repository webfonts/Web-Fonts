<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class WebfontsControllerStylesheet extends JController {

  public function display($cachable = false, $urlparams = false){
    JRequest::setVar('view', 'stylesheet');
    parent::display($cachable, $urlparams);
  }

  public function addSelector(){
    $selector = JRequest::getVar('selector', false, 'post');
    $fontId = JRequest::getVar('fontId', false, 'post');
    $vendor = JRequest::getVar('vendor', false, 'post');
    $this->_justGiveMeTheParams('addSelector', $selector, $fontId, $vendor);
  }

  public function removeSelector(){
    $this->_justGiveMeTheParams('removeSelector', JRequest::getVar('sid', false, 'post'));
  }

  public function updateSelectors(){
    $this->_justGiveMeTheParams('updateSelectors', JRequest::getVar('selectors', false, 'post'), 
			  JRequest::getVar('fallBack', false, 'post'));    
  }

  public function updateFallBack(){
    $vendor = JRequest::getVar('vendor', false, 'post');
    $fontId = JRequest::getVar('fontId', false, 'post');
    $fallBack = JRequest::getVar('fallBack', false, 'post');
    $this->_justGiveMeTheParams('updateFallBack', $vendor, $fontId, $fallBack);
  }

  public function removeFont(){
    $font = JRequest::getVar('font', false, 'get');
    $vendor = JRequest::getCmd('vendor', false, 'get');
    $this->_justGiveMeTheParams('removeFont', $font, $vendor);
  }
 
  protected function _justGiveMeTheParams(){
    $style = $this->getModel('stylesheet');
    $error = null;
    $args = func_get_args();
    $method = array_shift($args);
    $result = call_user_func_array(array($style, $method), $args);
    $msg = null;
    if(!$result) {
      $msg = array_pop($style->getErrors());
      $error = 'error';
    }
    $layout = ($layout = JRequest::getVar('layout', null)) ? '&layout=' . $layout : '';
    $this->setRedirect('index.php?option=com_webfonts' . $layout, $msg, $error);
  }

}