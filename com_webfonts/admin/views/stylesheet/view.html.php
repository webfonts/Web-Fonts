<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.view');

class WebfontsViewStylesheet extends JView {

  public $fonts = null;
  protected $_coord = null;

  public function display($tpl = null){
   $this->loadHelper('options');
   $this->loadHelper('styledeclaration'); 
   try {
      $this->_coord =& $this->get('fonts');
      $this->fonts = $this->_coord->getFonts();
      $this->selectors =& $this->get('selectors');
    } catch(Exception $e){
      $app = JFactory::getApplication();
      $app->enqueueMessage($e->getMessage(), 'error');
    }
   $this->_initAssets();
   $this->_initToolbar();
   parent::display($tpl);
  }

  protected function _initAssets($tpl = null){
    JHtml::stylesheet('com_webfonts/webfonts.css', array(), true, false, false);
    JHtml::_('behavior.mootools');
    JHtml::script('com_webfonts/stylesheet.js', false, true, false, false);
  }

  protected function _initToolbar(){
    JToolbarHelper::title('Web Fonts: Stylesheet');
    JToolbarHelper::custom('stylesheet.display', 'css', 'css', JText::_('EDIT_STYLESHEET'), false, false);
    JToolbarHelper::custom('vendors.display', 'upload', 'upload', JText::_('ADD_FONTS'), false, false);
    if(!$this->_doWeHaveFonts()) return;
    JSubMenuHelper::addEntry(JText::_('ASSIGN_FONTS'), 'index.php?option=com_webfonts&view=stylesheet');
    JSubMenuHelper::addEntry(JText::_('ASSIGN_SELECTORS'), 'index.php?option=com_webfonts&view=stylesheet&layout=selectors');
  }
  
  protected function _doWeHaveFonts(){
    return ($this->fonts && (!empty($this->fonts)));
  }

  protected function _listOptions($selected, $sid){
    foreach($this->fonts AS $font){
      $option = '<option value="' . $font->getHandler() . '::' . $font->getId() . '::' . $sid . '"';
      if($selected === $font->getId()) $option .= ' selected="selected"';
      echo $option . '>' . $font->getName() . '</option>' . PHP_EOL;
    }
  }

  protected function _getFontPreview($id, $vendor){
    return $this->_coord->getPreview($id, $vendor);
  }

  protected function _organizeMySelectors($myId){
    $mine = array();
    if(count($this->selectors) === 0) return $this->_getNullSelectors();
    foreach($this->selectors as $selector){
      if($selector->fontId === $myId) $mine[] = $selector;
    }
    if(empty($mine)) return $this->_getNullSelectors();
    return $mine;
  }

  protected function _getNullSelectors(){
    $std = new stdClass;
    $std->selector = JText::_('NO_SELECTORS');
    $std->fallBack = '';
    $std->id = false;
    return array($std);
  }

}