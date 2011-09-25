<div id="webFonts">

<?php echo JHtml::_('tabs.start'); ?>

<?php echo JHtml::_('tabs.panel', JText::_('WF_SETUP'), 'tab-setup'); ?>

<h3><?php echo JText::_('WF_HEADING_SETUP') ?></h3>

<div id="preexistingAccount">
  
<?php if($this->properties->key): ?>

<script type="text/javascript">
  Window.wfKeySaved = true;
</script>

<div class="notice">
  <h4 class="important"><?php echo JText::_('WF_GOOD_TO_GO') ?></h4>
  <p><?php echo JText::_('WF_GOOD_TO_GO_MSG') ?></p>
</div>
<?php endif; ?>

<?php if($this->_createdNewAccount()): ?>

<div class="notice">

  <h3 class="important"><?php echo JText::_('WF_ACCOUNT_CREATED') ?></h3>
  <p><?php echo JText::_('WF_ACCOUNT_CREATED_NEXT') ?></p>

</div>

<?php endif; ?>

<form name="authenticationKey" id="authenticationKey" action="index.php" method="POST">

<label for="authKey"><?php echo JText::_('WF_ENTER_AUTH_KEY') ?></label>
<input type="text" name="authKey" id="authKey"
       <?php if($this->properties->key):?>
       value="<?php echo $this->properties->key ?>"
       <?php endif; ?>
/>
<input type="submit" value="<?php echo JText::_('SAVE') ?>" />

<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="fontscom.saveKey" />
<?php echo JHTML::_('form.token'); ?>

</form>

<div class="beverlyClearly">&nbsp;</div>

<p><strong><?php echo JText::_('OR') ?></strong></p>

<form id="getKey" action="index.php" method="POST">

<label for="email"><?php echo JText::_('WF_ENTER_EMAIL') ?></label>
<input type="text" name="email" id="email"
       <?php if($this->properties->account->email):?>
       value="<?php echo $this->properties->account->email ?>"
       <?php endif; ?>
/>

<label for="password"><?php echo JText::_('WF_ACCOUNT_PASSWORD') ?></label>
<input type="password" name="password" id="password"/>
<input type="submit" value="<?php echo JText::_('WF_GET_KEY') ?>" />

<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="fontscom.getkey" />
<?php echo JHTML::_('form.token'); ?>
</form>

<div class="beverlyClearly">&nbsp;</div>

<?php if(!$this->_createdNewAccount()): ?>

<p><strong><?php echo JText::_('OR') ?></strong></p>

<input type="button" value="Create Account" id="createAccountButton"/>

<div class="beverlyClearly ramona">&nbsp;</div>

<p><strong><?php echo JText::_('WF_NEWACCOUNT_INCENTIVE') ?></strong></p>

</div><!-- End  preexistingAccount -->

<div id="newAccount" class="hidden">

<form name="newAccount" id="newAccount" action="index.php" method="POST" class="form-validate" onSubmit="return validateNew(this);">

<ul class="fieldList">
  <li>
    <label for="createFirstName"><?php echo JText::_('FIRST_NAME') ?></label>
    <input type="text" name="createFirstName" id="createFirstName"/>
  </li>
  <li>
    <label for="createLastName"><?php echo JText::_('LAST_NAME') ?></label>
    <input type="text" name="createLastName" id="createLastName"/>
  </li>
  <li>
    <label for="createEmail" id="createEmailLabel"><?php echo JText::_('EMAIL_ADDRESS') ?>:<span id="createEmailError" class="hidden error"><?php echo JText::_('INVALID') ?></span></label>
    <input type="text" name="createEmail" id="createEmail" class="validate-email required" />
  </li>
</ul>

<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="fontscom.newaccount" />

<?php echo JHTML::_('form.token'); ?>

<div class="beverlyClearly ramona">&nbsp;</div>

<input type="submit" value="<?php echo JText::_('WF_SIGNUP') ?>" />

<div class="beverlyClearly ramona">&nbsp;</div>

<input type="button" value="<?php echo JText::_('CANCEL') ?>" id="newAccountCancel"/>

</form>

</div><!-- End newAccount -->

<?php endif; ?>

<div class="beverlyClearly">&nbsp;</div>

<?php echo JHtml::_('tabs.panel', JText::_('WF_PROJECT'), 'tab-project'); ?>

<h3><?php echo JText::_('WF_HEADING_PROJECT') ?></h3>

<form name="manageProjects" id="manageProjects" action="index.php" method="POST">
<div class="column">
  <label for="newProjectName"><?php echo JText::_('WF_PROJECT_NAME') ?></label>
  <input type="text" name="projectName" id="projectName" value="<?php echo $this->current['Project'] ?>" />
</div>
<div class="column">
  <label for="currentProject"><?php echo JText::_('WF_CURRENT_PROJECT') ?></label>
  <select id="currentProject" name="wfspid">
    <option value="create"><?php echo JText::_('WF_CREATE_PROJECT') ?></option>
    <?php if($this->projects && (count($this->projects > 0))): ?>
    <?php foreach($this->projects as $project): ?>
    <option value="<?php echo $project->ProjectKey ?>"<?php echo $this->_isCurrentProject($project->ProjectKey) ?>>
<?php echo $project->ProjectName ?></option>
    <?php endforeach; ?>
    <?php endif; ?>
  </select>
</div>

<div class="beverlyClearly ramona">&nbsp;</div>

<ul id="domainList" class="fieldList">
  <li>
    <span class="label"><?php echo JText::_('WF_PUBLISH_DOMAINS') ?><span>
  </li>
  <?php if($this->domains && (count($this->domains > 0))): ?>
  <?php foreach($this->domains as $domain): ?>
  <li>
    <input type="text" name="domains[<?php echo $domain->DomainID ?>]" class="domain" value="<?php echo $domain->DomainName ?>" />
    <input type="button" value="<?php echo JText::_('REMOVE') ?>" class="button removable" />
  </li>
  <?php endforeach; ?>
  <?php endif; ?>  
  <li id="cloneDomain">
    <input type="text" name="domains[]" class="greyed domain" value="www.anotherdomain.com" />
    <input type="button" value="<?php echo JText::_('ADD') ?>" class="button" />
  </li>
</ul>

<input type="button" value="<?php echo JText::_('REMOVE') ?>" class="button hidden" id="cloneRemove" />

<div class="beverlyClearly ramona">&nbsp;</div>

<p class="darkGreyed"><?php echo JText::_('WF_DOMAINS_WHERE') ?></p>

<input type="submit" value="<?php echo JText::_('WF_SAVE_PROJECT') ?>" />
<input type="hidden" name="oldName" value="<?php echo $this->current['Project'] ?>" />
<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="view" value="fontscom" />
<input type="hidden" name="task" value="fontscom.saveProject" id="projectTask"/>
<?php echo JHTML::_('form.token'); ?>

</form>

<div class="beverlyClearly ramona">&nbsp;</div>

<?php echo JHtml::_('tabs.panel', JText::_('WF_FONTS'), 'tab-fonts'); ?>

<div class="m">

<form name="adminForm" action="index.php" method="post" id="fontForm">

<?php if(!$this->_amIEditingThisProject()): ?>

<div class="notice">

  <h3 class="important"><?php echo JText::_('WF_PROJECT_NOT_SELECTED') ?></h3>
  <p><?php echo JText::_('WF_PROJECT_NOT_SELECTED_MSG') ?></p>

</div>

<?php else: ?>

<?php if($this->_imEditingFonts()): ?>

<script type="text/javascript">
  Window.wfEditingFonts = true;
</script>

<?php endif; //end are we editing fonts the projects fonts yes ?>

<fieldset class="t">
  <input type="button" value="<?php echo JText::_('WF_RESET') ?>" id="resetForm" title="<?php echo JText::_('WF_RESET_DESCRIPTION') ?>" />
</fieldset>

<fieldset class="m" id="filters">
  <div class="filter-search fltlft">
    <label class="filter-search-lbl" for="keyword"><?php echo JText::_('WF_KEYWORD') ?>:&nbsp;&nbsp;</label>
    <input type="text" name="keyword" id="keyword" 
	   value="<?php echo JRequest::getVar('keyword', '', 'post'); ?>" 
    title="<?php echo JText::_('WF_KEYWORD_TITLE') ?>"/>
    <button type="submit" class="btn"><?php echo JText::_('SEARCH') ?></button>
    <button type="button" onclick="document.id('keyword').value='';this.form.submit();">Clear</button>
  </div>
  <div class="filter-search fltrt">
    <select name="alphabet" id="alphabet">
      <option value=""><?php echo JText::_('WF_FIRSTLETTER') ?></option>
      <?php $this->_outputAlphabetFilters() ?>
    </select>
   <?php echo $this->filters ?>
  </div>
</fieldset>

<div class="beverlyClearly">&nbsp;</div>

<?php 
$i = 0;
$count = count($this->fonts);
?>

<?php if(($this->fonts) && ($count > 0)): ?>

<table class="adminlist fonts">
  <tr>
    <?php foreach($this->fonts as $font): ?>
    <?php $i++; ?>

    <?php if(!is_object($font)): ?>

    <td>
      <span><?php echo JText::_('WF_MISSING_FONT') ?></span>

    <?php else: ?>

    <?php $selected = ($this->_isFontAlreadyOnThisProject($font->FontID)) ? ' selected' : ''; ?>
    <td class="fontTile<?php echo $selected ?>">

      <span style="font-family: '<?php echo $font->FontCSSName ?>';" class="fontSample">
      <?php echo new WebfontsLanguageSample($font->FontLanguage); ?>
      </span><br /><br />
      <span class="fontLabel"><?php echo $font->FontName ?></span><br />
  <span class="fontLabelLanguage">(<?php echo $font->FontLanguage ?>)</span>
      <div class="hidden">
	<div id="tileMeta<?php echo $i ?>">
	<h4><?php echo $font->FontName ?></h4>
	<ul>
	  <li><?php echo JText::_('FOUNDRY') ?>: <?php echo $font->FontFoundryName ?></li>
	  <li><?php echo JText::_('DESIGNER') ?>: <?php echo $font->Designer ?></li>
	  <li><?php echo JText::_('CLASSIFICATION') ?>: <?php echo $font->Classification ?></li>
	  <li><?php echo JText::_('LANGUAGE') ?>: <?php echo $font->FontLanguage ?></li>
  <li><?php echo JText::_('SIZE') ?>: <?php echo $this->_kiloBite($font->FontSize) ?></li>	  
	</ul>
	<p class="longExample" style="font-family: '<?php echo $font->FontCSSName ?>';">
	<?php echo $font->FontPreviewTextLong ?>
        </p>
	<p class="longExample invert" style="font-family: '<?php echo $font->FontCSSName ?>';">
	<?php echo $font->FontPreviewTextLong ?>
        </p>

	<?php if(!$this->_isFontAlreadyOnThisProject($font->FontID)): ?>

        <input type="hidden" name="fonturls[<?php echo $font->FontID ?>][EOT]" value="<?php echo $font->EOTURL ?>" />
        <input type="hidden" name="fonturls[<?php echo $font->FontID ?>][WOFF]" value="<?php echo $font->WOFFURL ?>" />
        <input type="hidden" name="fonturls[<?php echo $font->FontID ?>][TTF]" value="<?php echo $font->TTFURL ?>" />
        <input type="hidden" name="fonturls[<?php echo $font->FontID ?>][SVG]" value="<?php echo $font->SVGURL ?>" />

  <?php if(($font->FontTier === '0') || $this->_isACommercialProjectAccount()): ?>

	<input type="button" 
	       value="<?php echo JText::_('WF_ADD_TO_PROJECT') ?>" 
	onclick="addFont('<?php echo $font->FontID ?>')"/>  

  <?php else: ?>

  <a href="https://webfonts.fonts.com/en-us/Account/LogOn" class="commercialLink" target="_blank"><?php echo JText::_('WF_UPGRADE') ?></a>

  <?php endif; //Commercial Check ?>

  <?php else: ?>

		<input type="button" 
	       value="<?php echo JText::_('WF_REMOVE_FROM_PROJECT') ?>" 
	onclick="removeFont('<?php echo $font->FontID ?>')"/>  

	<?php endif; //End font on this project check ?>

      </div>
      <div>

	<?php endif; // end check to see if font is an object ?>

    </td>
    <?php if(($i % 5) === 0): ?>
  </tr>
  <tr>
    <?php endif; //end check to see if row ends?>
    <?php endforeach; ?>
    <?php $this->_plugInRemainingEmptyCells($count); ?>
  </tr>
  <tfoot>
    <tr>
      <td colspan="5">
	<?php echo $this->pagination->getListFooter(); ?>
      </td>
    </tr>
  </tfoot>
</table>

<?php else: ?>

<div class="notice">

  <h4><?php echo JText::_('WF_NOFONTS') ?></h4>

  <p><?php echo JText::_('WF_NOFONTS_MSG') ?></p>

</div>

<?php endif; //end do we have results to list ?>


<?php endif; //End have we selected a project ?>

<input type="hidden" name="limit" id="realLimit" value="25" />
<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="view" value="fontscom" />
<input type="hidden" name="editingFonts" value="1" />
<input type="hidden" name="wfspid" value="<?php echo $this->current['wfspid'] ?>" />
<input type="hidden" name="wfsfid" value="" id="wfsfid" />
<input type="hidden" name="task" value="" id="fontFormTask" />
<?php echo JHTML::_('form.token'); ?>
</form>

</div><!-- end m -->

<?php echo JHtml::_('tabs.end'); ?>

</div>