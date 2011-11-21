<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

class JTableFontscom extends JTable {

  public $id = null;
  public $ProjectID = null;
  public $FontID = null;
  public $name = null;
  public $family = null;
  public $preview = null;

  public function __construct(&$db){
    parent::__construct('#__webfonts_fontscom', 'id', $db);
  }

}