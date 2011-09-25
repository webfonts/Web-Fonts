<?php

defined ('_JEXEC') or die();

class PluginWebfontsFontscom implements PluginWebfonts {

  public function execute(){
    if($this->_isAdmin()) return;
    $key = $this->_getActiveProject();
    if(!$key) return false;
    $doc = JFactory::getDocument();
    $doc->addScript("http://fast.fonts.com/jsapi/{$key}.js");
    $fallBack = $this->_buildFallbackDeclarations($key);
    if($fallBack) $doc->addStyledeclaration($fallBack);
  }

  protected function _isAdmin(){
    $user = JFactory::getUser();
    return in_array('8', $user->getAuthorisedGroups());
  }

  protected function _getActiveProject(){
    $db = JFactory::getDBO();
    $key = $this->_getProjectKey($db);
    if($this->_projectHasFonts($db, $key)) return $key;
    return false;
  }

  protected function _getProjectKey(&$db){
    $query = $db->getQuery(true);
    $query->select('properties')->from('#__webfonts_vendor')->where('`name` = ' . $db->quote('Fonts.com'));
    $db->setQuery($query);
    $result = $db->loadResult();
    if(!$result) return false;
    $properties = json_decode($result);
    if($properties->wfspid) return $properties->wfspid;
    return false;
  }

  protected function _projectHasFonts(&$db, $key){
    $query = $db->getQuery(true);
    $query->select('id')->from('#__webfonts_fontscom')->where('`ProjectID` = ' . $db->quote($key));
    $db->setQuery($query);
    $results = $db->loadResultArray();
    return (empty($results)) ? false : true;
  }

  protected function _buildFallbackDeclarations($key){
    $declarations = $this->_getDeclarationsFromDB($key);
    if(!$declarations || empty($declarations)) return false;
    $this->_encodeFontObjects($declarations);
    $output = $this->_buildOutStyles($declarations);
    return $output;
  }

  protected function _getDeclarationsFromDB($key){
    $db = JFactory::getDBO();
    $db->setQuery('SELECT `fallBack`, `selector`, `font` FROM #__webfonts AS f INNER JOIN #__webfonts_fontscom AS w ON (f.fontId = w.FontID) WHERE w.ProjectID = ' . $db->quote($key));
    return $db->loadObjectList();
  }

  protected function _encodeFontObjects(&$declarations){
    foreach($declarations as &$declaration){
      $declaration->font = json_decode($declaration->font);
    }
  }

  protected function _buildOutStyles($declarations){
    $buffer = "";
    $count = 0;
    foreach($declarations as $d){
      if($d->fallBack == '') continue;
      $count++;
      $buffer .= PHP_EOL . "body " . $d->selector . " {" . PHP_EOL;
      $buffer .= "font-family: '" . $d->font->FontCSSName . "', " . $d->fallBack . " !important;" . PHP_EOL;
      $buffer .= "}" . PHP_EOL;
    }
    return ($count === 0) ? false : $buffer;
  }

}
