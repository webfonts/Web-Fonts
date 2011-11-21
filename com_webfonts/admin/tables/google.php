<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

class JTableGoogle extends JTable {

  public $id = null;
  public $kind = null;
  public $family = null;

  public function __construct(&$db){
    parent::__construct('#__webfonts_google', 'id', $db);
  }

}