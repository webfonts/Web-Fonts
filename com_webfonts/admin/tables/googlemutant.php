<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

class JTableGoogleMutant extends JTable {

  public $id = null;
  public $fk_fontId = null;
  public $mutant = null;
  public $type = null;
  public $inUse = 0;

  public function __construct(&$db){
    parent::__construct('#__webfonts_google_mutant', 'id', $db);
  }

}