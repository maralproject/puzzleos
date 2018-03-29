<?php 
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.3") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

$language = new Language; $language->app = "users";
?>
<div class="container">
<div style="max-width:600px;">
<h3><?php $language->dump("a_set")?></h3><br>
<form action="<?php echo __SITEURL?>/users/profile" method="post">	
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
	  <input name="name" required type="text" autofocus class="form-control" placeholder="<?php $language->dump("name")?>" aria-describedby="sizing-addon1" value="<?php echo $_SESSION['account']['name']?>">
	</div><br>			
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-envelope-o"></i></span>	
	  <input type="hidden" name="ineedtochangesettings" value="pass">
	  <input name="email" required type="email" class="form-control" placeholder="<?php $language->dump("email")?>" aria-describedby="sizing-addon1" value="<?php echo $_SESSION['account']['email']?>">
	</div><br>			
	<div class="input-group">
	  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-phone"></i></span>
	  <input name="phone" pattern="^[0-9\+]{8,13}$" required type="text" class="form-control" placeholder="<?php $language->dump("phone")?>" aria-describedby="sizing-addon1" value="<?php echo $_SESSION['account']['phone']?>">
	</div><br>
	<?php LangManager::dumpForm("lang",$_SESSION['account']['lang'],false,false,true)?><br>		
	<input type="hidden" name="tf" value="1">
	<button type="submit" class="btn btn-default"><?php $language->dump("save_set")?></button>
</form>		
</div>
</div>