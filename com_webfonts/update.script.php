<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die;

class com_webfontsInstallerScript {
  
  protected $_release = '1.0.0';

  public function preflight($type, $parent){
    if($type === 'update' && !$this->_checkForSchemaEntry()) $this->_updateSchemas();
  }

  public function update($parent) {
    $this->_release = $parent->get("manifest")->version;
    echo '<p>Updated to ' . $this->_release .'</p>';
    return true;
  }

  protected function _checkForSchemaEntry(){
    $db =& JFactory::getDBO();
    $query = $db->getQuery(true);
    $query->select('s.extension_id')->from('`#__schemas` AS s')
      ->innerJoin('`#__extensions` AS x ON s.extension_id = x.extension_id')
      ->where("x.element = 'com_webfonts'");
    $db->setQuery($query);
    return $db->loadResult();
  }

  protected function _updateSchemas(){
    $db =& JFactory::getDBO();
    $query = $db->getQuery(true);
    $query->select('`extension_id`')->from('`#__extensions`')->where("`type` = 'component' AND `element` = 'com_webfonts'");
    $db->setQuery($query);
    $eid = $db->loadResult();
    if(!$eid) return;
    $db->setQuery('INSERT INTO `#__schemas` (`extension_id`,`version_id`) VALUES (' . $db->quote($eid) . ", '1.0.0-2011-11-01')");
    $db->query();
  }

}

/* 
   Need to do a bit more nuanced update
*/

class WFVersionOneToTwo {

  protected $_db = null;

  public function __construct(){
    $this->_db =& JFactory::getDBO();
  }

  public function versionOneToTwo(){
    $db =& $this->_db;
    $db->setQuery('SHOW COLUMNS FROM `#__webfonts_fontscom`');
    $result = $db->loadResultArray();
    if(in_array('font', $result)) $this->_changes();
  }

  protected function _changes(){
    $this->_structureChangesToFontscom();
    $this->_addPublishingFieldToFontscomVendor();
  }

  protected function _structureChangesToFontscom(){
    $db =& $this->_db;
    $query = $db->getQuery(true);
    $query->select('*')->from('`#__webfonts_fontscom`');
    $db->setQuery($query);
    $list = $db->loadObjectList();
    $this->_alterSchema(); // Order Important Here
    if(!$list || empty($list)) return;
    $this->_updateTable($list);
  }

  protected function _alterSchema(){
    $query = <<<QUERY
ALTER TABLE `#__webfonts_fontscom` ADD `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
ADD `family` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
ADD `preview` VARCHAR( 120 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
DROP `font`
QUERY;
    $this->_db->setQuery($query);
    if(!$this->_db->query()) throw new Exception('Failed to update Fonts.com table for version 2.0.  Please submit a bug with host information on GitHub https://github.com/webfonts/Web-Fonts/issues');
    return true;
  }

  protected function _updateTable($list){
    foreach($list as $row){
      $query = $this->_db->getQuery(true);
      $font = json_decode($row->font);
      $query->update('`#__webfonts_fontscom`')
	->set("`name` = '" . $font->FontName . "', `family` = '" . $font->FontCSSName . "', `preview` = '" . $font->FontPreviewTextLong . "'")
	->where("`id` = '" . $row->id . "'");
      $this->_db->setQuery($query);
      $this->_db->query();
    }
  }

  protected function _addPublishingFieldToFontscomVendor(){
    $properties =& $this->_getFontscomProperties();
    $properties->published = 0;
    $query = $this->_db->getQuery(true);
    $query->update('`#__webfonts_vendor`')->set('`properties` = ' . $this->_db->quote(json_encode($properties)))->where("`id` = '1'");
    $this->_db->setQuery($query);
    return $this->_db->query();
  }			    

  protected function _getFontscomProperties(){
    $db =& $this->_db;
    $query = $db->getQuery(true);
    $query->select('`properties`')->from('`#__webfonts_vendor`')->where("`id` = '1'");
    $db->setQuery($query);
    return json_decode($db->loadResult());
  }

}

$wfTwo = new WFVersionOneToTwo;

$wfTwo->versionOneToTwo();
