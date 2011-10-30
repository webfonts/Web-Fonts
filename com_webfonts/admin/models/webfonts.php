<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_COMPONENT . DS . 'tables');

class WebfontsModelWebfonts extends JModel {

  private $_table = null;

  public function __construct(){
    parent::__construct();
    $this->_table =& JTable::getInstance('Webfonts', 'Table');
  }

}