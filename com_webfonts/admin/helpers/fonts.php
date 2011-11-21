<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

interface StylesheetFontWrapper {

  public function getName();
  
  public function getId();

  public function getDetails();

  public function getPreview();

  public function getHandler();

  public function getVendor();

  public function isEnabled();

  public function init();

}

class StylesheetFontCoordinator {

  protected $_fonts = array();

  public function add($fonts, $vendor){
    if(!is_array($fonts)) return false;
    $this->_fonts[$vendor] = $fonts;
  }

  public function getFonts(){
    $singlified = array();
    foreach ($this->_fonts as $vendor){
      $this->_initiateInit($vendor);
      $singlified = array_merge($singlified, $vendor);
    }
    return $singlified;
  }

  protected function _initiateInit($vendor){
    foreach($vendor as $font){
      $font->init();
    }
  }

  public function getPreview($fid, $vendor){
    if(!$fid || !$vendor) return;
    return $this->_fonts[$vendor][$fid]->getPreview();
  }

}

class StylesheetFontFontscom implements StylesheetFontWrapper {

  public $handler = 'fontscom';
  public $enabled = '1';
  protected $_font = null;
  
  public function __construct($font){
    $this->_font = $font;
  }

  public function getName(){
    return $this->_font->name;
  }
  
  public function getDetails(){
    return JText::_('WF_FONTDETAILS');
  }

  public function getId(){
    return $this->_font->FontID;
  }

  public function getPreview() {
    $text = "<span style=\"font-family: '" . $this->_font->family ."';\">";
    $text .= $this->_font->preview;
    return $text . '</span>' . PHP_EOL;
  }

  public function getHandler(){
    return 'fontscom';
  }

  public function getVendor(){
    return 'Fonts.com';
  }

  public function isEnabled(){
    return true;
  }

  public function init(){
    $doc = JFactory::getDocument();
    $doc->addScript('http://fast.fonts.com/jsapi/' . $this->_font->ProjectID .'.js');
  }

}

class StylesheetFontGoogle implements StylesheetFontWrapper {

  public $_family;
  public $_variant;
  protected $_id;
  protected $_subsets;
  protected $_styleSheetUri = null;

  public function __construct($args){
    extract($args);
    $this->_id = $id;
    $this->_family = $family;
    $this->_variant = $variant;
    $this->_subsets = $subsets;
    $this->_stylesheetUri = $stylesheetUri;
  }

  public function getName(){
    return $this->_family . ' ' .  ucfirst($this->_variant);
  }
  
  public function getDetails(){
    return JText::_('WF_GOOGLE_MULTIPLESAMPLES');
  }

  public function getId(){
    return $this->_id;
  }

  public function getPreview(){
    $span = '';
    if(empty($this->_subsets)) $this->_loadLatinSubset();
    foreach($this->_subsets as $subset){
      if(strpos($subset->subset, '-ext')) continue;
      $sample = new WebfontsLanguageSampleLong($subset->subset);
      $span .= '<span style="' . $this->_determineStyle() . '">' . $sample . '</span><br />';
    }
    return substr($span, 0, -6);
  }

  protected function _loadLatinSubset(){
   $default = new stdClass;
   $default->subset = 'latin';
   $this->_subsets = array($default);
  }

  /* Very similar method in google view, might extract to helper */
  protected function _determineStyle(){
    $base = "font-family: '" . $this->_family . "'; ";
    $style = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$2", $this->_variant);
    $weight = preg_replace('|([0-9]*)([A-Za-z]*)|is', "$1", $this->_variant);
    $base .= (is_numeric($weight)) ? 'font-weight: ' . $weight . ';' : '';
    $base .= (ctype_alpha($style)) ? 'font-style: ' . $style . ';' : '';
    return $base;
  }

  public function getHandler(){
    return 'google';
  }

  public function getVendor(){
    return 'Google Web Fonts';
  }

  public function isEnabled(){
    return true;
  }

  /* Might save some processor time here by alt approach */
  public function init(){
    $doc =& JFactory::getDocument();
    $headInfo = $doc->getHeadData();
    if(!in_array($this->_stylesheetUri, $headInfo['styleSheets'])) $doc->addStylesheet($this->_stylesheetUri);
  }

}