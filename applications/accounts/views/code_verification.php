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
<?php ob_start()?>
<style>
.f_center{
	width: 100%;
	height: 70vh;
	display: flex;
	align-items: center;
	justify-content: center;
}
input.big{
	font-size: 40px;
	width: 100%;
	padding: 5px 0;
	border: none;
	background: inherit;
	border-bottom: 2px solid #c0c0c0;
	outline:none!important;
}
input.big:focus{
	border-bottom: 2px solid #4992ff;
}
input.big.wrong{
	border-bottom: 2px solid #f93e3e;
}
</style>
<?php echo Minifier::outCSSMin()?>

<div class="f_center">
	<div>
		<?php if(isset($_SESSION["account"]["confirm_activation"])):?>
		<form action="<?php echo __SITEURL?>/users/activate" method="POST">
		<?php elseif(isset($_SESSION["account"]["change_pass"])):?>
		<form action="<?php echo __SITEURL?>/users/changepassword" method="POST">
		<?php elseif(isset($_SESSION["account"]["confirm_email"])):?>
		<form action="<?php echo __SITEURL?>/users/verifyemail" method="POST">
		<?php endif?>
			<span style="font-size:20pt;font-weight:500;"><?php $language->dump("VER_ACC") ?></span><br>
			<?php if(isset($_SESSION["account"]["confirm_activation"])):?>
			<span style="font-size:16pt;font-weight:400;"><?php echo $_SESSION["account"]["confirm_activation"]["msg"]?></span>
			<?php elseif(isset($_SESSION["account"]["change_pass"])):?>
			<span style="font-size:16pt;font-weight:400;"><?php echo $_SESSION["account"]["change_pass"]["msg"]?></span>
			<?php elseif(isset($_SESSION["account"]["confirm_email"])):?>
			<span style="font-size:16pt;font-weight:400;"><?php echo $_SESSION["account"]["confirm_email"]["msg"]?></span>
			<?php else:?>
			<span style="font-size:16pt;font-weight:400;"><?php $language->dump("VER_CODE_SENT")?></span>
			<?php endif?>
			<div style="height:60px;"></div>
			<span><?php $language->dump("VER_CODE_INPUT")?></span><br>
			<input required autocomplete="off" type="text" class="big <?php if($_SESSION["account"]["confirm_email"]["wrong"] || $_SESSION["account"]["confirm_activation"]["wrong"] || $_SESSION["account"]["change_pass"]["wrong"] ) echo "wrong"?>" maxlength="6" name="code_input_usr" autofocus>
			<br><br>
			<?php if(isset($_SESSION["account"]["confirm_activation"])):?>
			<input type="hidden" name="verification_confirm" value="1">
			<?php elseif(isset($_SESSION["account"]["change_pass"])):?>
			<input type="hidden" name="ch_pass_confirm" value="1">
			<?php elseif(isset($_SESSION["account"]["confirm_email"])):?>
			<input type="hidden" name="ver_emailaddr" value="1">
			<?php endif?>
			<input type="hidden" name="thiscamefromverify" value="1">
			<input type="hidden" name="redir" value="<?php echo ($_GET["redir"]!=""?htmlentities($_GET["redir"]):htmlentities($_POST["redir"]));?>">
			<button type="submit" class="btn btn-primary"><?php $language->dump("CONTINUE")?></button>
		</form>
	</div>
</div>
