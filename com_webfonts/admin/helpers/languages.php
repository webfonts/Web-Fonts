<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die();

class WebfontsLanguageSample {

  protected $_type = null;
  protected $_samples = array('latin' => 'AaGg',
			      'arabic' => 'ونِك',
			      'hebrew' => 'שפה',
			      'armenian' => 'Մէկ',
			      'cyrillic' => 'неза',
			      'esperanto' => 'AaGg',
			      'greek' => 'ανεξ',
			      'indic' => 'यूनिक',
			      'japanese' => 'ある美し',
			      'korean' => '유니코드',
			      'simplified chinese' => '文字之美',
			      'symbol' => 'AFMS',
			      'thai' => 'โดยไ',
			      'traditional chinese' => '文字之美');

  public function __construct($type = 'latin'){
    $type = strtolower($type);
    $this->_type = (array_key_exists($type, $this->_samples)) ? $type : 'latin';
  }

  public function __toString(){
    return $this->_samples[$this->_type];
  }  

}