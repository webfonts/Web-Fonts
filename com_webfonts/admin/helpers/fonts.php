<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

interface StylesheetFontWrapper {

  public function getName();
  
  public function getId();

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
    return $this->_font->font->FontName;
  }
  
  public function getId(){
    return $this->_font->FontID;
  }

  public function getPreview() {
    $text = "<span style=\"font-family: '" . $this->_font->font->FontCSSName ."';\">";
    $text .= $this->_font->font->FontPreviewTextLong;
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
    $font = $this->_font->font;
    $eot = (property_exists($font, 'EOTURL')) ? $font->EOTURL : null;
    $woff = (property_exists($font, 'WOFFURL')) ? $font->WOFFURL : null;
    $ttf = (property_exists($font, 'TTFURL')) ? $font->TTFURL : null;
    $svg = (property_exists($font, 'SVGURL')) ? $font->SVGURL : null;
    $style = StyleDeclaration::font($font->FontCSSName, $eot, $woff, $ttf, $svg);
    $doc->addStyleDeclaration($style);
  }

}