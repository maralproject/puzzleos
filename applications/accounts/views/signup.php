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
 * @software     Release: 1.1.1
 */

$language = new Language; $language->app = "users";
if(!isset($_GET["redir"])) $_GET["redir"] = "";

$en_recaptcha = Accounts::getSettings()["f_en_recaptcha"] == "on";
?>
<?php if($en_recaptcha):?>
<script>
	function onposlogin(token){
		$("#loginCtn form").submit();
	}
</script>
<script src='https://www.google.com/recaptcha/api.js' async></script>
<?php endif?>

<div style="display:table;width:100%;height:100%;max-width:480px;margin: auto;">
<div id="loginCtn" class="signupCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
	<div style="font-weight:300;margin-bottom:20px;">
	<span style="font-size:20pt;font-weight:500;"><?php $language->dump("signup")?></span>
	</div>
	<form onsubmit="$(this).find('button').prop('disabled',true);$(this).find('input').trigger('blur')" action="<?php echo __SITEURL?>/users/signup" method="post" style="text-align:center;">
        <div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
			<input value="<?php echo $_POST["fullname"]?>" required name="fullname" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("name")?>" >
		</div><br>
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
			<input value="<?php echo $_POST["user"]?>" required name="user" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("username")?>" >
		</div><br>
		<?php if(Accounts::getSettings()["f_reg_activate"] == "on" || Accounts::getSettings()["f_reg_required1"] == "on"):?>
        <div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-envelope"></i></span>
			<input value="<?php echo $_POST["email"]?>" required name="email" type="email" autocomplete="off" autocapitalize="none" class="form-control" placeholder="<?php $language->dump("email")?>">
		</div><br>
		<?php endif?>
		<div class="input-group">
			<span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
			<input required name="password" autocomplete="off" autocapitalize="none"  type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" >
		</div><br>
		<input type="hidden" name="redir" value="<?php echo ($_GET["redir"]!=""?htmlentities($_GET["redir"]):htmlentities($_POST["redir"]));?>">
		<input type="hidden" name="trueLogin" value="1">
		<button <?php if($en_recaptcha):?>data-sitekey="<?php echo Accounts::getSettings()["f_recaptcha_site"]?>" data-callback="onposlogin"<?php endif?> title="<?php $language->dump("signup")?>" type="submit" class="g-recaptcha btn btn-info"><?php $language->dump("signup")?></button>
	</form><br><br>

	<div class="helpform" style="text-align:center;">
	<?php $language->dump("OWN_ACC_CONFIRM")?> <b><a href="<?php echo __SITEURL?>/users"><?php $language->dump("LOGIN")?></a></b>
	</div>
</div>
</div>
