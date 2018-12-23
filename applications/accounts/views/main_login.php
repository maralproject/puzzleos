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

if(Accounts::$customM_UE && !Accounts::$customM_UP){
	$u_label = $language->get("uom");
}elseif(!Accounts::$customM_UE && Accounts::$customM_UP){
	$u_label = $language->get("uop");
}else{
	$u_label = $language->get("username");
}

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
<div id="loginCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
	<div style="font-weight:300;margin-bottom:20px;">
	<span style="font-size:20pt;font-weight:500;"><?php $language->dump("login")?></span>
	</div>
	<form onsubmit="$(this).find('button').prop('disabled',true);$(this).find('input').trigger('blur')" action="<?php echo __SITEURL?>/users/login" method="post" style="text-align:center;">
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa fa-user"></i></span>
			</div>
			<input maxlength="50" required name="user" autocomplete="username" autocapitalize="none" value="<?php echo $_POST["user"]?>" <?php if($_POST["user"] == ""):?>autofocus<?php endif;?> type="text" class="form-control <?php if($GLOBALS["ULFailed"]) echo "is-invalid"?>" placeholder="<?php echo $u_label?>" >
		</div><br>
		<div class="input-group">
			<div class="input-group-prepend">
				<span class="input-group-text"><i class="fa fa-key"></i></span>
			</div>
			<input maxlength="50" required name="pass" autocomplete="off" type="password" class="form-control <?php if($GLOBALS["ULFailed"]) echo "is-invalid"?>" <?php if($_POST["user"] != ""):?>autofocus<?php endif;?> placeholder="<?php $language->dump("password")?>">
		</div><br>
		<input type="hidden" name="redir" value="<?php echo ($_GET["redir"]!=""?htmlentities($_GET["redir"]):htmlentities($_POST["redir"]));?>">
		<input type="hidden" name="trueLogin" value="1">
		<button <?php if($en_recaptcha):?>data-sitekey="<?php echo Accounts::getSettings()["f_recaptcha_site"]?>" data-callback="onposlogin"<?php endif?> title="<?php $language->dump("login")?>" type="submit" class="g-recaptcha btn btn-primary"><?php $language->dump("login")?></button>
	</form><br>

	<div class="helpform" style="text-align:center;">
		<a href="<?php echo __SITEURL?>/users/forgot"><?php $language->dump("F_PASS")?></a>
		<?php if(Accounts::getSettings()["f_en_registration"] == "on"):?>
		<br><?php $language->dump("NOT_OWN_ACC_CONFIRM")?> <b><a href="<?php echo __SITEURL?>/users/signup"><?php $language->dump("SIGNUP")?></a></b>
		<?php endif?>
	</div>
</div>
</div>

<?php if(isset($_SESSION['account']['signup_emailsent'])):?>
<div class="modal fade" id="signup_g">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
				<h5 class="modal-title"><?php $language->dump("oms")?>,</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="text-align:center;max-width:400px;margin:auto;line-height:30px;">
				<i class="fa fa-envelope-o fa-3x"></i><br><br>
                <?php $language->dump("CONFIRM_EMAIL")?><br>
				<?php $language->dump("CHECK_SPAM_EMAIL")?>
            </div>
            <div class="modal-footer" style="box-shadow:none">
                <button type="button" class="btn btn-info" data-dismiss="modal"><?php $language->dump("CLOSE")?></button>
            </div>

        </div>
    </div>
</div>
<script>$(document).ready(function(){$("#signup_g").modal('show');})</script>
<?php unset($_SESSION['account']['signup_emailsent']);endif;?>
