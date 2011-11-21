<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

JLoader::register('WebfontsModelGoogle', JPATH_ADMINISTRATOR . '/components/com_webfonts/models/google.php');
JLoader::register('StylesheetFontGoogle', JPATH_ADMINISTRATOR . '/components/com_webfonts/helpers/fonts.php');

class PluginWebfontsGoogle {

  protected $_isAdmin = null;
  protected $_head = null;

  public function __construct(){
    $this->_head = new WFHeadHelper();
    $this->_isAdmin = BBJoomlaEnviroUtilities::areWeOnAdminSide();
  }

  public function onBeforeCompileHead(){
    if($this->_isAdmin) return;
    $google = new WebfontsModelGoogle;
    $fonts = $google->getSelectedFonts();
    if(empty($fonts)) return;
    foreach($fonts as $font){
      $font->init();
    }
  }

  public function onAfterRender(){
    if($this->_isAdmin) return;
    $selectors =& $this->_getGoogleSelectorsWithFontInfo();
    if(empty($selectors)) return;
    foreach($selectors as $selector){
      $style = $this->_fontStyle($selector->mutant);
      $weight = $this->_fontWeight($selector->mutant);
      $this->_head->addStyleDeclaration($selector->selector, $selector->family, $selector->fallBack, $style, $weight);
    }
    $declarations = $this->_head->getStyleDeclarations();
    $this->_head->resetStyles();
    $buffer = '<style type="text/css">' . PHP_EOL . implode($declarations) . PHP_EOL . '</style>';
    $this->_head->insertBeforeClosingTag($buffer, '<!-- Web Fonts: Google -->');
  }


  protected function _getGoogleSelectorsWithFontInfo(){
    $db =& JFactory::getDBO();
    $query = $db->getQuery(true);
    $query->select('`selector`, `fallBack`,`family`,`mutant`')
      ->from('`#__webfonts` AS wf')
      ->innerJoin('`#__webfonts_google_mutant` AS m ON(wf.fontId = m.id)')
      ->innerJoin('`#__webfonts_google` AS g ON(m.fk_fontId = g.id)')
      ->where("`vendor` = 'google' AND `inUse` = '1'");
    $db->setQuery($query);
    return $db->loadObjectList();
  }

  protected function _fontWeight($str){
    $weight = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$1", $str);
    return $weight;
  }

  protected function _fontStyle($str){
    $style = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$2", $str);
    return $style;
  }

}