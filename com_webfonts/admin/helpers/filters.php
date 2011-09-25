<?php

defined('_JEXEC') or die();

class WebfontsFontscomFilters {

  protected $_buffer = '';
  protected $_response = array();

  public function __construct($responses){
    $this->_responses = $responses;
  }

  protected function _buildFilters(){
    foreach($this->_responses as $name => $response){
      if(!is_array($response->FilterValue)) $response->FilterValue = array($response->FilterValue);
      $filters = $response->FilterValue;
      if(empty($filters)) continue;
      $options = array();
      $options[] = JHtml::_('select.option', '', JText::_($name));
      foreach($filters as $filter){
	$options[] = JHtml::_('select.option', $filter->ValueID, $filter->ValueName);
      }
      $default = JRequest::getVar($response->FilterName, null);
      $this->_buffer .= JHtml::_('select.genericlist', 
				 $options, 
				 $response->FilterName, 
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