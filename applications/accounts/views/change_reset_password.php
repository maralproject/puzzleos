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
<div style="display:table;width:100%;height:100%;max-width:480px;margin: auto;">
<div id="loginCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
<div style="font-size:24pt;font-weight:300;margin-bottom:15px;"><?php $language->dump("c_pass")?></div>
<form action="<?php echo __SITEURL?>/users/changepassword" method="post" style="text-align:center;">		
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
	  <input type="hidden" name="datafromresetpassafterverify" value="ok"> 
	  <input type="text" class="form-control" placeholder=""  value="<?php echo Database::read("app_users_list","email","id",$_SESSION['account']['change_pass']['id'])?>" disabled>
	</div><br>
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
	  <input name="passnew" autofocus type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>"  required>
	</div><br>		
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
	  <input name="passver" type="password" class="form-control" placeholder="<?php $language->dump("ver_pass")?>"  required>
	</div><br>	
	<input type="hidden" name="realcpass" value="1"> 
	<button type="submit" class="btn btn-default"><?php $language->dump("c_pass")?></button><br><br>
</form>
</div>
</div>
