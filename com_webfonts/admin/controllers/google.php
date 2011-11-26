<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.controller');

class WebfontsControllerGoogle extends JController {

  public function addFont(){
    $this->_changeFontStatus('addFont');
  }

  public function removeFont(){
    $this->_changeFontStatus('removeFont');
  }

  protected function _changeFontStatus($method){
    $fid = JRequest::getInt('fid', false, 'post');
    if(!$fid) $this->display();
    $google = $this->getModel('google');
    $base = 'index.php?option=com_webfonts&view=google&subset=' . 
      JRequest::getVar('subset', 'latin') . '&keyword=' . JRequest::getVar('keyword', '');
    if($google->$method($fid)){
      $this->setRedirect($base);
    } else {
      $this->setRedirect($base, JText::_('FAILED_ADD_FONT'), 'error');
    }
  }

}