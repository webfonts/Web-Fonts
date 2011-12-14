<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.model');

class WebfontsModelStylesheet extends JModel {

  public function __construct($config = array()){
    parent::__construct($config);
  }

  public function getFonts(){
    $fonts = new StylesheetFontCoordinator;
    foreach($this->_vendors as $key => $vendor){
      $fonts->add($vendor->getSelectedFonts(), $key);
    }
    return $fonts;
  }

  public function getSelectors(){
    $query = $this->_db->getQuery(true);
    $query->select('*')->from('`#__webfonts`');
    $this->_db->setQuery($query);
    $records = $this->_db->loadObjectList();
    foreach($this->_vendors as $vendor){
      $vendor->preProcessAvailableSelectors($records);
    }
    return $records;
  }

  public function addSelector($selector, $fontId = false, $vendor = false){
    if(!$selector) return $this->_error(JText::_('FAIL_SELECTOR'));
    $stylesheet = JTable::getInstance('webfonts', 'JTable');
    $stylesheet->selector = $selector;
    if($fontId) $stylesheet->fontId = $fontId;
    if($vendor) $stylesheet->vendor = $vendor;
    $this->_removeSelectorIfExists($selector);
    if($vendor && $fontId) {
      $this->_vendors[$vendor]->addSelectorWithFont($selector, $fontId);
    } elseif($vendor) {
      $this->_vendors[$vendor]->addSelector($selector);
    }
    return $stylesheet->store();
  }

  protected function _removeSelectorIfExists($selector){
    $sid = $this->_getSelectorIfExists($selector);
    if($sid) return $this->removeSelector($sid);
  }

  protected function _getSelectorIfExists($selector){
    $query = $this->_db->getQuery(true);
    $query->select('`id`')->from('`#__webfonts`')->where('`selector` = ' . $this->_db->quote($selector));
    $this->_db->setQuery($query);
    return $this->_db->loadResult();
  }

  public function removeSelector($sid){
    $selector = $this->_getSelectorById($sid);
    if($selector->vendor == null) return $this->_justDeleteSelector($sid);
    if(array_key_exists($selector->vendor, $this->_vendors))
      return $this->_vendors[$selector->vendor]->removeSelector($selector);
    return false;
  }

  protected function _justDeleteSelector($sid){
    $this->_db->setQuery('DELETE FROM `#__webfonts` WHERE `id` = ' . $this->_db->quote($sid));
    return $this->_db->query();
  }

  public function updateSelectors($selectors, $fallBack = array()){
    $vendors = $this->_compileSelectors($selectors);
    $error = false;
    foreach($vendors as $vendor => $arr){
      $this->_vendors[$vendor]->updateSelectors($arr, $fallBack);
      if($this->_vendors[$vendor]->gotsErrors()) $error = array_pop($this->_vendors[$vendor]->getErrors());
    }
    if($error) return $this->_error($error);
    return true;
  }

  /* sort of ugly return format, might want to clean this up */
  protected function _compileSelectors($selectors){
    $new = array();
    foreach($selectors as $selector){
      if($selector === 'none') continue;
      $s = explode('::', $selector);
      $s['selectorId'] = $s[2];
      $s['selector'] = $this->_getSelectorById($s[2]);
      $s['vendor'] = $s[0];
      $s['FontID'] = $s[1];
      $new[$s['vendor']][] = $s;
    }
    return $new;
  }

  public function updateFallBack($vendor, $fontId, $fallBack){
    if(array_key_exists($vendor, $this->_vendors))
      $this->_vendors[$vendor]->updateFallBackForFont($fontId, $fallBack);
  }

  public function removeFont($fid, $vendor){
    return $this->_vendors[$vendor]->removeFontById($fid);
  }

  protected function _getSelectorById($id){
    $query = $this->_db->getQuery(true);
    $query->select('*')->from('`#__webfonts`')->where('`id` = ' . $this->_db->quote($id));
    $this->_db->setQuery($query);
    return $this->_db->loadObject();
  }

  protected function _error($msg){
    $this->setError($msg);
    return false;
  }

  public function __get($key){
    if($key === '_vendors'){
      $this->_vendors = array();
      $this->_vendors['fontscom'] = new WebfontsModelFontscom;
      $this->_vendors['google'] = new WebfontsModelGoogle;
      return $this->_vendors;
    }
  }

}