<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

class JTableFontscom extends JTable {

  public $id = null;
  public $ProjectID = null;
  public $FontID = null;
  public $font = null;

  public function __construct(&$db){
    parent::__construct('#__webfonts_fontscom', 'id', $db);
  }

}