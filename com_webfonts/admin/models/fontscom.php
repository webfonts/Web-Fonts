<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.modellist');

class WebfontsModelFontscom extends JModelList {

  const FONTSCOMRECORD = 1;
  const DAYSBETWEENFILTERUPDATES = 5;
  protected $_service = null;
  protected $_table = null;
  protected $_totalResults = 0;
  protected $_fontSearch = array();

  public function __construct($config = array()){
    parent::__construct($config);
    $this->_table = (array_key_exists('table', $config)) ? $config['table'] : $this->_getWebFontVendorInfo();
    $this->_service = (array_key_exists('service',$config)) ? $config['service'] : new WFServiceDecorator($this->_table->properties);
  }

  public function newAccount($post){
    $fields = $this->_getValidatedNewAccount($post);
    $response = new ResponseFontscom($this->_service->newAccount($fields['firstName'], $fields['lastName'], $fields['email']),
				     array('Success' => JText::_('WF_ACCOUNT_CREATED'),
					   'UserAlreadyRegistered' => JText::_('WF_USER_REGISTERED')));			     
    if($response->wasSuccessful()) $this->_saveVendorInfo($fields);
    return $response;
  }

  protected function _saveVendorInfo($fields){
    extract($fields);
    $this->_table->properties->account->email = $email;
    $this->_table->properties->account->firstName = $firstName;
    $this->_table->properties->account->lastName = $lastName;
    $this->_table->store();
  }

  public function setProject($wfspid){
    if(!$wfspid) return false;
    //    if($this->_table->properties->wfspid === $wfspid) return true;
    $this->_table->properties->wfspid = $wfspid;
    $this->_table->store();
    $this->_syncProjectToAPI();
  }
 
  protected function _getValidatedNewAccount($post){
    $validator = $this->_getValidatorNewAccount();
    if($validator->validate(new GenericFieldsCandidate($post))) return $post;
    throw new Exception(array_pop($validator->getErrors()));
  }

  protected function _getValidatorNewAccount(){
    $validator = new GenericValidationFacade;
    $validator->addValidator(new GenericValidator(new GenericEmailSpec('email'), JText::_('WF_VALID_EMAIL')));
    $validator->addValidator(new GenericValidator(new GenericAlphaNumericSpec('firstName'), JText::_('WF_VALID_FIRSTNAME')));
    $validator->addValidator(new GenericValidator(new GenericAlphaNumericSpec('lastName'), JText::_('WF_VALID_LASTNAME')));
    return $validator;
  }

  public function getKey($post){
    extract($post);
    $response = new ResponseFontscom($this->_service->getAccountAuthenticationKey($email, $password),
				     array('Success' => JText::_('WF_KEY_RETRIEVED'),
					   'IsLoginFailed' => JTEXT::_('WF_KEY_LOGIN_FAIL')));
    if($response->wasSuccessful()) $this->_saveAccountAuthKey($response->Account->AuthorizationKey);
    return $response;
  }

  public function saveKey($authKey){
    $response = new ResponseFontscom(json_encode(new WebfontsMockResponse('Success')), 
				     array('Success' => JText::_('WF_KEY_SAVED')),
				     '');
    if(!$this->_saveAccountAuthKey($authKey))
      throw new Exception('Failed to save Authorization Key');
    return $response;
  }

  protected function _saveAccountAuthKey($key){
    $this->_table->properties->key = $key;
    return $this->_table->store();
  }

  public function getProjects(){
    if(!$this->_table->properties->key) return false;
    $this->_service->setWfspParams('0','50');
    $projects = json_decode($this->_service->listProjects());
    if((!property_exists($projects, 'Projects')) || ($projects->Projects->Message !== 'Success')) return false;
    return $projects->Projects;
  }

  public function getProperties($public = true){
    return $this->_table->properties;
  }

  public function getDomains(){
    $wfspid = $this->_getProjectId();
    if(!$wfspid) return false;
    $this->_service->setProjectKey($wfspid);
    $domains = json_decode($this->_service->listDomains());
    if($this->_theseAreNotTheDomainsYourLookingFor($domains)) return false;
    return (!is_array($domains->Domains->Domain)) ? array($domains->Domains->Domain) : $domains->Domains->Domain;
  }

  protected function _theseAreNotTheDomainsYourLookingFor($domains){
    if(!$domains) return true;
    if((!property_exists($domains, 'Domains')) || ($domains->Domains->Message !== 'Success')) return true;
    if(!property_exists($domains->Domains, 'Domain')) return true;
  }

  protected function _getProjectId(){
    $wfspid = JRequest::getVar('wfspid', false);
    if($wfspid === 'create') return false;
    if(!$wfspid) $wfspid = $this->_table->properties->wfspid;
    return $wfspid;
  }

  public function saveProject($post){
    if($post['wfspid'] !== 'create') $this->_service->setProjectKey($post['wfspid']);
    $project = $this->_saveProject($post);
    $domainResponse = $this->_saveDomains($post['domains']);
    $this->_markNeedsPublishing();
    if($project) return $project;
    if($domainResponse) return $domainResponse;
    throw new Exception("Failed to save project.");
  }

  protected function _saveProject(&$post){
    $project = $this->_saveProjectName($post);
    if(!$project) return false;
    if(!is_array($project->Project)) $project->Project = array($project->Project);
    $this->_table->properties->wfspid = $project->Project[0]->ProjectKey;
    $this->_table->store();
    return $project;
  }

  protected function _saveProjectName(&$post){
    if($post['wfspid'] === 'create') return $this->_createNewProject($post['projectName']);
    if($this->_projectNameHasNotChanged($post)) return false;
    return $this->_updateProjectName($post['wfspid'], $post['projectName']);
  }

  protected function _projectNameHasNotChanged($post){
    return ($post['oldName'] === '') || ($post['oldName'] === $post['projectName']);
  }

  protected function _saveDomains(&$domains){
    if(!is_array($domains)) return;
    $current = $this->getDomains();
    $this->_processForWWWorHttp($domains);
    $response = $this->_saveNewDomains($domains);
    if($response && !$response->wasSuccessful()) throw new Exception($response->getMessage());
    $response = $this->_editDomains($current, $domains);
    if($response && !$response->wasSuccessful()) throw new Exception('Failed to update domain names.');
    $response = $this->_deleteDomains($current, $domains);
    if($response && !$response->wasSuccessful()) throw new Exception('Failed to delete domain name.');
    return new ResponseFontscom(json_encode(new WebfontsMockResponse('Success')), 
				array('Success' => JText::_('WF_PROJECT_UPDATED')));
  }

  protected function _saveNewDomains(&$domains){
    $response = null;
    foreach($domains as $key => $value){
      if((!$this->_doesThisHaveAFontscomKey($key)) && ($value !== 'anotherdomain.com')){
	$response = new ResponseFontscom($this->_service->addDomain($value),
					 array('Success' => JText::_('WF_PROJECT_UPDATED'),
					       'Duplicate DomainName' => JText::_('WF_DOMAIN_DUPLICATE')),
					 'Domains');
      } 
    }
    return ($response) ? $response : false;
  }

  protected function _processForWWWorHttp(&$domains){
    foreach($domains as &$value){
      $value = strtolower($value);
      $value = str_replace(array('http://','https://'), array('',''), $value);
      if(substr($value, 0, 4) === 'www.') $value = substr($value, 4);
    }
  }

  protected function _doesThisHaveAFontscomKey($key){
    return (is_numeric($key)) ? false : true;
  }

  protected function _editDomains(&$current, $domains){
    if(!is_array($current)) $current = array($current);
    $response = null;
    foreach($current AS $domain){
      if(!$domain) continue;
      if($this->_domainNameChanged($domain, $domains)){
	$response = new ResponseFontscom($this->_service->editDomain($domain->DomainName, $domains[$domain->DomainID]),
					 array('Success' => JText::_('WF_PROJECT_UPDATED')),
					 'Domains');
      }
    }
    return ($response) ? $response : false;
  }

  protected function _domainNameChanged($domain, $domains){
    return ((array_key_exists($domain->DomainID, $domains)) && ($domains[$domain->DomainID] !== $domain->DomainName));
  }

  protected function _deleteDomains(&$current, $domains){
    if(!is_array($current)) $current = array($current);
    $response = null;
    foreach($current AS $domain){
      if(!$domain) continue;
      if($this->_domainWasDeleted($domain, $domains)){
	$response = new ResponseFontscom($this->_service->deleteDomain($domain->DomainName),
					 array('Success' => JText::_('WF_PROJECT_UPDATED')),
					 'Domains');
      }
    }
    return ($response) ? $response : false;
  }

  protected function _domainWasDeleted($domain, $domains){
    return (!array_key_exists($domain->DomainID, $domains));
  }

  protected function _updateProjectName($wfspid, $newName){
    $result = $this->_service->editProjectName($wfspid, $newName);
    $response = new ResponseFontscom($result, 
				     array('Success' => JText::_('WF_PROJECT_UPDATED')), 
				     'Projects');
    if($response->wasSuccessful()) return $response;
    return false;
  }

  protected function _createNewProject($name){
    $result = $this->_service->addProject($name);
    $response = new ResponseFontscom($result, 
				     array('DuplicateProjectName' => JText::_('WF_DUPLICATE_PROJECT'),
					   'Success' => JText::_('WF_PROJECT_CREATED')), 
				     'Projects');
    if(!$response->wasSuccessful()) return false;
    if(is_array($response->Project)){
      $this->_service->setProjectKey($response->Project[0]->ProjectKey);
    } else {
      $this->_service->setProjectKey($response->Project->ProjectKey);
    }
    return $response;
  }

  protected function _getWebFontVendorInfo(){
    $table = JTable::getInstance('vendor', 'JTable');
    $table->load(self::FONTSCOMRECORD);
    if(!$table->properties) {
      $properties = new stdClass;
      $properties->account = array('email' => '', 'firstName' => '', 'lastName' => '');
      $properties->key = '';
      $properties->designers = array('lastUpdated' => false, 'designer' => array());
      $properties->foundries = array('lastUpdated' => false, 'foundry' => array());
      $properties->classifications = array('lastUpdated' => false, 'classification' => array());
      $properties->languages = array('lastUpdated' => false, 'language' => array());
      $properties->wfspid = null;
      $properties->published = null;
      $table->properties = $properties;
    }
    return $table;
  }

  public function getFonts(){
    $response = new ResponseFontscom($this->_service->filterFonts($this->_getFontSearchArguments()), 
				     array('Success' => JText::_('WF_FONTS_LISTED')), 
				     'AllFonts');
    if($response->wasSuccessful()) {
      $this->_totalResults = $response->TotalRecords;
      if($response->Font === false) return false;
      return (is_array($response->Font)) ? $response->Font : array($response->Font);
    }
    return false;
  }

  protected function _getFontSearchArguments(){
    if(!empty($this->_fontSearch)) return $this->_fontSearch;
    $this->_fontSearch = array('classification' => JRequest::getInt('classification', 0, 'post'),
			       'designer' => JRequest::getInt('designer', 0, 'post'),
			       'foundry' => JRequest::getInt('foundry', 0, 'post'),
			       'language' => JRequest::getVar('language', 0, 'post'),
			       'keyword' => JRequest::getVar('keyword', '', 'post'),
			       'alphabet' => JRequest::getVar('alpha', 'All', 'post'),
			       'free' => JRequest::getVar('freeorpaid', 'all', 'post'),
			       'limit' => 15,
			       'limitStart' => $this->getState('list.start'));
    return $this->_fontSearch;
  }

  public function getFilters(){
    return new ResponseFontscom($this->_service->getFilteredFilters($this->_getFontSearchArguments()),
				array(),
				'FilterValues');
  }

  protected function _loadFilters($type){
    $response = new ResponseFontscom($this->_service->getFilteredFilter($type, $this->_getFontSearchArguments()),
				     array(),
				     'FilterValues');
    if($response->wasSuccessful()) return $response;
    return $this->_emptyResponseFilters();
  }

  protected function _emptyResponseFilters(){
    $f = new stdClass;
    $f->FilterValue = array();
    return $f;
  }

  public function getTotal(){
    return $this->_totalResults;
  }

  public function addFont($wfspid, $wfsfid){
    if(!$wfspid || !$wfsfid) return $this->_error(JText::_('WF_MISSINGPARAMS_FALTER'));
    $response = new ResponseFontscom($this->_service->addFont($wfspid, $wfsfid),
				     array('Success' => 'Font added to project',
					   'Requested data out of range' => JText::_('WF_FAILED_TRANSACTION'),
					   'PremierFontSelected' => JText::_('WF_PREMIERFONT')),
				     'Fonts');
    if(!$response->wasSuccessful()) return $this->_error($response->getMessage());
    $fonts = (is_array($response->Font)) ? $response->Font : array($response->Font);
    $this->_markNeedsPublishing();
    return $this->_saveFontsNotInList($wfspid, $fonts);
  }

  protected function _saveFontsNotInList($wfspid, $fonts){
    $ids = $this->_getLocalFontIds($wfspid);
    foreach($fonts as $font){
      if(in_array($font->FontID, $ids)) continue;
      $table = JTable::getInstance('fontscom', 'JTable');
      $table->ProjectID = $wfspid;
      $table->FontID = $font->FontID;
      $table->name = $font->FontName;
      $table->family = $font->FontCSSName;
      $table->preview = $font->FontPreviewTextLong;
      $table->store();
    }
    return true;
  }

  protected function _removeFontsNotInList($wfspid, $fonts){
    $list = array();
    foreach($fonts as $font){
      $list[] = $font->FontID;
    }
    $list = implode("','", $list);
    $query = $this->_db->getQuery(true);
    $query->delete('#__webfonts_fontscom')->where("`FontID` NOT IN('" . $list . "')");
    $this->_db->setQuery($query);
    return $this->_db->query();
  }

  protected function _getLocalFontIds($wfspid){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select('FontID')->from('#__webfonts_fontscom')->
      where('`ProjectID` = ' . $db->quote($wfspid));
    $db->setQuery($query);
    return $db->loadResultArray();
  }

  public function removeFont($wfspid, $wfsfid){
    if(!$wfspid || !$wfsfid) return $this->_error(JText::_('WF_MISSINGPARAMS_FALTER'));
    $response = new ResponseFontscom($this->_service->removeFont($wfspid,$wfsfid),
				     array('Success' => 'Font deleted',
					   'NotValidFontId' => JText::_('WF_FALTER_INVALIDID')),
				     'Fonts');
    if(!$response->wasSuccessful()) return $this->_error($response->getMessage());
    $this->_markNeedsPublishing();
    return $this->_removeFont($wfspid, $wfsfid);
  }

  public function removeFontById($wfsfid){
    $query = $this->_db->getQuery(true);
    $query->select('`ProjectID`')->from('`#__webfonts_fontscom`')->where('`FontID` = ' . $this->_db->quote($wfsfid));
    $this->_db->setQuery($query);
    $wfspid = $this->_db->loadResult();
    return $this->removeFont($wfspid, $wfsfid);
  }

  protected function _removeFont($wfspid, $wfsfid){
    $this->_unsetSelectors($wfsfid);
    $this->_db->setQuery("DELETE FROM `#__webfonts_fontscom` WHERE `ProjectID` = " . $this->_db->quote($wfspid) .
			 " AND `FontID` = " . $this->_db->quote($wfsfid));
    return $this->_db->query();
  }

  protected function _unsetSelectors($wfsfid){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->update('`#__webfonts`')->set('`fontId` = NULL, `vendor` = NULL')->where("`fontId` = " . $db->quote($wfsfid) . " AND `vendor` = 'fontscom'");
    $db->setQuery($query);
    return $db->query();
  }

  public function getProjectfontids(){
    $wfspid = JRequest::getVar('wfspid', false);
    if(!$wfspid) $wfspid = $this->_table->properties->wfspid;
    if(!$wfspid) return;
    $db = $this->_db;
    $query = $db->getQuery(true);    
    $query->select('`FontID`')->from('`#__webfonts_fontscom`')->
      where('`ProjectID` = ' . $db->quote($wfspid));
    $db->setQuery($query);
    return $db->loadResultArray();
  }

  protected function _error($error){
     $this->setError($error);
     return false;
  }

  public function getSelectedFonts(){
    $fonts = $this->_getSelectedFontsFromDB();
    $fontsWrapped = array();
    if(!$fonts) return $fontsWrapped;
    foreach($fonts as $font){
      $fontsWrapped[$font->FontID] = new StylesheetFontFontscom($font);
    }
    return $fontsWrapped;
  }

  protected function _getSelectedFontsFromDB(){
    $db = $this->_db;
    $query = $db->getQuery(true);
    $query->select('*')->from('`#__webfonts_fontscom`')
      ->where('`ProjectID` = ' . $this->_db->quote($this->_table->properties->wfspid));
    $db->setQuery($query);
    return $db->loadObjectList();
  }

  public function addSelector($selector){
    // Not used for Fontscom
  }

  public function addSelectorWithFont($selector, $wfsfid){
    $this->_service->setProjectKey($this->_table->properties->wfspid);
    $response = new ResponseFontscom($this->_service->addSelector($selector), array(), 'Selectors');
    $sid = $this->_extractSelectorId($response->Selector, $selector);
    if($response->wasSuccessful() && $sid){
      $response = new ResponseFontscom($this->_service->updateSelector($sid, $wfsfid), array(),'Selectors');
      $this->_markNeedsPublishing();
      return $response->wasSuccessful();
    }
    $this->setError(JText::_('WF_SELECTORADD_FAIL'));
  }

  protected function _extractSelectorId($selectors, $tag){
    if(!is_array($selectors)) $selectors = array($selectors);
    foreach($selectors as $selector){
      if($selector->SelectorTag == $tag) return $selector->SelectorID;
    }
    return false;
  }

  public function updateSelectors($local, $fallBacks){
    $this->_service->setProjectKey($this->_table->properties->wfspid);
    $selectors = $this->_getProjectSelectors();
    $changes = $this->_checkForChanges($selectors, $local);
    $this->_performSelectorUpdates($changes['update']);
    $this->_performSelectorRemovals($changes['remove']);
    $this->_addMissingSelectors($changes['missing']);
    $this->_updateFallBackFonts($fallBacks);
    $this->_markNeedsPublishing();
  }

  protected function _getProjectSelectors(){
    $response = new ResponseFontscom($this->_service->listSelectors(),
				     array(),
				     'Selectors');
    if($response->Selector === false) return false;
    return (is_array($response->Selector)) ? $response->Selector : array($response->Selector);
  }
  
  protected function _checkForChanges($service, &$local){
    $changes = array('remove' => array(), 'update' => array(), 'missing' => array());
    foreach($service as $selector){
      if(!$this->_selectorDoesntExistLocal($selector, $local)){
	$changes['remove'][] = array('SelectorID' => $selector->SelectorID, 'SelectorTag' => $selector->SelectorTag);
      }
      $newFont = $this->_getCurrentSelectorFont($selector, $local);
      if(!$newFont) continue;
      if($newFont != $selector->SelectorFontID) {
	$changes['update'][] = array('SelectorID' => $selector->SelectorID, 
				     'SelectorFontID' => $newFont, 
				     'SelectorTag' => $selector->SelectorTag);
      }
    }
    $changes['missing'] = $this->_getMissingSelectors($service, $local);
    return $changes;
  }

  protected function _selectorDoesntExistLocal($selector, &$local){
    foreach($local as $arr){
      if($arr['selector']->selector == $selector->SelectorTag) return true;
    }
    return false;
  }

  protected function _getCurrentSelectorFont($selector, &$local){
    foreach($local as $arr){
      if($arr['selector']->selector == $selector->SelectorTag) {
	return $arr['FontID'];
      }
    }
    return false;
  }

  protected function _getMissingSelectors($service, $local){
    $missing = array();
    foreach($local as $selectorLocal){
      $flag = false;
      foreach($service as $selectorService){
	if($selectorService->SelectorTag == $selectorLocal['selector']->selector) 
	  $flag = true;
      }
      if($flag === false) $missing[] = $selectorLocal;
    }
    return $missing;
  }

  protected function _performSelectorUpdates($updates){
    if(empty($updates)) return;
    foreach($updates as $update){
      $response = new ResponseFontscom($this->_service->updateSelector($update['SelectorID'], $update['SelectorFontID']),
				       array(), 'Selectors');
      if($response->wasSuccessful()) {
	$this->_updateSelectorByTag($update['SelectorTag'], $update['SelectorFontID']);
      }
    }
  }

  protected function _updateSelectorByTag($selector, $wfsfid){
    $this->_db->setQuery('UPDATE `#__webfonts` SET `fontId` = ' . $this->_db->quote($wfsfid) . ', ' . 
			 '`vendor` = ' . $this->_db->quote('fontscom') . 
			 ' WHERE ' .  '`selector` = ' . $this->_db->quote($selector));
    return $this->_db->query();
  }

  protected function _insertSelector($selector, $wfsfid){
    $wf = JTable::getInstance('webfonts', 'JTable');
    $wf->selector = $selector;
    $wf->vendor = 'fontscom';
    $wf->fontId = $wfsfid;
    return $wf->store();
  }

  public function removeSelector($selector){
    $this->_service->setProjectKey($this->_table->properties->wfspid);
    $stacked = array(array('SelectorTag' => $selector->selector));
    $this->_markNeedsPublishing();
    return $this->_performSelectorRemovals($stacked, false);
  }

  protected function _performSelectorRemovals($removals, $update = true){
    foreach($removals as $selector){
      $response = new ResponseFontscom($this->_service->deleteSelector($selector['SelectorTag']), array(), 'Selectors');
      if($response->wasSuccessful()) {
	$tag = $selector['SelectorTag'];
	if($update){
	  $this->_db->setQuery("UPDATE `#__webfonts` SET `vendor` = '', `fontId` = '' WHERE `selector` = " . $this->_db->quote($tag) . " AND `vendor` = 'fontscom'");
	} else {
	  $this->_db->setQuery("DELETE FROM `#__webfonts` WHERE `selector` = " . $this->_db->quote($tag) . " AND `vendor` = 'fontscom'");
	}
	$this->_db->query();
      }
    }
  }

  protected function _addMissingSelectors($selectors){
    $nowAssignIt = array();
    foreach($selectors as $selector){
      $response = new ResponseFontscom($this->_service->addSelector($selector['selector']->selector), array(), 'Selectors');
      $selReturned = (is_array($response->Selector)) ? $response->Selector : array($response->Selector);
      if($response->wasSuccessful()) $nowAssignIt[] = array('SelectorID' => $this->_extractSIDByTag($selReturned, $selector['selector']->selector),
							    'SelectorFontID' => $selector['FontID'],
							    'SelectorTag' => $selector['selector']->selector);

    }
    if(!empty($nowAssignIt)) $this->_performSelectorUpdates($nowAssignIt);
  }

  protected function _extractSIDByTag($selectors, $tag){
    foreach($selectors as $sel){
      if($sel->SelectorTag == $tag) return $sel->SelectorID;
    }
  }

  public function preProcessAvailableSelectors(&$selectors){
    foreach($selectors as $key => $selector){
      if($selector->vendor != 'fontscom') continue;
      $query = $this->_db->getQuery(true);
      $query->select('1')->from('`#__webfonts_fontscom`')->
	where('`FontID` = ' . $this->_db->quote($selector->fontId) . 
	      ' AND `ProjectID` = ' . $this->_db->quote($this->_table->properties->wfspid));
      $this->_db->setQuery($query);
      if(!$this->_db->loadResult()) unset($selectors[$key]);
    }
  }

  protected function _updateFallBackFonts($fallBacks){
    $db = $this->_db;
    foreach($fallBacks as $sid => $stack){
      if(!($sid && $stack)) continue;
      $db->setQuery('UPDATE #__webfonts SET `fallBack` = ' . $db->quote($stack) . ' WHERE `id` = ' . $db->quote($sid));
      $db->query();
    }
  }

  public function updateFallBackForFont($wfsfid, $fallBack){
    $db = $this->_db;
    $db->setQuery('UPDATE #__webfonts SET `fallBack` = ' . $db->quote($fallBack) . ' WHERE `fontId` = ' . $db->quote($wfsfid));
    return $db->query();
  }

  protected function _markNeedsPublishing(){
    $this->_initPublished();
    if($this->_table->properties->published === 0) return;
    $this->_table->properties->published = 0;
    $this->_table->store();
  }

  protected function _initPublished(){
    if(!property_exists($this->_table->properties, 'published')) $this->_table->properties->published = 0;
    $this->_table->store();
  }

  public function publish(){
    if($this->_table->properties->published === 1) return true;
    $this->_service->setProjectKey($this->_table->properties->wfspid);
    $response = new ResponseFontscom($this->_service->publish(), array(), 'Publish');
    if(!$response->wasSuccessful()) return false;
    $this->_table->properties->published = 1;
    $this->_table->store();
    return true;
  }

  public function gotsErrors(){
    $errors = $this->getErrors();
    if(empty($errors)) return false;
    return true;
  }

  public function _syncProjectToAPI(){
    $this->_service->setProjectKey($this->_table->properties->wfspid);
    $this->_syncSelectorsToAPI();
    $this->_syncFontsToAPI();  
    $this->_markNeedsPublishing();
  }

  protected function _syncFontsToAPI(){
    $fonts = $this->_getAPIFontsOnProject();
    if(!$fonts) return;
    $this->_saveFontsNotInList($this->_table->properties->wfspid, $fonts);
    $this->_removeFontsNotInList($this->_table->properties->wfspid, $fonts);
  }

  protected function _syncSelectorsToAPI(){
    $this->_blankFontscomSelectors();
    $selectors = $this->_getProjectSelectors();
    if(!$selectors) return;
    $local = $this->_getLocalSelectorTags();
    foreach($selectors as $selector){
      if(in_array($selector->SelectorTag, $local)) {
	$this->_updateSelectorByTag($selector->SelectorTag, $selector->SelectorFontID);
      } else {
	$this->_insertSelector($selector->SelectorTag, $selector->SelectorFontID);
      }
    }
  }

  protected function _getLocalSelectorTags(){
    $query = $this->_db->getQuery(true);
    $query->select('`selector`')->from('`#__webfonts`');
    $this->_db->setQuery($query);
    return $this->_db->loadResultArray();
  }

  protected function _blankFontscomSelectors(){
    $query = $this->_db->getQuery(true);
    $query->delete()->from('`#__webfonts`')->where("`vendor` = 'fontscom'");
    $this->_db->setQuery($query);
    return $this->_db->query();
  }

  protected function _getAPIFontsOnProject(){
    $response = new ResponseFontscom($this->_service->listFonts(),
    				     array(),
    				     'Fonts');
    if(!$response->wasSuccessful()) return false;
    if($response->Font === false) return false;
    return (!is_array($response->Font)) ? array($response->Font) : $response->Font;
  }

}