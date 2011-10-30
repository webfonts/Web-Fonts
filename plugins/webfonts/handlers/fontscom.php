<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class PluginWebfontsFontscom {

  protected $_key = false;
  protected $_isAdmin = null;

  public function onBeforeCompileHead(){
    if($this->_isAdmin()) return;
    $key = $this->_getActiveProject();
    if(!$key) return false;
    $doc = JFactory::getDocument();
    $fallBack = $this->_buildFallbackDeclarations($key);
    if($fallBack) $doc->addStyledeclaration($fallBack);
  }

  /* Has to be last line in Head element to override equivalent styles */
  public function onAfterRender(){
    if($this->_isAdmin()) return;
    $response = JResponse::getBody();
    $key = $this->_getActiveProject();
    if(!$key) return false;
    $link = "<script type=\"text/javascript\" src=\"http://fast.fonts.com/jsapi/{$key}.js\"></script>";
    $link .= "<!-- Fonts.com CDN call -->" . PHP_EOL . "</head>";
    // Manipulating this causes the event to be called again, so we do a check here
    if(strpos($response, '<!-- Fonts.com CDN call -->') > 0) return;
    $response = str_ireplace('</head>', $link, $response);
    JResponse::setBody($response);
  }

  protected function _isAdmin(){
    if($this->_isAdmin !== null) return $this->_isAdmin;
    $app =& JFactory::getApplication();
    $this->_isAdmin = $app->isAdmin();
    return $this->_isAdmin;
  }

  protected function _getActiveProject(){
    $db = JFactory::getDBO();
    $key = $this->_getProjectKey($db);
    if($this->_projectHasFonts($db, $key)) {
      $this->_key = $key;
    }
    return $this->_key;
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
