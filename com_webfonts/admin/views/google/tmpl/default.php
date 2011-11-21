<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

defined('_JEXEC') or die;

?>
<div id="webfonts" class="google">

<form name="adminForm" method="GET" action="index.php" id="adminForm">

<fieldset class="t">
  <input type="button" value="<?php echo JText::_('WF_RESET') ?>" id="resetForm" title="<?php echo JText::_('WF_RESET_DESCRIPTION') ?>" />
</fieldset>

<fieldset class="m" id="filters">
  <div class="filter-search fltlft">
    <label class="filter-search-lbl" for="keyword"><?php echo JText::_('WF_KEYWORD') ?>:&nbsp;&nbsp;</label>
    <input type="text" name="keyword" id="keyword" 
	   value="<?php echo JRequest::getVar('keyword', '', 'get'); ?>" 
    title="<?php echo JText::_('WF_KEYWORD_TITLE') ?>"/>
    <button type="button" class="btn" id="keywordClick"><?php echo JText::_('SEARCH') ?></button>
    <button type="button" onclick="document.id('keyword').value='';this.form.submit();">Clear</button>
  </div>
  <div class="filter-search fltrt">
      <?php echo $this->_getFilters() ?>
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

    <?php $selected = ($font->inUse === '1') ? ' selected' : ''; ?>

    <td class="fontTile<?php echo $selected ?>">

      <span style="<?php echo $this->_determineStyle($font) ?>" class="fontSample">

      <?php echo new WebfontsLanguageSample(JRequest::getVar('subset', 'latin', 'get')); ?>

      </span>
      <br />
      <br />

      <span class="fontLabel"><?php echo $this->_determineLabel($font) ?></span><br />

      <span class="fontLabelLanguage">(<?php echo $this->_matchFontSubset($font->id) ?>)</span>

      <div class="hidden">

	<div id="tileMeta<?php echo $i ?>">

	<h4><?php echo $font->family ?></h4>

	<p class="longExample" style="<?php echo $this->_determineStyle($font) ?>">
	<?php echo new WebfontsLanguageSampleLong(JRequest::getVar('subset', 'latin', 'get')); ?>
        </p>

	<p class="longExample invert" style="<?php echo $this->_determineStyle($font) ?>">
	<?php echo new WebfontsLanguageSampleLong(JRequest::getVar('subset', 'latin', 'get')); ?>
        </p>

	<?php if($font->inUse === '0'): ?>
	<input type="button" 
	       value="<?php echo JText::_('WF_ADD_TO_PROJECT') ?>" 
	onclick="addFont('<?php echo $font->mutantId ?>')"/>  

	<?php else: ?>

		<input type="button" 
	       value="<?php echo JText::_('WF_REMOVE_FROM_PROJECT') ?>" 
	onclick="removeFont('<?php echo $font->mutantId ?>')"/>  

	<?php endif; //End font on this project check ?>

      </div>
      <div>

	<?php endif; // end check to see if font is an object ?>

    </td>
    <?php if(($i % 5) === 0): ?>
  </tr>
  <tr>
    <?php endif; //end check to see if row ends ?>
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


<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="view" value="google" />
<input type="hidden" name="fid" value="" id="fid"/>
<input type="hidden" name="task" value="" id="fontFormTask" />
<?php echo JHTML::_('form.token'); ?>
</form>

</div><!-- end #m -->

</div><!-- end #google -->
