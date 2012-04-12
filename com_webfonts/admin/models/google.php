<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class WebfontsModelGoogle extends JModelList {

  const APIKEY = 'AIzaSyD2Mrb2LTTZ_SDv6ytfhsuM5unnCpIv7Qs';
  const UPDATENUMDAYS = '10';

  protected $_totalResults = 0;
  protected $_cachedFontInfo = array('subsets' => array(),
				     'variants' => array());

  public function getFonts(){
    $properties = $this->getState('properties');
    if($this->_isItTimeToUpdate($properties->updated)) $this->_syncFonts($properties->hash);
    return $this->getItems();
  }

  /* API Key allows for 10,000 hits a day.  So we're checking every 10 days to keep hits low */
  protected function _isItTimeToUpdate($lastUpdated){
    return (($lastUpdated + (self::UPDATENUMDAYS * 86400)) < time());
  }

  protected function _syncFonts($currentHash){
    JLoader::load('BBRemoteClient');
    $rc = new BBRemoteClient('https://www.googleapis.com/webfonts/v1/webfonts?key=' . self::APIKEY);
    $content = $rc->get();
    if(!$content) throw new Exception(JText::_('WF_GOOGLE_LOADFAIL'));
    $newHash = md5($content);
    if($this->_hashMatchesLastCheck($newHash, $currentHash)) return true;
    $this->_checkAndSyncIndividualFonts(json_decode($content));
    $this->_updateLastSynced($newHash);
  }

  protected function _hashMatchesLastCheck($newHash, $currentHash){
    return ($newHash === $currentHash);
  }

  /* Liberal use of term "sync"-- all its doing is adding what is missing */
  protected function _checkAndSyncIndividualFonts($fonts){
    foreach($fonts->items as $font){
      $stored = $this->_loadMatchingFontByFamily($font->family);
      if(!$stored) {
	$this->_insertFont($font);
      } else {
	$this->_checkForAdditionalXMen($font, $stored);
      }
    }
  }

  protected function _loadMatchingFontByFamily($family){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select('g.id, g.family, m.mutant, m.type')->from('`#__webfonts_google` AS g')
      ->leftJoin('`#__webfonts_google_mutant` AS m ON g.id = m.fk_fontId')
      ->where('`family` = ' . $db->quote($family));
    $db->setQuery($query);
    return $db->loadObjectList();
  }

  protected function _insertFont($font){
    $table = JTable::getInstance('Google', 'JTable');
    $table->save($font);
    $this->_loopAndInsertMutants($font, $table->id, 'variants');
    $this->_loopAndInsertMutants($font, $table->id, 'subsets');
  }

  protected function _loopAndInsertMutants($font, $id, $type){
    foreach($font->$type as $mutant){
      $this->_insertMutant($id, $mutant, $type);
    }
  }

  protected function _checkForAdditionalXMen($font, $stored){
    $this->_dualCheckAndInsert($font, $stored, 'variants');
    $this->_dualCheckAndInsert($font, $stored, 'subsets');
  }

  protected function _dualCheckAndInsert($font, $stored, $type){
    foreach($font->$type as $value){
      $flag = false;
      foreach($stored as $row){
	if($row->type === $type && $row->mutant == $value) $flag = true;
      }
      if($flag === false) $this->_insertMutant($stored[0]->id, $value, $type);
    }
  }

  protected function _insertMutant($fontId, $variant, $type){
    $table = JTable::getInstance('GoogleMutant', 'JTable');
    return $table->save(array('fk_fontId' => $fontId, 'mutant' => $variant, 'type' => $type));
  }

  protected function _updateLastSynced($hash){
    $db = $this->_db;
    $properties = new stdClass;
    $properties->updated = time();
    $properties->hash = $hash;
    $db->setQuery('UPDATE `#__webfonts_vendor` SET `properties` = ' . $db->quote(json_encode($properties)) .
		  'WHERE `id` = ' . $db->quote(2));
    if($db->query()) $this->setState('properties', $properties);
  }

  protected function getListQuery(){
    $search = $this->_db->quote('%' . $this->getState('keyword') . '%');
    $query = $this->_getQueryForBaseFontInformation();
    if($search === "'%%'") {
      $query->where("m.type != 'subsets' AND f.id IN(" . $this->_getCharsetSubQuery() .  ")");    
    } else {
      $query->where("m.type != 'subsets' AND f.family LIKE(" . $search . ') OR m.mutant LIKE(' . $search . ') AND '.
		  " f.id IN(" . $this->_getCharsetSubQuery() . ")");    
    }
    return $query;
  }

  protected function _getQueryForBaseFontInformation(){
    $query = $this->_db->getQuery(true);
    $query->select('f.id,f.family, m.id AS `mutantId`, m.mutant, m.type, m.inUse')->from('`#__webfonts_google` AS f')
      ->innerJoin('`#__webfonts_google_mutant` AS m ON(f.id = m.fk_fontId)');
    return $query;
  }

  /* Overwriting JModel cuz I want them keys! */
  protected function _getList($query, $limitstart=0, $limit=0) {
    $this->_db->setQuery($query, $limitstart, $limit);
    return $this->_db->loadObjectList('mutantId');
  }

  protected function _getCharsetSubQuery(){
    return "SELECT DISTINCT `fk_fontId` FROM `#__webfonts_google_mutant` WHERE `type` = 'subsets' AND " .
      "`mutant` = '" . $this->getState('subset') . "' OR `mutant` = '" . $this->getState('subset') . "-ext'";
  }

  public function getSubsets(){
    $fonts = $this->getItems();
    $mids = array_keys($fonts);
    $query = $this->_db->getQuery(true);
    $query->select('`fk_fontId` AS font, `mutant`')
      ->from('`#__webfonts_google_mutant`')
      ->where("`type` = 'subsets' AND `fk_fontId` IN(SELECT `fk_fontId` FROM `#__webfonts_google_mutant` " .
	      " WHERE `id` IN('". implode("','", $mids) ."'))");
    $this->_db->setQuery($query);
    return $this->_organizeSubsets($this->_db->loadObjectList());
  }

  protected function _getSubsetsByFontId($fid){
    return $this->_lookupFontInfo($fid, 'subset', 'subsets');

  }

  protected function _getVariantsByFontId($fid){
    return $this->_lookupFontInfo($fid, 'variant', 'variants');
  }

  protected function _lookupFontInfo($fid, $nameIt, $type){
    if(array_key_exists($fid, $this->_cachedFontInfo[$type])) return $this->_cachedFontInfo[$type][$fid];
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select("`mutant` AS {$nameIt}")->from('`#__webfonts_google_mutant`')
      ->where('`fk_fontId` = ' . $db->quote($fid) . " AND `type` = '{$type}'");
    $db->setQuery($query);
    $this->_cachedFontInfo[$type][$fid] = $db->loadObjectList();
    return $this->_cachedFontInfo[$type][$fid];
  }

  protected function _organizeSubsets($subsets){
    if(!$subsets) return array();
    $new = array();
    foreach($subsets as $result){
      if(!array_key_exists($result->font, $new)) {
	$new[$result->font] = ucfirst($result->mutant);
      } else {
	$new[$result->font] .= ', ' .ucfirst($result->mutant);
      }
    }
    unset($subsets);
    return $new;
  }

  public function addFont($fid){
    return $this->_updateFontStatus($fid, 1);
  }

  public function removeFont($fid){
    $this->_unsetSelectors($fid);
    return $this->_updateFontStatus($fid, 0);
  }

  public function removeFontById($fid){
    $this->removeFont($fid);
  }

  protected function _unsetSelectors($fid){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->update('`#__webfonts`')->set('`fontId` = NULL, `vendor` = NULL')->where('`fontId` = ' . $db->quote($fid) . " AND `vendor` = 'google'");
    $db->setQuery($query);
    return $db->query();
  }

  protected function _updateFontStatus($fid, $status){
    if($fid === 0) return false;
    $query = $this->_db->getQuery(true);
    $query->update('`#__webfonts_google_mutant`')->set("`inUse` = '{$status}'")
      ->where('`id` = ' . $this->_db->quote($fid));
    $this->_db->setQuery($query);
    return $this->_db->query();
  }

  public function getSelectedFonts(){
    $query = $this->_getQueryForBaseFontInformation();
    $query->where("`inUse` = '1'");
    $this->_db->setQuery($query);
    return $this->_processIntoStylesheetReadyFonts($this->_db->loadObjectList());
  }

  protected function _processIntoStylesheetReadyFonts($fonts){
    if(empty($fonts)) return false;
    $googleRequestUri = $this->_buildGoogleRequestUri($fonts);
    $processed = array();
    foreach($fonts as $font){
      $variant = ($font->type === 'variants') ? $font->mutant : null;
      $processed[$font->mutantId] = new StylesheetFontGoogle(array('id' => $font->mutantId,
								   'family' => $font->family,
								   'variant' => $variant,
								   'subsets' => $this->_getSubsetsByFontId($font->id),
								   'stylesheetUri' => $googleRequestUri));
    }
    return $processed;
  }

  protected function _buildGoogleRequestUri($fonts){
    $variants = array();
    $base = 'http://fonts.googleapis.com/css?family=';
    $lookedUp = array();
    foreach($fonts as $font){
      if(in_array($font->id, $lookedUp)) continue;
      $lookedUp[] = $font->id;
      $base .= $font->family;
      $variants = $this->_getVariantsByFontId($font->id);
      if(empty($variants)) {
	$base .= '|';
	continue;
      }
      $base .= ':';
      foreach($variants as $variant){
	$base .= $variant->variant . ',';
      }
      $base = substr($base, 0, -1);
      $base .= '|';
    }
    return substr($base, 0, -1);
  }

  public function updateFallBackForFont($fontId, $fallBack){
    $db = $this->_db;    
    $query = $db->getQuery(true);
    $updates = '`fallBack` = ' . $db->quote($fallBack);
    $query->update('`#__webfonts`')->set($updates)->where('`fontId` = ' . $db->quote($fontId) . " AND `vendor` = 'google'");
    $db->setQuery($query);
    return $db->query();
  }

  public function updateSelectors($selectors, $fallBack){
    foreach($selectors as $selector){
      $table = JTable::getInstance('Webfonts', 'JTable');
      $table->id = (int) $selector['selectorId'];
      $table->fontId = (int) $selector['FontID'];
      $table->fallBack = $fallBack[$selector['selectorId']];
      $table->selector = $selector['selector']->selector;
      $table->vendor = 'google';
      if(!$table->store()) {
	$this->_errors[] = JText::_('WF_SELECTORADD_FAIL');
	return false;
      }
    }
    return true;
  }

  public function removeSelector($selector){
    $db = $this->_db;    
    $query = $db->getQuery(true);
    $query->delete('`#__webfonts`')->where('`id` = ' . $db->quote($selector->id) . " AND `vendor` = 'google'");
    $db->setQuery($query);
    return $db->query();
  }

  public function gotsErrors(){
    return $this->_errors;
  }

  protected function populateState($ordering = null, $direction = null){
    parent::populateState();
    $this->setState('properties', $this->_getProperties());
    $this->setState('keyword', JRequest::getWord('keyword', false, 'get'));
    $this->setState('subset', JRequest::getVar('subset', 'latin', 'get'));
  }

  protected function _getProperties(){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select('properties')->from('#__webfonts_vendor')->where('`id` = ' . $db->quote('2'));
    $db->setQuery($query);
    return json_decode($db->loadResult());
  }

  public function preProcessAvailableSelectors($selectors){ }

  public function addSelector($selector){ }

  public function addSelectorWithFont($selector, $fontId){ }

}