<?php

defined('_JEXEC') or die('Access Restricted');

jimport('joomla.application.component.controller');

$cbase = dirname(__FILE__);
JLoader::register('WFServiceDecorator', $cbase . '/helpers/ServiceDecorator.php');
JLoader::register('Services_WFS', $cbase . '/helpers/Services_WFS.php');
JLoader::register('GenericValidationFacade', $cbase . '/helpers/validators.php');
JLoader::register('ResponseFontscom', $cbase . '/helpers/responses.php');
JLoader::register('WebfontsMockResponse', $cbase . '/helpers/responses.php');
JLoader::register('WebfontsModelFontscom', $cbase . '/models/fontscom.php');
JLoader::register('StylesheetFontFontscom', $cbase . '/helpers/fonts.php');
JLoader::register('StylesheetFontCoordinator', $cbase . '/helpers/fonts.php');

$controller = JController::getInstance('webfonts');

try {
  $controller->execute(JRequest::getCmd('task', 'display'));
} catch (Exception $e){
  $controller->setMessage($e->getMessage(), 'error');
}
$controller->redirect();