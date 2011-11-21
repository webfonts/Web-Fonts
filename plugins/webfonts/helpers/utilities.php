<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class BBJoomlaEnviroUtilities {

  static public function areWeOnAdminSide(){
    $app =& JFactory::getApplication();
    return $app->isAdmin();
  }
  

}