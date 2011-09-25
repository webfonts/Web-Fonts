<?php

defined('_JEXEC') or die('Access Restricted');

class JTableVendor extends JTable {

  public $id = null;
  public $name = null;
  public $properties = null;

  public function __construct(&$db){
    parent::__construct('#__webfonts_vendor', 'id', $db);
  }

  public function store($replaceNulls = false){
    $this->properties = json_encode($this->properties);
    $status = parent::store($replaceNulls);
    $this->properties = json_decode($this->properties);
    return $status;
  }

  public function load($id){
    $status = parent::load($id);
    $this->properties = json_decode($this->properties);
    return $status;
  }

}