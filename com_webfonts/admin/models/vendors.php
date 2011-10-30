<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.model');

class WebfontsModelVendors extends JModel {

  public function getAll(){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select($db->nameQuote('id') . ',' . $db->nameQuote('name'))->from($db->nameQuote('#__webfonts_vendor'));
    $db->setQuery($query);
    return $db->loadObjectList();
  }

}