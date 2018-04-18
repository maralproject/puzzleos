<?php 
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.1.1") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.1
 */

$language = new Language; $language->app = "users";
?>
<div class="container">
<div style="max-width:600px;">
<h2><?php $language->dump("c_pass")?></h2><br>
<form action="<?php echo __SITEURL?>/users/changepassword" method="post">			
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
	  <input name="passold" required autofocus type="password" class="form-control" placeholder="<?php $language->dump("old_pass")?>" >
	</div><br>		
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
	  <input type="hidden" name="datafromresetpassafterverify" value="ok">
	  <input name="passnew" required type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" >
	</div><br>		
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
	  <input name="passver" required type="password" class="form-control" placeholder="<?php $language->dump("ver_pass")?>" >
	</div><br>		
	<input type="hidden" name="realcpass" value="1">
	<button type="submit" class="btn btn-default"><?php $language->dump("c_pass")?></button>
</form>		
</div>
</div>