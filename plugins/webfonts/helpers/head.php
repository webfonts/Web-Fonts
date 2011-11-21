<?php
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class WFHeadHelper {

  protected $_styles = array();
  protected $_isAdmin = null;

  public function addStyleDeclaration($selector, $family, $fallBack = false, $style = null, $weight = null){
    $buffer = (strpos($selector, 'body') !== 0) ? 'body ' : '';
    $buffer .= $selector . " {" . PHP_EOL;
    $buffer .= "font-family: '" . $family . "'";
    $buffer .= ($fallBack) ? ", " . $fallBack . " !important;" . PHP_EOL : '!important;' . PHP_EOL;
    $buffer .= ($weight && is_numeric($weight)) ? 'font-weight: ' . $weight . ';' . PHP_EOL : '';
    $buffer .= ($style && ctype_alpha($style)) ? 'font-style: ' . $style . ';' . PHP_EOL : '';
    $buffer .= "}" . PHP_EOL;
    $this->_styles[] = $buffer;
  }

  /* Last line in Head element to override equivalent styles */
  public function insertBeforeClosingTag($script, $commentMarker){
    $response =& JResponse::getBody();
    if(strpos($response, $commentMarker) > 0) return;
    $script .= $commentMarker . PHP_EOL . '</head>';
    $response = str_ireplace('</head>', $script, $response);
    JResponse::setBody($response);
  }

  public function loadAllStyleDeclarations(){
    if(empty($this->_styles)) return;
    $doc =& JFactory::getDocument();
    foreach($this->_styles as $style){
      $doc->addStyledeclaration($style);
    }
    $this->resetStyles();
  }

  public function getStyleDeclarations(){
    return $this->_styles;
  }

  public function resetStyles(){
    $this->_styles = array();
  }

}