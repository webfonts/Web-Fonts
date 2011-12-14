<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

	define('ROOT_URL', "api.fonts.com");
        define('APPKEY', "693c8014-ddb8-4282-883e-551b375a2ddb1090995");

	define('MAIN_API_URL',"/rest/");

	define('PROJECTS', "Projects/");
	define('PROJECTSTYLES', "ProjectStyles/");
	define('PROJECTSTYLESEXPORT', "ProjectStylesExport/");
	define('PROJECTSTYLESIMPORT', "ProjectStylesImport/");
	define('SELECTORS', "Selectors/");
	define('DOMAINS', "Domains/");
	define('FONTS', "Fonts/");
	define('MESSAGE', "Message");
	define('SUCCESS', "Success");

class Services_WFS{

	protected $wfspstart = 0;
	protected $wfsplimit = 10;
	protected $wfspid = null;
	protected $uri = null;
	protected $wfspParams = null;
	protected $curlPost = null;
	//do we return complete json/xml-formatted responses to the caller of this object,
	//or simple associative arrays with key-value pairs true=complete responses, false=associative arrays
	protected $completeResponses = false;

	protected $public_key = null;
	protected $private_key = null;
	protected $api_key = null;
	
	protected $_header = null;
	protected $_autoPublish = false;
	/**
	* constructs a new WFS API instance
	*/
	public function __construct(){
		//default format for server responses is xml
		$this->uri = "xml/";
		//by default we return associative arrays  to caller
		$this->completeResponses = false;
	}
	
	/**
	*Set the required credentials needed to access the WFS API
	* @param string $publicKey
	* @param string $privateKey
	* @param string $apiKey
	* @return string - message that the server returned to test query.
	*/
	public function setCredentials($publicKey, $privateKey, $apiKey){
		$this->public_key = $publicKey;
		$this->private_key = $privateKey;
		$this->api_key = $apiKey;
		//test the validity of the keys by listing projects
		if($publicKey && $privateKey){
		  $result = $this->listInternal("projects");
		  return $result[MESSAGE];
		}
	}
		
	
	/**
	* set the id of the project that this object will use in requests to Web Fonts Service
	* @param string pid - the project id
	* @return string - message that the server returned to test query.
	*/
	public function setProjectKey($pkey){
		$this->wfspid = $pkey;
		//test the project key validity by listing domains
		$result = $this->listInternal("domains");
		return $result[MESSAGE];		
	}


	/**
	* Change the return format in the request.
	* This automatically causes the API to return complete json/xml responses from the server to the caller
	* @param string $newFormat "json" for json, otherwise defaults to "xml"
	*/
	public function setOutputFormat($newFormat=""){
		if($newFormat == "json"){
			//replace "xml/" with "json/" in URI
			$this->uri = str_replace("xml/", "json/", $this->uri);
			$this->completeResponses = true;
		}
		else if($newFormat == "xml"){	
			//replace "json/" with "xml/" in URI
			$this->uri = str_replace("json/", "xml/", $this->uri);	
			$this->completeResponses = true;
		}
		else{
			$this->completeResponses = false;
		}
			
	}
	
	/**
	* Set request parameters.
	* @param int start - the start page
	* @param int  limit - how many entries to fetch per page
	*/
	public function setWfspParams($start = "", $limit = ""){
		if(is_numeric($start) && is_numeric($limit)){
			$this->wfspParams = "&wfspstart=".$start."&wfsplimit=".$limit;
		}
		else{
			$this->wfspParams = null;
		}
	}

	
	/**
	* Parse the $msg for $values
	* @param string msg - the xml or json-formatted document to parse
	* @param string array $path - the path to the wanted value(s) in the document
	* @param string array $values - the value(s) we want returned
	* @return string array - associative array of key-value-pairs
	*/
	protected function parseOutput($msg, $path, $values){
		$arr = array();
		if($this->formatIsXml()){
			$doc = new DOMDocument();
			$doc->loadXML($msg);
			//get the message-tag value and place it in array which we return
			$message = $doc->getElementsByTagName( MESSAGE )->item(0)->nodeValue;
			$arr[MESSAGE] = $message;				
			if( $message == SUCCESS){
				$entries = $doc->getElementsByTagName( $path );
				//Go through entries and add requested values to the return array
				foreach($entries as $e){	
					$name = $e->getElementsByTagName($values[0])->item(0)->nodeValue;
					$key = $e->getElementsByTagName($values[1])->item(0)->nodeValue;
					$arr[$name] = $key;	
				}		
			}
		}
		else{ //format is json
			$doc = json_decode($msg);
			$message = null;
			if($doc === null) return false;
			/*Get the message-value from the json-document. This can be in different places depending
			on if the message was successful or not.
			if successful, the message is contained within an element (e.g. Projects or Domains)*/
			if(property_exists($doc, MESSAGE)){
				$message = $doc->Message;
			}
			//if there was a failure, the message is at the root-level of JSON-body
			else if(property_exists($doc->$path[0], MESSAGE)){
				$message = $doc->$path[0]->Message;
			}
			$arr[MESSAGE] = $message;
			if($message == SUCCESS){
				//follow the path given as a parameter (e.g. Projects->Project)	
			  if((!property_exists($doc, $path[0])) || (!property_exists($doc->$path[0], $path[1]))) return $arr;
				foreach($doc->$path[0]->$path[1] as $e){
					//for each object, extract the values requested in parameters (e.g. ProjectName, ProjectKey)
					if(is_object($e)){		
						$name = $e->$values[0];
						$key = $e->$values[1];	
					}	
          else {
						$name = $doc->$path[0]->$path[1]->$values[0];
						$key = $doc->$path[0]->$path[1]->$values[1];	
          }
					$arr[$name] = $key;
				}
			}
		}
		return $arr;
	}
	
	/**
	* Protected helper function to check if response format is xml or json
	* @return bool - true if xml, false if json
	*/
	protected function formatIsXml(){
		if ( substr( $this->uri, 0, 3 ) == "xml" ){
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	* Internal helper function for listing projects, fonts and selectors.
	* This function sets the completeResponses-variable to "false" for the duration of the call
	* so that the calling function will receive key-value-pairs (instead of complete xml/json) as a response, because internally the API
	* works with these values.
	* After the call is made, the completeResponses is set back to the value that it had before the change.
	* @param string what - what we want listed
	* @return string array - associative array of key-value-pairs
	*/ 
	protected function listInternal($what){
		$tempResponses = $this->completeResponses;
		$this->completeResponses = false;
		$response = null;
		switch($what){
			case "projects":
				$response = $this->listProjects();
				break;
			case "domains":
				$response = $this->listDomains();
				break;
			case "fonts":
				$response = $this->listFonts();
				break;
			case "selectors":
				$response = $this->listSelectors();
				break;
			default: 
				$response = null;
				break;
		}
		$this->completeResponses = $tempResponses;
		return $response;
	}
  
	/*****************************************
	**************PROJECT FUNCTIONS***********
	*****************************************/

	/**
	* List all projects associated with this $wfs-object
	* @return an associative array of project name => project key -value pairs or xml/json if completeResponses is true
	**/
	public function listProjects(){
	  $limit = ($this->wfspParams) ? '?' . substr($this->wfspParams, 1) : '';
	  $returnMsg = $this->wfs_getInfo_post("", PROJECTS . $limit);	
    
		//return whole xml/json and do nothing else?
		if($this->completeResponses){
			return $returnMsg;
		}
		return $this->parseProjects($returnMsg);
	}

	/**
	* Get projects associated with this WFS account and search for projectKey corresponding the name given as a parameter
	* @param string projectName - the name of project whose key we want to obtain
	* @return string - The projectKey corresponding given projectName or null if error or not found
	*/
	public function getProjectKeyByName($projectName){
		//Get projects
		$projects = $this->listInternal("projects");
		$projectKey = null;
		foreach($projects as $name => $key){
			if($projectName == $name){
				$projectKey = $key;
			}
		}
		return $projectKey;
	} 
  
	/**
	* Protected helper function used to pass requests to parser function
	* @param string msg - the message from which we want projects extracted
	* @return string array - an associative array with project information
	*/
	protected function parseProjects($msg){
		if($msg == null){
			return $msg;
		}
		$projArr = array();
		if ( $this->formatIsXml() ){
			$projArr = $this->parseOutput($msg, "Project", array("ProjectName", "ProjectKey"));
		}
		else{
			$projArr = $this->parseOutput($msg, array("Projects", "Project"), array("ProjectName", "ProjectKey"));
		}		
		return $projArr;	
	}
	/****
	* Project adding function
	*@param string $wfs_project_name
	*@return string
	****/
	public function addProject($wfs_project_name) {
		if(!empty($wfs_project_name)) {
			$this->curlPost.='&wfsproject_name='.$wfs_project_name;
		}
		$request = PROJECTS . $this->wfspParams;
		$response = $this->wfs_getInfo_post("create", $request);

		if($this->completeResponses){
			return $response;
		}
		return $this->parseProjects($response);
	}
  
  	/*
	* Project deleting function
	*@param string - name of project to delete
	*@return string - the remaining projects
	*/
	public function deleteProject($wfs_project_name){
		$projectID = $this->findProjectIdByName($wfs_project_name);
		$response = null;
		//ID was found, carry on with actual deletion
		if($projectID != null){
			$request = PROJECTS ."?wfspid=" .urlencode($projectID) . $this->wfspParams;
			$response = $this->wfs_getInfo_post("delete", $request);
      if($projectID == $this->wfspid)
          $this->wfspid = null;
		}
		else{
      //the project to be deleted wasn't found. return list of known projects.
			$request = PROJECTS . "?wfspid=" . $this->wfspid . $this->wfspParams;
			$response =  $this->wfs_getInfo_post("", $request);		
		}
		if($this->completeResponses){
			return $response;
		}
		return $this->parseProjects($response);
	}
  
  	/**
	*Return the projectID corresponging the given project name
	*@param string projectName - the project name whose ID we want to get
	*@return string - the ID of the project, or null if not found
	*/
	protected function findProjectIdByName($projectName){
		//get the list of existing projects
		$projectList = $this->listInternal("projects");
		$projectID = null;
		return empty($projectList[$projectName]) ? null : $projectList[$projectName]; 	
	}

  
	/*****************************************
	**************FONT FUNCTIONS**************
	*****************************************/

	/*
	* List fonts from the project
	* @return string array - an associative array of project name/project key - pairs
	*/
	public function listFonts(){
		$request = FONTS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		$returnMsg = $this->wfs_getInfo_post("", $request);	
		
		//return whole xml/json and do nothing else?
		if($this->completeResponses){
			return $returnMsg;
		}

		$fontsArr = array();
		if ( $this->formatIsXml() ){
			$fontsArr = $this->parseOutput($returnMsg, "Font", array("FontCSSName", "FontID"));
		}
		//if return format is json
		else if ( substr( $this->uri, 0, 4 ) == "json" ){
			$fontsArr = $this->parseOutput($returnMsg, array("Fonts", "Font"), array("FontCSSName", "FontID"));
		}
		
		return $fontsArr;
	}

	/**
	*Return the fontID corresponging the given font name
	*@param string fontName - the font name whose ID we want to get
	*@return string - the ID of the font, or null if not found
	*/
	protected function findFontIdByName($fontName){
		//get the list of existing fonts
		$fontList = $this->listInternal("fonts");
		$fontID = null;
		return empty($fontList[$fontName]) ? null : $fontList[$fontName]; 
	}


	/*****************************************
	************DOMAIN FUNCTIONS**************
	*****************************************/

	/*
	* List domains from the project 
	*/
	public function listDomains(){
		$request = DOMAINS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		$returnMsg = $this->wfs_getInfo_post("", $request);	
		//return whole xml/json and do nothing else?
		if($this->completeResponses){
			return $returnMsg;
		}
		
		return $this->parseDomains($returnMsg);
	}
	
	/**
	* Protected helper function used to pass requests to parser function
	* @param string msg - the message from which we want domains extracted
	* @return string array - an associative array with domain information
	*/
	protected function parseDomains($msg){
		if($msg == null){
			return $msg;
		}
		$domArr = array();
		if ( $this->formatIsXml() ){
			$domArr = $this->parseOutput($msg, "Domain", array("DomainName", "DomainID"));
		}
		else{
			$domArr = $this->parseOutput($msg, array("Domains", "Domain"), array("DomainName", "DomainID"));
		}		
		return $domArr;	
	}
	
	/**
	*Return the domainID corresponging the given domain name
	*@param string domainName - the domain name whose ID we want to get
	*@return string - the ID of the domain, or null if not found
	*/
	protected function findDomainIdByName($domainName){
		//get the list of existing domains
		$domainList = $this->listInternal("domains");
		$domainID = null;
		return empty($domainList[$domainName]) ? null : $domainList[$domainName]; 	
	}

	/*
	* Domain adding function
	*@param string $wfs_domain_name
	*@return string
	*/
	public function addDomain($wfs_domain_name) {
		if(!empty($wfs_domain_name)) {
			$this->curlPost.='&wfsdomain_name='.$wfs_domain_name;
		}
		$request = DOMAINS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
		$response = $this->wfs_getInfo_post("create", $request);
		if($this->completeResponses){
			return $response;
		}
		return $this->parseDomains($response);
	}

	/*
	* Domain deleting function
	*@param string - name of domain to delete
	*@return string - the remaining domains in the project
	*/
	public function deleteDomain($wfs_domain_name){
		$domainID = $this->findDomainIdByName($wfs_domain_name);
		$response = null;
		//ID was found, carry on with actual deletion
		if($domainID != null){
			$request = DOMAINS ."?wfspid=" . $this->wfspid . "&wfsdomain_id=".urlencode($domainID) . $this->wfspParams;
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
			$response = $this->wfs_getInfo_post("delete", $request);
		}
		else{//the domain to be deleted wasn't found. return list of known domains.
			$request = DOMAINS . "?wfspid=" . $this->wfspid . $this->wfspParams;
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
			$response =  $this->wfs_getInfo_post("", $request);		
		}
		if($this->completeResponses){
			return $response;
		}
		return $this->parseDomains($response);
	}

	/*
	* Domain editing function
	*@param string $old_domain_name - the old name of domain
	*@param string $new_domain_name - the new name of domain
	*@return string - the domains in the project
	*/
	public function editDomain($old_domain_name, $new_domain_name) {
		
		$wfs_domain_id = $this->findDomainIdByName($old_domain_name);
		if(!empty($new_domain_name)) {
			$this->curlPost.='&wfsdomain_name='.$new_domain_name;
		}
		$request = DOMAINS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		if(!empty($wfs_domain_id)) {
			$request.='&wfsdomain_id='.urlencode($wfs_domain_id);
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
		} 
		$response = $this->wfs_getInfo_post("update", $request);
		
		if($this->completeResponses){
			return $response;
		}
		return $this->parseDomains($response);
	}


	/*****************************************
	************SELECTOR FUNCTIONS************
	*****************************************/

	/*
	* List Selectors from the project 
	*/
	public function listSelectors(){
		$request = SELECTORS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		$returnMsg = $this->wfs_getInfo_post("", $request);	
		//return whole xml/json and do nothing else?
		if($this->completeResponses){
			return $returnMsg;
		}
		return $this->parseSelectors($returnMsg);
	}
	
	/**
	* Protected helper function used to pass requests to parser function
	* @param string msg - the message from which we want selectors extracted
	* @return string array - an associative array with selector information
	*/
	protected function parseSelectors($msg){
		if($msg == null){
			return $msg;
		}
		$selArr = array();
		if ( $this->formatIsXml() ){
			$selArr = $this->parseOutput($msg, "Selector", array("SelectorTag", "SelectorID"));
		}
		else if ( substr( $this->uri, 0, 4 ) == "json" ){
			$selArr = $this->parseOutput($msg, array("Selectors", "Selector"), array("SelectorTag", "SelectorID"));
		}
		
		return $selArr;	
	}

	/**
	* Protected helper function to find out an id of selector based on its name.
	*@param string selectorName - the selector tag name whose ID we want to get
	*@return string - the ID of the selector, or null if not found
	*/
	protected function findSelectorIdByName($selectorName){
		//get the list of existing selectors
		$selectorList = $this->listInternal("selectors");
		$selectorID = null;
		//search through the list to find the id for the selector
		return empty($selectorList[$selectorName]) ? null : $selectorList[$selectorName]; 
	}

	/*
	* Selector adding
	*@param string $wfs_selector_tag
	*@return string
	*/
	public function addSelector($wfs_selector_tag){
		if(!empty($wfs_selector_tag)) {
			$this->curlPost.='&wfsselector_tag='.urlencode($wfs_selector_tag);
		}
		$request = SELECTORS . "?wfspid=" . $this->wfspid . $this->wfspParams;
		$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
		$response = $this->wfs_getInfo_post("create", $request);
		if($this->completeResponses){
			return $response;
		}
		return $this->parseSelectors($response);
	}

	/*
	* Selector deletion
	*@param string $wfs_selector_tag
	*@return string - the remaining selectors in the project
	*/
	public function deleteSelector($wfs_selector_tag){
		$selectorID = $this->findSelectorIdByName($wfs_selector_tag);
		//ID was found, carry on with actual deletion
		$response = null;
		if($selectorID != null){
			$request = SELECTORS ."?wfspid=" . $this->wfspid . "&wfsselector_id=".urlencode($selectorID) . $this->wfspParams;
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
			$response =  $this->wfs_getInfo_post("delete", $request);
		}
		else{//the selector to be deleted wasn't found. return list of known selectors.
			$request = SELECTORS . "?wfspid=" . $this->wfspid . $this->wfspParams;	
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
			$response = $this->wfs_getInfo_post("", $request);
		}
		if($this->completeResponses){
			return $response;
		}
		return $this->parseSelectors($response);
		
	}


	/*
	* selector saving function
	* This function is used to pass the request to the server to assign font id's to selector id's
	*@param string $wfs_font_names - comma-separated list of font names
	*@param string $wfs_selector_names- comma-separated list of selector names
	*@return array - the selectors in the project
	*/
		public function saveSelector($wfs_font_names, $wfs_selector_names){
		//split the strings by commas and if the resulting arrays don't match in size, return an error
		$fontArr = explode(",", $wfs_font_names);
		$selectorArr = explode(",", $wfs_selector_names);
		if(sizeof($fontArr) != sizeof($selectorArr)){
			$arr = array(MESSAGE => "ParameterNumberMismatch");
			return $arr;
		}


		$fontList = $this->listInternal("fonts");

		//Replace each font name in fontArr by fontId
		for($i = 0; $i < sizeof($fontArr); $i++){

			$fid = $fontList[trim($fontArr[$i])];
			if($fid != null){
				$fontArr[$i] = $fid;
			}
			else{
				return array(MESSAGE => "NoFontFound", "Font" => $fontArr[$i]);
			}
			
		}
		
		$selectorList = $this->listInternal("selectors");	
		//Replace each selector tag in selectorArr by selectorId
		for($i = 0; $i < sizeof($selectorArr); $i++){
			$sid = $selectorList[trim($selectorArr[$i])];
			if($sid != null){
				$selectorArr[$i] = $sid;
			}
			else{
				return array(MESSAGE => "NoSelectorFound", "Selector" => $selectorArr[$i]);
			}	
		}
		
		//convert fontArr and selectorArr back to comma-separated strings and pass the request to server 
		$wfs_font_ids = implode(",", $fontArr);
		$wfs_selector_ids = implode(",", $selectorArr);
		
		$response = null;
		
		if(!empty($wfs_font_ids) && !empty($wfs_selector_ids)) {
			$this->curlPost.='&wfsfont_ids='.urlencode($wfs_font_ids);
			$this->curlPost.='&wfsselector_ids='.urlencode($wfs_selector_ids);
			$request = SELECTORS . "?wfspid=" . $this->wfspid . $this->wfspParams;
			$request .= ($this->_autoPublish) ? '' : '&wfsnopublish=1';
			$response = $this->wfs_getInfo_post("update", $request);		
		}
		if($this->completeResponses){
			return $response;
		}
		return $this->parseSaveSelectorsOutput($response);
	}
	
	/**
	* Protected helper function for parsing save selector-command output.
	* @param string msg - the output from save selectors-command
	* @return array - associative array containing information about selectors, their ids, and their matching fonts
	*/
	protected function parseSaveSelectorsOutput($msg){
		$arr = array();
		if($msg == null){
			return $msg;
		}
		if($this->formatIsXml()){
			$doc = new DOMDocument();
			$doc->loadXML($msg);
			//get the message-tag value and place it in array which we return
			$message = $doc->getElementsByTagName( MESSAGE )->item(0)->nodeValue;
			$arr[MESSAGE] = $message;				
			if( $message == SUCCESS){
				$entries = $doc->getElementsByTagName( "Selector" );
				foreach($entries as $e){
					//Go through entries and add requested values to the return array
					$tag = $e->getElementsByTagName("SelectorTag")->item(0)->nodeValue;
					$id = $e->getElementsByTagName("SelectorID")->item(0)->nodeValue;
					$fontid = $e->getElementsByTagName("SelectorFontID")->item(0)->nodeValue;
					$arr[$tag] = array($id, $fontid);	
				}		
			}
		}
		else{ //format is json
			$doc = json_decode($msg);
			$message = null;
			/*Get the message-value from the json-document. This can be in different places depending
			on if the message was successful or not
			the message is contained within an element (e.g. Projects or Domains) if success*/
			if(property_exists($doc, MESSAGE)){
				$message = $doc->Message;
			}
			//the message is at the root-level of JSON-body if there was a failure 
			else if(property_exists($doc->$path[0], MESSAGE)){
				$message = $doc->$path[0]->Message;
			}
			//the response message is somehow wrong, message-element couldn't be found
			else{ 
				die("Server responded with unknown message");
			}
			$arr[MESSAGE] = $message;
			if($message == SUCCESS){	
				foreach($doc->Selectors->Selector as $e){
					if(is_object($e)){						
						$tag = $e->SelectorTag;
						$id = $e->SelectorID;
						$fontid = $e->SelectorFontID;	
						$arr[$name] = array($id, $fontid);
					}	
				}
			}
		}
		return $arr;
	}

	/*****************************************
	**************STYLESHEET FUNCTIONS********
	*****************************************/
  
	/**
	* Stylesheet adding function
	*@param token $wfs_project_token (later we'll use project name)
	*@return string
	**/
	public function exportStyleSheet($wfs_project_name) {
	  $projectID = $this->findProjectIdByName($wfs_project_name);
	  $request = PROJECTSTYLESEXPORT . "?wfspid=" . $projectID . $this->wfspParams;
	  $response = $this->wfs_getInfo_post("", $request);
	  if($this->completeResponses)
	    return $response;
	  else
	    return $this->parseProjectStyleExport($response);
	}

	/**
	* Stylesheet adding function
	*@param token $wfs_project_token (later we'll use project name)
	*@return string
	**/
	public function importStyleSheet($wfs_project_token) {
	  $request = PROJECTSTYLESIMPORT . "?wfspid=" . $this->wfspid . "&wfsptoken=" . urlencode($wfs_project_token) . $this->wfspParams;
	  $response = $this->wfs_getInfo_post("", $request);
	  if($this->completeResponses)
	    return $response;
	  else
	    return $this->parseProjectStyle($response);
	}
	
	/**
	 * Stylesheet adding function
	 *@param token $wfs_project_token 
	 *@param name $selectorIDs comma separated ids 
	 *@return string
	 **/
	public function addStyleSheet($wfs_project_token, $selectorIDs) {
	  if($selectorIDs != null) {
	    $this->curlPost .= '&wfsselector_ids='.urlencode($selectorIDs);
	  }
	  $request = PROJECTSTYLES . "?wfspid=" . $this->wfspid . "&wfsptoken=" . urlencode($wfs_project_token) . $this->wfspParams;
	  $response = $this->wfs_getInfo_post("create", $request);
	  if($this->completeResponses)
	    return $response;
	  else
	    return $this->parseProjectStyle($response);
	}
  
  
  	/**
	 * Protected helper function used to pass requests to parser function
	 * @param string msg - the message from which we want projects extracted
	 * @return string array - an associative array with project information
	 */
	protected function parseProjectStyleExport($msg){
	  if($msg == null){
	    return $msg;
	  }
	  $projArr = array();
	  $projArr = $this->parseProjectStyleExportOutput($msg, "ProjectStyles");
	  return $projArr;	
	}
  
  
  /**
	* Parse the $msg for $values
	* @param string msg - the xml or json-formatted document to parse
	* @return string array - associative array of key-value-pairs
	*/
	protected function parseProjectStyleExportOutput($msg, $path){
		$arr = array();
		if($this->formatIsXml()){
			$doc = new DOMDocument();
			$doc->loadXML($msg);
			//get the message-tag value and place it in array which we return
			$message = $doc->getElementsByTagName( MESSAGE )->item(0)->nodeValue;
			$arr[MESSAGE] = $message;				
			if( $message == SUCCESS){
			  //get the token value and place it in array which we return
        if( $doc->getElementsByTagName( "ProjectToken" )->item(0) != null) {
          $token = $doc->getElementsByTagName( "ProjectToken" )->item(0)->nodeValue;
			    if($token != null) {
            if( $doc->getElementsByTagName( "ProjectID" )->item(0) != null) {
              $id = $doc->getElementsByTagName( "ProjectID" )->item(0)->nodeValue;
              $arr[$id] = $token;	
            }
          }
        }
			}
		}
		else{ //format is json
			$doc = json_decode($msg);
			$message = null;
			/*Get the message-value from the json-document. This can be in different places depending
			on if the message was successful or not.
			if successful, the message is contained within an element (e.g. Projects or Domains)*/
			if(property_exists($doc, MESSAGE)){
				$message = $doc->Message;
			}
			else if(property_exists($doc->$path, MESSAGE)){
				$message = $doc->$path->Message;
			}
			$arr[MESSAGE] = $message;
			if($message == SUCCESS){
			  if(property_exists($doc, "ProjectToken")){
				  $token = $doc->ProjectToken;
          $id = $doc->ProjectID;
				  $arr[$id] = $token;
			  }
        else if(property_exists($doc->$path, "ProjectToken")){
				  $token = $doc->$path->ProjectToken;
				  $id = $doc->$path->ProjectID;
				  $arr[$id] = $token;
			  }
			}
		}
		return $arr;
	}
    
  
  	/**
	* Protected helper function used to pass requests to parser function
	* @param string msg - the message from which we want projects extracted
	* @return string array - an associative array with project information
	*/
	protected function parseProjectStyle($msg){
		if($msg == null){
			return $msg;
		}
		$projArr = array();
		if ( $this->formatIsXml() ){
			$projArr = $this->parseOutput($msg, "ProjectStyle", array("SelectorTag", "SelectorID"));
		}
		else{
			$projArr = $this->parseOutput($msg, array("ProjectStyles", "ProjectStyle"), array("SelectorTag", "SelectorID"));
		}		
		return $projArr;	
	}


	/*
	 *core function for communication with api
	 * @param string method
	 * @param string uriEnding - the uri part after http://api.fonts.com/rest/{format}/...
	 * @param string protocol - the connection type, https needed for getAccountAuthenticationKey
	 */
	public function wfs_getInfo_post($method = "", $uriEnding, $protocol = 'http'){
	  $curlurl = $protocol . '://' . ROOT_URL.MAIN_API_URL.$this->uri.$uriEnding;
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
		$this->_thanksWindoze($ch);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data=curl_exec($ch);
		curl_close($ch);
		if(trim($data)==""){
			//If the server returns an empty response, there is a fatal error in the
			//request passed to the server and there's nothing we can do.
			//Die and show the last passed curl-call to the user. 
		  throw new Exception("Curl received empty response from server to call: " . $curlurl);
		}
		return $data;
	}
	/*end curl*/

	protected function _thanksWindoze(&$ch){
	  $server = JRequest::getVar('SERVER_SOFTWARE', '', 'server');
	  if(stripos($server, 'Microsoft') !== false) curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
	}

	public function sign($message, $publicKey, $privateKey){
		return base64_encode(hash_hmac('md5', $publicKey."|".$message, $privateKey, true));
	}

	protected function _getHeader($finalHeader){
	  if($this->_header) return $this->_header;
	  return array("Authorization: " . urlencode($finalHeader), "AppKey: ".$this->api_key);
	}

} //end class Services_WFS