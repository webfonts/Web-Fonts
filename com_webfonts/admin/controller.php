<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.controller');

class WebfontsController extends JController {

  public function display(){
    $view = JRequest::getCmd('view', false);
    if(!$view) JRequest::setVar('view', 'stylesheet'); 
    parent::display();
  }

}