<?php 
/*-----------------------------------------
  License: GPL v 3.0 or later
-----------------------------------------*/

 defined('_JEXEC') or die; 
 ?>
<form name="adminForm" id="adminForm" action="index.php" method="POST">

<?php if(empty($this->vendors)): ?>

<p><?php echo JText::_('NO_VENDORS_IN_DB'); ?></p>

<?php else: ?>
<table class="adminlist">
  <thead>
    <tr>
      <th>Vendor</th>
    </tr>
  </thead>
  <tbody>
<?php foreach($this->vendors as $vendor):
   $view = $this->_determineView($vendor);
 ?>
    <tr>
      <td>
         <?php echo JHtml::link("index.php?option=com_webfonts&view={$view}", $vendor->name); ?>
      </td>
    </tr>
<?php endforeach;?>
  </tbody>
</table>

<?php endif; ?>

<input type="hidden" name="option" value="com_webfonts" />
<input type="hidden" name="task" value="" />

</form>
