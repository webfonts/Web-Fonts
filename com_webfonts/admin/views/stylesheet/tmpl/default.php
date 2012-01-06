<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

 defined('_JEXEC') or die; 
 ?>
<div id="fontFace">

<form name="adminForm" id="adminForm" method="POST" action="index.php">
  
<?php if(!$this->_doWeHaveFonts()): ?>

<div class="notice">

<h3 class="important"><?php echo JText::_('NO_FONTS_SELECTED'); ?></h3>
<p><?php echo JText::_('NO_FONTS_EXPLAIN') ?></p>
<p><a href="index.php?option=com_webfonts&view=vendors"><?php echo JText::_('SELECT_FONTS'); ?></a></p>

</div>


<?php else: ?>

<?php foreach($this->fonts as $font): ?>

<div class="fontSelectList">

<fieldset class="m">

  <legend>
    <?php echo $font->getName()?> (<?php echo $font->getVendor()?>)
  </legend>

  <a href="javascript:removeFont(<?php echo "'" . $font->getId() . "','" . $font->getHandler() ."'" ?>);" class="remove">
    <img src="<?php echo JURI::root() ?>media/com_webfonts/images/exit.png" alt="<?php echo JText::_('DELETE') ?>" width="25" height="24" class="hoverImage" />
  </a>

  <div class="fontPreview">
    <?php echo $font->getPreview() ?>
  </div>

  <div class="beverlyClearly ramona">&nbsp;</div>

  <div class="fltlft">
    <label class="filter-search-lbl" for="fallBack<?php echo $font->getId() ?>"><?php echo JText::_('FALLBACK') ?>:&nbsp;&nbsp;</label>
    <input type="text" class="fallBack" font="<?php echo $font->getId() ?>" handler="<?php echo $font->getHandler() ?>"
	   value="" 
    title="<?php echo JText::_('FALLBACK_DESC') ?> " 
    id="fallBack<?php echo $font->getId() ?>" />

    <input type="button" class="btn addFallBack" value="<?php echo JText::_('SET') ?>"
	   font="<?php echo $font->getId() ?>" />

    <label class="filter-search-lbl" for="<?php echo $font->getId() ?>"><?php echo JText::_('SELECTOR') ?>:&nbsp;&nbsp;</label>
    <input type="text" class="addSelectors" font="<?php echo $font->getId() ?>" handler="<?php echo $font->getHandler() ?>"
	   value="" 
    title="<?php echo JText::_('SELECTOR_DESC') ?> " id="<?php echo $font->getId() ?>" />
    <input type="button" class="btn addSelector" value="<?php echo JText::_('ADD') ?>"
	   font="<?php echo $font->getId() ?>" />
  </div><!-- end fltlft -->
  <div class="clr">&nbsp;</div>
</fieldset>

  <table class="adminlist">
    <tr>
      <thead>
	<th><?php echo JText::_('SELECTOR') ?></th>
	<th>
	  <?php echo JText::_('FALLBACK') ?>
	</th>
	<th class="fontBasedRemove"><?php echo JText::_('REMOVE') ?></th>
      </thead>
    </tr>
    <tbody>
      <?php $selectors = $this->_organizeMySelectors($font->getId()) ?>
      <?php foreach($selectors as $selector): ?>
      <tr>
	<td>
	  <?php echo $selector->selector ?>
	</td>
	<td>
	  <?php echo $selector->fallBack ?>
	</td>
	<td class="center">
	  <?php if($selector->id): ?>
	  <input type="button" value="<?php echo JText::_('REMOVE') ?>" onclick="removeSelector('<?php echo $selector->id ?>')" />
	  <?php endif; ?>
	</td>
      </tr>
	<?php endforeach; ?>
    </tbody>
  </table>

</div>


<?php endforeach; //fonts ?>

<?php endif; // Have we imported fonts ?>

<span class="hidden" id="confirmDelete">
<?php echo JText::_('WF_DELETE_FONT'); ?>
</span>

<input type="hidden" name="view" value="stylesheet" />
<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="" id="task" />
<input type="hidden" name="sid" id="sid" value="" />
<input type="hidden" name="selector" value="" id="selector" />
<input type="hidden" name="fontId" value="" id="fontId" />
<input type="hidden" name="vendor" value="" id="vendor" />
<input type="hidden" name="fallBack" value="" id="fallBack" />

</form>

</div> <!-- end #fontFace -->
