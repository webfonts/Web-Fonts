<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined ('_JEXEC') or die();

class WFServiceDecorator extends Services_WFS {

  public function __construct($properties){
    parent::__construct();
    list ($publicKey, $privateKey) = ($properties->key) ? explode('--', $properties->key) : array('','');
    $this->setCredentials($publicKey, $privateKey, APPKEY);
    $this->setOutputFormat('json');
  }

  protected function _wfs_getInfo_post($method = "", $uriEnding = ''){
    $curlurl = ROOT_URL.MAIN_API_URL.$this->uri.$uriEnding;
    if($method === '') $curlurl .= '&wfsnopublish=1';
    $data="";
    $finalHeader = $this->public_key.":".$this->sign(MAIN_API_URL.$this->uri . $uriEnding, $this->public_key, $this->private_key);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $curlurl);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_getHeader($finalHeader));
    switch($method){
    case "create":
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      break;
    case "update":
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      break;
    case "delete":
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      break;
    default:
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
      break;
    }
    if(!empty($this->curlPost)){
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->curlPost);
      unset($this->curlPost);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $this->_thanksWindoze($ch);
    $data=curl_exec($ch);
    curl_close($ch);
    if(trim($data)==""){
      throw new Exception("Curl received empty response from server to call: " . $curlurl);
    }
    return $data;
  }
  /*end curl*/  
 
  public function newAccount($firstName, $lastName, $email){
    $this->uri = 'json/Accounts/?';
    $this->curlPost = "wfsfirst_name={$firstName}&wfslast_name={$lastName}&wfsemail={$email}";
    return $this->_wfs_getInfo_post("create");
  }
  
  public function getAccountAuthenticationKey($email, $password){
    $this->uri = "json/Accounts/?wfsemail={$email}";
    $this->_header = array('Content-type: text/plain',
			   "AppKey: " . $this->api_key, 
			   "Password: " . $password);
    return $this->_wfs_getInfo_post();
  }
  
  protected function _getHeader($finalHeader){
    if($this->_header) return $this->_header;
    return array("Authorization: " . urlencode($finalHeader), "AppKey: ".$this->api_key);
  }

  public function editProjectName($wfspid, $newName){
    $this->curlPost = 'wfsproject_name=' . $newName;
    $request = 'Projects/?wfspid=' . urlencode($wfspid);
    $request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
    return $this->wfs_getInfo_post("update", $request);
  }

  // This mis-spelling for Foundry is on the API
  public function filterFonts($args){
    extract($args);
    $base = 'AllFonts/?';
    $query = '';
    if($classification) $query .= '&wfsClassId=' . urlencode($classification);
    if($designer) $query .= '&wfsDesignerId=' . urlencode($designer);
    if($foundry) $query .= '&wfsFountryId=' . urlencode($foundry);
    if($language) $query .= '&wfsLangId=' . urlencode($language);
    if($keyword) $query .= '&wfsKeyword=' . urlencode($keyword);
    if($free === '0') $query .= '&wfsfree=true';
    if($alphabet) $query .= '&wfsAlphabet=' . urlencode($alphabet);
    $query .= ($limitStart) ? '&wfspstart=' . $limitStart : '&wfspstart=0';
    $query .= ($limit) ? '&wfsplimit=' . $limit : '&wfsplimit=15';
    $query = $base . substr($query, 1);
    return $this->wfs_getInfo_post('', $query);
  }

  public function getFilter($type){
    return $this->wfs_getInfo_post('', 'FilterValues/?wfsfiltertype=' . $type);
  }

  public function getFilteredFilter($type, $args){
    extract($args);
    $query = '';
    if($classification) $query .= '&wfsclassificationid=' . urlencode($classification);
    if($designer) $query .= '&wfsdesignerid=' . urlencode($designer);
    if($foundry) $query .= '&wfsfoundryid=' . urlencode($foundry);
    if($language) $query .= '&wfslanguageid=' . urlencode($language);
    return $this->wfs_getInfo_post('', 'FilterValues/?wfsfiltertype=' . $type . $query);
  }

  public function getFilteredFilters($args){
    extract($args);
    $query = '';
    if($classification) $query .= '&wfsclassificationid=' . urlencode($classification);
    if($designer) $query .= '&wfsdesignerid=' . urlencode($designer);
    if($foundry) $query .= '&wfsfoundryid=' . urlencode($foundry);
    if($language) $query .= '&wfslanguageid=' . urlencode($language);
    if($free === '0') $query .= '&wfsfreeorpaid=0';
    if($alphabet) $query .= '&wfsalphachar=' . urlencode($alphabet);
    $query = ($query !== '') ? '?' . substr($query, 1) : '';
    return $this->wfs_getInfo_post('', 'AllFilterValues/' . $query);
  }

  public function addFont($wfspid, $wfsfid){
    $this->curlPost = 'wfsfid=' . urlencode($wfsfid);
    $request = 'Fonts/?wfspid=' . urlencode($wfspid);
    $request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
    return $this->wfs_getInfo_post("create", $request);
  }

  public function removeFont($wfspid, $wfsfid){
    $request = 'Fonts/?wfspid=' . urlencode($wfspid) . '&wfsfid=' . urlencode($wfsfid);
    $request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
    return $this->wfs_getInfo_post("delete", $request);
  }
  
  public function addSelector($selector){
    $this->curlPost = 'wfsselector_tag=' . urlencode($selector);
    $request = 'Selectors/?wfspid=' . $this->wfspid;
    $request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';    
    return $this->wfs_getInfo_post('create', $request);
  }

  public function updateSelector($SelectorID, $newFont){
    $this->curlPost = 'wfsselector_ids=' . $SelectorID . '&wfsfont_ids=' . $newFont;
    $request = 'Selectors/?wfspid=' . $this->wfspid;
    $request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
    return $this->wfs_getInfo_post('update', $request);
  }

  public function publish(){
    return $this->wfs_getInfo_post('', 'Publish/');
  }

}
