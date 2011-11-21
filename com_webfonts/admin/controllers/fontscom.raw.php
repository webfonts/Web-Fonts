<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.controller');

class WebfontsControllerFontscom extends JController {

  public function publish(){
    $model =& $this->getModel('fontscom');
    if($model->publish()) {
      echo 'Web Fonts: Published.';
    } else {
      echo 'Web Fonts: Failed to publish.';
    }
  }

}