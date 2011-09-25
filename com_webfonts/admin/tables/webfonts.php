<?php

defined('_JEXEC') or die('Access Restricted');

class JTableWebfonts extends JTable {

  public $id = null;
  public $selector = null;
  public $fallBack = null;
  public $vendor = null;
  public $fontId = null;

  public function __construct(&$db){
    parent::__construct('#__webfonts', 'id', $db);
  }

}