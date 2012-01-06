<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.view');

class WebfontsViewGoogle extends JView {
  
  public $filters = null;
  public $fonts = array();
  public $subsets = array();
  protected $_marker = null;
  protected $_style = null;

  public function display($tpl = null){
    $this->loadHelper('languages');
    $this->_initToolbar();
    $this->_initAssets();
    $this->fonts = $this->get('fonts');
    $this->subsets = $this->get('subsets');
    $this->pagination = $this->get('pagination');
    $this->_addFontDeclarationsToHeader();
    parent::display();
  }

  protected function _initAssets(){
    JHtml::_('behavior.mootools');
    JHtml::_('behavior.modal');
    JHtml::script('com_webfonts/google.js', false, true, false, false);
    JHtml::stylesheet('com_webfonts/webfonts.css', array(), true, false, false);
  }

  protected function _initToolbar(){
    JToolbarHelper::title(JText::_('WF_GOOGLE'), 'webfonts');
    JToolbarHelper::custom('stylesheet.display', 'css', 'css', JText::_('EDIT_STYLESHEET'), false, false);
    JToolbarHelper::custom('vendors.display', 'upload', 'upload', JText::_('ADD_FONTS'), false, false);
  }

  protected function _addFontDeclarationsToHeader(){
    $doc = JFactory::getDocument();
    if(empty($this->fonts)) return;
    $families = array();
    foreach($this->fonts as $font){
      $families[$font->family][] = $font;
    }
    foreach($families as $group){
      $request = $this->_buildCSSRequestString($group);
      $doc->addStylesheet('http://fonts.googleapis.com/css?' . $request);
    }
  }

  protected function _buildCSSRequestString(&$group){
    $request = 'family=' . $group[0]->family . ':';
    $query = '';
    foreach($group as $font){
      $query .= ($font->type === 'variants' && $font->mutant != 'regular') ? $font->mutant . ',' : '';
    }
    $query = ($query !== '') ? substr($query, 0,-1) : '';
    if($query === '') return substr($request, 0, -1);
    return $request . $query;
  }

  protected function _getLanguage($font){
    
  }

  protected function _matchFontSubset($fid){
    return (array_key_exists($fid, $this->subsets)) ? $this->subsets[$fid] : JText::_('LATIN');
  }

  protected function _isFontAlreadyOnThisProject($font){
    return false;
  }

  protected function _plugInRemainingEmptyCells($count){
    $tds = 5 - ($count % 5);
    for($i = 0; $i < $tds; ++$i){
      echo '<td>&nbsp;</td>';
    }
  }

  protected function _determineLabel($font){
    $label = $font->family;
    $label .= ($font->type === 'variants') ? ' ' . ucfirst($font->mutant) : '';
    return $label;
  }

  protected function _determineStyle($font){
    if($font === $this->_marker) return $this->_style;
    $this->_style = "font-family: '" . $font->family . "'; ";
    if($font->type === 'variants') {
      $style = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$2", $font->mutant);
      $weight = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$1", $font->mutant);
      $this->_style .= (is_numeric($weight)) ? 'font-weight: ' . $weight . ';' : '';
      $this->_style .= (ctype_alpha($style)) ? 'font-style: ' . $style . ';' : '';
    }
    $this->_marker = $font;
    return $this->_style;
  }

  protected function _getFilters(){
    $subsets = array('latin','cyrillic', 'khmer', 'greek', 'vietnamese');
    $options = array();
    foreach($subsets as $subset){
      $options[] = JHtml::_('select.option', strtolower($subset), JText::_(strtoupper($subset)));
    }
    $filters = JHtml::_('select.genericlist', 
			 $options, 
			 'subset', 
			 'class="inputbox" onchange="this.form.submit()"', 
			 'value', 
			 'text', 
			JRequest::getVar('subset', 'latin', 'get'));
    return $filters;
  }

}