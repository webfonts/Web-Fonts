<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die();

abstract class KeyedTranslation {

  protected $_type = null;
  protected $_samples = array();

  public function __construct($type){
    $type = strtolower($type);
    $type = (strpos($type, ' extended')) ? str_replace(' extended', '', $type) : $type;
    $this->_type = (array_key_exists($type, $this->_samples)) ? $type : 'latin';
  }

  public function __toString(){
    return $this->_samples[$this->_type];
  }

}

class WebfontsLanguageSample extends KeyedTranslation {

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
			      'traditional chinese' => '文字之美',
			      'khmer' => 'ខ្ញុំអាច',
			      'vietnamese' => 'Tôi');

}

/* Incomplete */
class WebfontsLanguageSampleLong extends KeyedTranslation {

  protected $_samples = array('latin' => 'Quick Brown Fox Jumps Over The Lazy Dog',
			      'arabic' => 'ونِك',
			      'hebrew' => 'שפה',
			      'armenian' => 'Մէկ',
			      'cyrillic' => 'независимо от языка',
			      'esperanto' => 'AaGg',
			      'greek' => 'ανεξάρτητα από το λειτουργικό',
			      'indic' => 'यूनिक',
			      'japanese' => 'ある美し',
			      'korean' => '유니코드',
			      'simplified chinese' => '文字之美',
			      'symbol' => 'AFMS',
			      'thai' => 'โดยไ',
			      'traditional chinese' => '文字之美',
			      'khmer' => 'ខ្ញុំអាចញ៉ាំកញ្ចក់បាន ដោយគ្មានបញ្ហ',
			      'vietnamese' => 'Tôi có thể ăn thủy tinh mà không hại gì.');

}