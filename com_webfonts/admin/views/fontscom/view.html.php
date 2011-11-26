<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.view');

class WebfontsViewFontscom extends JView {

  public $properties = null;
  public $projects = null;
  public $domains = null;
  public $current = array('Project' => '', 'wfspid' => '');
  public $fonts = null;

  public function display($tpl = null){
    $this->_initAssets();
    $this->_initToolbar();
    $this->properties = $this->get('properties');
    $this->_loadProjectInfo();
    $this->domains = $this->get('domains');
    if($this->_amIEditingThisProject()) $this->_initFontStuff();
    parent::display();
  }

  protected function _loadProjectInfo(){
    $projects = $this->get('projects');
    if(!$projects) return;
    $this->userRole = $projects->UserRole;
    if(!property_exists($projects, 'Project')) return $this->projects = array();
    $this->projects = (is_array($projects->Project)) ? $projects->Project : array($projects->Project);
    unset($projects);
    $this->_setCurrent($this->projects);
  }

  protected function _initFontStuff(){
    $this->loadHelper('languages');
    $this->loadHelper('filters');
    $this->fonts = $this->get('fonts');
    $this->currentFonts = $this->get('projectfontids');
    $this->filters = new WebfontsFontscomFilters($this->get('filters'));
    $this->pagination = $this->get('pagination');
    $this->_addStyleDeclarationsToHeader();    
  }

  protected function _initAssets(){
    JHtml::_('behavior.mootools');
    JHtml::stylesheet('com_webfonts/webfonts.css', array(), true, false, false);
    JHtml::script('com_webfonts/fontscom.js', false, true, false, false);
    JHtml::_('behavior.formvalidation');
    JHtml::_('behavior.modal');
  }

  protected function _initToolbar(){
    JToolbarHelper::title('Web Fonts: Fonts.com');
    JToolbarHelper::custom('stylesheet.display', 'css', 'css', JText::_('EDIT_STYLESHEET'), false, false);
    JToolbarHelper::custom('vendors.display', 'upload', 'upload', JText::_('ADD_FONTS'), false, false);
  }

  protected function _createdNewAccount(){
    return (($this->properties->account->firstName && 
	     $this->properties->account->lastName && 
	     $this->properties->account->email)
	    && (!$this->properties->key));
  }

  protected function _setCurrent($projects){
    $wfspid = JRequest::getVar('wfspid', false);
    if(!$wfspid && isset($this->properties->wfspid)) $wfspid = $this->properties->wfspid;
    if(!$wfspid || !$projects) return;
    foreach($projects as $project){
      if($project->ProjectKey === $wfspid) {
	$this->current['Project'] = $project->ProjectName;
	$this->current['wfspid'] = $project->ProjectKey;
      }
    }
  }

  protected function _isCurrentProject($wfspid){
    if($wfspid === $this->current['wfspid']) return ' selected="selected"';
  }

  protected function _amIEditingThisProject(){
    return ($this->current['wfspid'] !== '') ? 1 : 0; 
  }

  protected function _addStyleDeclarationsToHeader(){
    $this->loadHelper('StyleDeclaration');
    $doc = JFactory::getDocument();
    $styles = "";
    if(!$this->fonts) return false;
    foreach($this->fonts as $font){
      if(is_object($font))
	$styles .= StyleDeclaration::font($font->FontCSSName, $font->EOTURL, $font->WOFFURL, $font->TTFURL, $font->SVGURL);
    }
    $doc->addStyleDeclaration($styles);
  }

  protected function _plugInRemainingEmptyCells($count){
    $tds = 5 - ($count % 5);
    for($i = 0; $i < $tds; ++$i){
      echo '<td>&nbsp;</td>';
    }
  }
                     
  protected function _imEditingFonts(){
    return JRequest::getInt('editingFonts', 0);
  }

  protected function _isFontAlreadyOnThisProject($FontID){
    return in_array($FontID, $this->currentFonts);
  }

  protected function _isACommercialProjectAccount(){
    return ($this->userRole === 'Free') ? false : true;
  }

  protected function _kiloBite($size){
    if(!is_numeric($size)) return;
    return number_format(($size / 1024), 2) . ' KB';
  }

}