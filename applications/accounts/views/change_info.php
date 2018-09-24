<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$language = new Language;
$s = Accounts::getSettings();
?>
<div class="container">
<div style="max-width:600px;">
<h2><?php $language->dump("a_set")?></h2><br>
<form action="<?php echo __SITEURL?>/users/profile" method="post">	
	<div class="input-group">
		<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
		<input type="hidden" name="ineedtochangesettings" value="pass">
		<input maxlength="50" name="name" required type="text" autofocus class="form-control" placeholder="<?php $language->dump("name")?>"  value="<?php echo $_SESSION['account']['name']?>">
	</div><br>
	<?php if(Accounts::$customM_UE || Accounts::getSettings()["f_reg_required1"] == "on"):?>
	<div class="input-group">
		<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-envelope-o"></i></span>	
		<input name="email" <?php if(Accounts::$customM_UE||$s["f_reg_required1"]=="on") echo "required"?> type="email" class="form-control" placeholder="<?php $language->dump("email")?>"  value="<?php echo $_SESSION['account']['email']?>">
	</div><br>
	<?php endif?>
	<?php if(Accounts::$customM_UP || Accounts::getSettings()["f_reg_required2"] == "on"):?>
	<div class="input-group">
		<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-phone"></i></span>
		<input name="phone" pattern="^[0-9\+]{8,15}$" <?php if(Accounts::$customM_UP||$s["f_reg_required2"]=="on") echo "required"?> type="text" class="form-control" placeholder="<?php $language->dump("phone")?>"  value="<?php echo $_SESSION['account']['phone']?>">
	</div><br>
	<?php endif?>
	<?php LangManager::dumpForm("lang",$_SESSION['account']['lang'],false,false,true)?><br>		
	<input type="hidden" name="tf" value="1">
	<button type="submit" class="btn btn-default"><?php $language->dump("save_set")?></button>
</form>		
</div>
</div>