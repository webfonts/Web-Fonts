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
    $this->_loadActiveProjectKey();
    $this->_insertFallbackDeclarations();
  }

  public function onAfterRender(){
    if($this->_isAdmin) return;
    $this->_loadActiveProjectKey();
    if(!$this->_key) return;
    $link = "<script type=\"text/javascript\" src=\"http://fast.fonts.com/jsapi/{$this->_key}.js\"></script>";
    $commentMarker = "<!-- Web Fonts: Fonts.com -->";
    $this->_head->insertBeforeClosingTag($link, $commentMarker);
  }

  protected function _loadActiveProjectKey(){
    $db = JFactory::getDBO();
    $key = $this->_getProjectKey($db);
    if($this->_projectHasFonts($db, $key)) {
      $this->_key = $key;
    }
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

  protected function _insertFallbackDeclarations(){
    $declarations = $this->_getProjectFallbacks();
    if(!$declarations || empty($declarations)) return false;
    $this->_addFallBacks($declarations);
  }

  protected function _getProjectFallbacks(){
    $db = JFactory::getDBO();
    $db->setQuery('SELECT `fallBack`, `selector`, `family` FROM #__webfonts AS f INNER JOIN #__webfonts_fontscom AS w ON (f.fontId = w.FontID) WHERE w.ProjectID = ' . $db->quote($this->_key));
    return $db->loadObjectList();
  }

  protected function _addFallBacks($declarations){
    foreach($declarations as $d){
      if($d->fallBack == '') continue;
      $this->_head->addStyleDeclaration($d->selector, $d->family, $d->fallBack);
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
