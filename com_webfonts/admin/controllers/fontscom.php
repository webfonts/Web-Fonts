<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.controller');

class WebfontsControllerFontscom extends JController {

  public function setProject(){
    $wf = $this->getModel('fontscom');
    $wf->setProject(JRequest::getVar('wfspid', false));
    parent::display();
  }

  public function newAccount(){
    $this->_executeGenericAPICall('newAccount', array('email' => JRequest::getVar('createEmail', false, 'post'),
						      'firstName' => JRequest::getVar('createFirstName', false, 'post'),
						      'lastName' => JRequest::getVar('createLastName', false, 'post')));
  }

  public function getKey(){
    $this->_executeGenericAPICall('getKey', array('email' => JRequest::getVar('email', null, 'post'),
						  'password' => JRequest::getVar('password', null, 'post')));
  }

  public function saveKey(){
    $this->_executeGenericAPICall('saveKey', JRequest::getVar('authKey', false, 'post'));
  }

  public function saveProject(){
    $post = array('projectName' => JRequest::getVar('projectName', false, 'post'),
		  'wfspid' => JRequest::getVar('wfspid', false, 'post'),
		  'oldName' => JRequest::getVar('oldName', false, 'post'),
		  'savedName' => JRequest::getVar('savedName', false, 'post'),
		  'domains' => JRequest::getVar('domains', false, 'post'));
    $result = $this->_executeGenericAPICall('saveProject', $post);
    if(!$result) return;
    if($post['oldName'] === ''){
      $this->redirect .= ($result->Project[0]->ProjectKey) ? '&wfspid=' . $result->Project[0]->ProjectKey : null;
    } else {
      $this->redirect .= '&wfspid=' . $post['wfspid'];
    }
  }

  protected function _executeGenericAPICall($method, $args){
    if(!JRequest::checkToken()) return false;
    $result = false;
    try {
      $wf = $this->getModel('fontscom');
      $result = $wf->$method($args);
      if($wf->gotsErrors()) return $this->_errorRedirect(array_pop($wf->getErrors()));
      $this->setRedirect('index.php?option=com_webfonts&view=fontscom', $result->getMessage());
    } catch (Exception $e){
      $this->_errorRedirect($e->getMessage());
    }
    if($result) return $result;
  }

  protected function _errorRedirect($error, $additionalOptions = ''){
    $this->setRedirect('index.php?option=com_webfonts&view=fontscom' . $additionalOptions, $error, 'error');
  }

  public function addFont(){
    $this->_fontStatusChange('addFont');
  }

  public function removeFont(){
    $this->_fontStatusChange('removeFont');
  }

  protected function _fontStatusChange($method){
    if(!JRequest::checkToken()) return false;
    $wf = $this->getModel('fontscom');
    $post = JRequest::get('post');
    $wfspid = $post['wfspid'];
    $wfsfid = $post['wfsfid'];
    try {
      if(!$wf->$method($wfspid, $wfsfid)) {
	$app = JFactory::getApplication();
	$app->enqueueMessage(array_pop($wf->getErrors()), 'error');
      }
      $this->display();
    } catch (Exception $e){
      $this->_errorRedirect($e->getMessage());      
    }
  }

}