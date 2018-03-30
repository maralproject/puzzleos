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
if(!isset($_GET["redir"])) $_GET["redir"] = "";
?>
<div id="loginCtn" style="max-width:400px;">
	<h3><?php $language->dump("pltya")?></h3>
	<form action="<?php echo __SITEURL?>/users/login" method="post">
		<div class="input-group">
		  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
		  <input name="user" autocomplete="username" autocapitalize="none" type="text" class="form-control" placeholder="<?php $language->dump("username")?>" aria-describedby="sizing-addon1">
		</div><br>
		<div class="input-group">
		  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
		  <input name="pass" autocomplete="off" type="password" class="form-control" placeholder="<?php $language->dump("password")?>" aria-describedby="sizing-addon1">
		</div><br>	
		<input type="hidden" name="redir" value="<?php echo $useless;?>">
		<input type="hidden" name="trueLogin" value="1">
		<button title="<?php $language->dump("login")?>" type="submit" class="btn btn-info"><?php $language->dump("login")?></button>
		<a href="<?php echo __SITEURL?>/users/forgot"><button title="<?php $language->dump("f_pass")?>" type="button" class="btn btn-link"><?php $language->dump("nh")?></button></a>
	</form>		
</div>