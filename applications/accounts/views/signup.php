<?php 
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
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
<div id="loginCtn" class="signupCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
	<div style="font-weight:300;margin-bottom:20px;">
	<span style="font-size:20pt;font-weight:500;"><?php $language->dump("signup")?></span>
	</div>
	<form onsubmit="$(this).find('button').prop('disabled',true);$(this).find('input').trigger('blur')" action="<?php echo __SITEURL?>/users/signup" method="post" style="text-align:center;">
        <div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
			<input required name="fullname" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("name")?>" aria-describedby="sizing-addon1">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
			<input required name="user" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("username")?>" aria-describedby="sizing-addon1">
		</div><br>
        <div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-envelope"></i></span>
			<input required name="email" autocomplete="off"autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("email")?>" aria-describedby="sizing-addon1">
		</div><br>
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
			<input required name="password" autocomplete="off" autocapitalize="none"  type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" aria-describedby="sizing-addon1">
		</div><br>
		<input type="hidden" name="redir" value="<?php echo ($_GET["redir"]!=""?htmlentities($_GET["redir"]):htmlentities($_POST["redir"]));?>">
		<input type="hidden" name="trueLogin" value="1">
		<button <?php if($en_recaptcha):?>data-sitekey="<?php echo Accounts::getSettings()["f_recaptcha_site"]?>" data-callback="onposlogin"<?php endif?> title="<?php $language->dump("signup")?>" type="submit" class="g-recaptcha btn btn-info"><?php $language->dump("signup")?></button>
	</form><br><br>
	
	<div class="helpform" style="text-align:center;">
	Sudah punya akun? <b><a href="<?php echo __SITEURL?>/users">Masuk</a></b>
	</div>
</div>
</div>

