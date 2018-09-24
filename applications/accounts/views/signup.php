<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
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
			<span class="input-group-addon"><i class="fa fa-user"></i></span>
			<input maxlength="50" value="<?php echo $_POST["fullname"]?>" required name="fullname" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("name")?>" >
		</div><br>
		<div class="input-group<?php if($GLOBALS["unmsd"]) echo " has-error"?>">
			<span class="input-group-addon"><i class="fa fa-user"></i></span>
			<input oninvalid="setCustomValidity('<?php $language->dump("USERNAME_VALIDITY")?>')" onchange="try{setCustomValidity('')}catch(e){}" maxlength="25" value="<?php echo $_POST["user"]?>" pattern="^[0-9a-z_]*$" required name="user" autocomplete="off" autocapitalize="none"  type="text" class="form-control" placeholder="<?php $language->dump("username")?>" >
		</div><br>
		<?php if(Accounts::$customM_UE || Accounts::getSettings()["f_reg_required1"] == "on"):?>
        <div class="input-group<?php if($GLOBALS["aemsd"]) echo " has-error"?>">
			<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
			<input value="<?php echo $_POST["email"]?>" required name="email" type="email" autocomplete="off" autocapitalize="none" class="form-control" placeholder="<?php $language->dump("email")?>">
		</div><br>
		<?php endif?>
		<?php if(Accounts::$customM_UP || Accounts::getSettings()["f_reg_required2"] == "on"):?>
        <div class="input-group<?php if($GLOBALS["nhpsd"]) echo " has-error"?>">
			<span class="input-group-addon"><i class="fa fa-phone"></i></span>
			<input value="<?php echo $_POST["phone"]?>" required name="phone" pattern="^[0-9\+]{8,15}$" autocomplete="off" autocapitalize="none" class="form-control" placeholder="<?php $language->dump("phone")?>">
		</div><br>
		<?php endif?>
		<div class="input-group">
			<span class="input-group-addon"><i class="fa fa-key"></i></span>
			<input maxlength="50" required name="password" autocomplete="off" autocapitalize="none"  type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" >
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
