<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class WebfontsControllerVendors extends JController {

  public function display($cachable = false, $urlparams = false){
    JRequest::setVar('view', 'vendors');
    parent::display($cachable, $urlparams);
  }


}