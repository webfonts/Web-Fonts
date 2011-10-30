<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class WebfontsControllerVendors extends JController {

  public function display(){
    JRequest::setVar('view', 'vendors');
    parent::display();
  }


}