<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die();

class WebfontsFontscomFilters {

  protected $_buffer = '';
  protected $_response = array();
  protected $_options = array('freeorpaid' => array(),
			      'alpha' => array(),
			      'designer' => array(),
			      'foundry' => array(),
			      'classification' => array(),
			      'language' => array());
  protected $_titles = array('freeorpaid' => 'WF_FREEORCOMMERCIAL',
			     'alpha' => 'WF_FIRSTLETTER',
			     'designer' => 'WF_DESIGNER',
			     'foundry' => 'WF_FOUNDRY',
			     'classification' => 'WF_CLASSIFICATION',
			     'language' => 'WF_LANGUAGE');

  public function __construct($response){
    $this->_response =& $response;
  }

  protected function _buildFilters(){
    if(empty($this->_response)) return;
    foreach($this->_response->FilterValue as $filter){
      $type = strtolower($filter->FilterType);
      $this->_initOptions($type);
      $this->_options[$type][] = JHtml::_('select.option', $filter->ValueID, $filter->ValueName);
    }
    $this->_buildSelects();
  }

  protected function _initOptions($type){
    if(empty($this->_options[$type])) {
      $this->_options[$type][] = JHtml::_('select.option', '', JText::_($this->_titles[strtolower($type)]));
    }
  }

  protected function _buildSelects(){
    foreach($this->_options as $name => $optionList){
      if(empty($optionList)) continue;
      $default = JRequest::getVar($name, null);
      $this->_buffer .= JHtml::_('select.genericlist', 
				 $optionList, 
				 $name, 
				 'class="inputbox"', 
				 'value', 
				 'text', 
				 $default);
    }
  }

  public function __toString(){
    $this->_buildFilters();
    return $this->_buffer;
  }

}
