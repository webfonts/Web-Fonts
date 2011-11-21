<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class PluginWebfontsFontscom {

  protected $_key = false;
  protected $_isAdmin = null;
  protected $_head = null;

  public function __construct(){
    $this->_head = new WFHeadHelper();
    $this->_isAdmin = BBJoomlaEnviroUtilities::areWeOnAdminSide();
  }

  public function onBeforeCompileHead(){
    if($this->_isAdmin) return;
    $key = $this->_getActiveProject();
    if(!$key) return false;
    $this->_insertFallbackDeclarations($key);
  }

  public function onAfterRender(){
    if($this->_isAdmin) return;
    $key = $this->_getActiveProject();
    if(!$key) return false;
    $link = "<script type=\"text/javascript\" src=\"http://fast.fonts.com/jsapi/{$key}.js\"></script>";
    $commentMarker = "<!-- Web Fonts: Fonts.com -->";
    $this->_head->insertBeforeClosingTag($link, $commentMarker);
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

  protected function _insertFallbackDeclarations($key){
    $declarations = $this->_getDeclarationsFromDB($key);
    if(!$declarations || empty($declarations)) return false;
    $this->_encodeFontObjects($declarations);
    $output = $this->_addFallBacks($declarations);
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

  protected function _addFallBacks($declarations){
    foreach($declarations as $d){
      if($d->fallBack == '') continue;
      $this->_head->addStyleDeclaration($d->selector, $d->font->FontCSSName, $d->fallBack);
    }
    $this->_head->loadAllStyleDeclarations();
  }

  public function webfontsStylesheetLoading(){
    if(!$this->_projectNeedsPublishing()) return;
    JHtml::_('behavior.mootools');
    JHtml::script('com_webfonts/fontscom.publish.js', false, true, false, false);
  }

  protected function _projectNeedsPublishing(){
    return true;
  }

}
