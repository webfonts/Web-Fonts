<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

 defined('_JEXEC') or die; 
 ?>
<div id="fontFace">

<form name="adminForm" id="adminForm" method="POST" action="index.php">
  
<?php if(!$this->_doWeHaveFonts()): ?>

<p><?php echo JText::_('NO_FONTS_SELECTED'); ?></p>
<p><strong><a href="index.php?option=com_webfonts&view=vendors"><?php echo JText::_('SELECT_FONTS'); ?></a></strong></p>

<?php else: ?>

<fieldset class="m" id="filters">
  <div class="fltlft">
    <label class="filter-search-lbl" for="selector"><?php echo JText::_('SELECTOR') ?>:&nbsp;&nbsp;</label>
    <input type="text" name="selector" id="selector"
	   value="" 
    title="<?php echo JText::_('SELECTOR_DESC') ?> "/>
    <input type="button" class="btn" id="addSelector" value="<?php echo JText::_('ADD') ?>" />
  </div>
</fieldset>

<table class="adminlist">
  <thead>
    <th>
      <?php echo JText::_('SELECTOR') ?>
    </th>
    <th>
      <?php echo JText::_('FONT') ?>
    </th>
    <th>
      <?php echo JText::_('PREVIEW') ?>
    </th>
    <th>
      <?php echo JText::_('FALLBACK') ?>
    </th>
    <th>
      <?php echo JText::_('REMOVE') ?>
    </th>
  </thead>
  <tbody>

  <?php foreach($this->selectors AS $selector): ?>
<tr>
  <td>
   <?php echo $selector->selector; ?>
  </td>
  <td>
    <?php echo $this->_listOptions($selector->fontId, $selector->id) ?>
  </td>
  <td class="fontPreview">
    <?php echo $this->_getFontPreview($selector->fontId, $selector->vendor); ?>
  </td>
  <td>
    <input type="text" class="fallBack" name="fallBack[<?php echo $selector->id ?>]" title="<?php echo JText::_('FALLBACK_DESC') ?>" value="<?php echo $selector->fallBack ?>"/>
  </td>
  <td class="center">
    <input type="button" value="<?php echo JText::_('REMOVE') ?>" onclick="removeSelector('<?php echo $selector->id ?>')" />
  </td>
</tr>
<?php endforeach; ?>
  </tbody>
</table>

<div class="beverlyClearly">&nbsp;</div>

<?php if(!empty($this->selectors)): ?>

<input type="submit" value="<?php echo JText::_('SAVE_CHANGES') ?>" id="saveChanges" />

<?php endif; //Selectors arent empty ?>

<?php endif; // Have we imported fonts ?>

<input type="hidden" name="view" value="stylesheet" />
<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="" id="task" />
<input type="hidden" name="sid" id="sid" value="" />
<input type="hidden" name="layout" value="selectors" />

</form>

</div> <!-- end #fontFace -->