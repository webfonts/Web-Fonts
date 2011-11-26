<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.view');

class WebfontsViewVendors extends JView {

  public $vendors = array();
  protected $_views = array('1' => 'fontscom',
			    '2' => 'google');

  public function display($tpl = null){
    $this->_initToolbar();
    $this->_initAssets();
    $this->vendors = $this->get('all');
    parent::display();
  }

  protected function _determineView($vendor){
    if(array_key_exists($vendor->id, $this->_views)){
      return $this->_views[$vendor->id];
    }
  }

  protected function _initAssets(){
    JHtml::stylesheet('com_webfonts/webfonts.css', array(), true, false, false);
  }

  protected function _initToolbar(){
    JToolbarHelper::title('Web Fonts: Vendors');
    JToolbarHelper::custom('stylesheet.display', 'css', 'css', JText::_('EDIT_STYLESHEET'), false, false);
    JToolbarHelper::custom('vendors.display', 'upload', 'upload', JText::_('ADD_FONTS'), false, false);
  }

}