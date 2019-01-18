<?php 
	$s = Accounts::getSettings();
	$language = new Language;
?>

<style>
.s_field{
	max-width:800px;
	margin-top:5px;
}
.rf, .grf{
	display:none;
}
</style>

<form id="cf" action="<?php echo __SITEURL?>/users/update" method="post">
<h3><?php $language->dump("settings")?></h3>

<h4><?php $language->dump("G-RECAPTCHA")?></h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_recaptcha" onchange="$('.rf').toggle($(this).prop('checked'))" <?php if($s["f_en_recaptcha"] == "on") echo "checked"?>> <?php $language->dump("G-RECAPTCHA-WARN");?></label>
	</div>
</div>
<div class="row s_field rf">
	<div class="col-md-4"><?php $language->dump("SITEKEY")?></div>
	<div class="col-md-8"><input type="text" name="f_recaptcha_site" class="form-control" value="<?php echo $s["f_recaptcha_site"]?>"></div>
</div>
<div class="row s_field rf">
	<div class="col-md-4"><?php $language->dump("SECRETKEY")?></div>
	<div class="col-md-8"><input type="text" name="f_recaptcha_secret" class="form-control" value="<?php echo $s["f_recaptcha_secret"]?>"></div>
</div>

<br><h4><?php $language->dump("REGISTRATION")?></h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_registration" onchange="$('.grf').toggle($(this).prop('checked'))" <?php if($s["f_en_registration"] == "on") echo "checked"?>> <?php $language->dump("LET_GUEST_REG")?></label>
	</div>
</div>
<div class="row s_field grf">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_reg_activate" <?php if($s["f_reg_activate"] == "on") echo "checked"?>> <?php $language->dump("REQ_ACTIVATE_ACC")?></label>
	</div>
</div>
<div class="row s_field grf">
	<div class="col-md-4"><?php $language->dump("REG_NEW_USER_AS")?></div>
	<div class="col-md-8"><?php $GLOBALS["app"]["managing"]->loadView("group_button",["f_reg_group",($s["f_reg_group"] == ""?Accounts::getRootGroupId(USER_AUTH_REGISTERED):$s["f_reg_group"]),USER_AUTH_REGISTERED])?></div>
</div>

<br><h4><?php $language->dump("PROFILE_REQ")?></h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_reg_required1" <?php if($s["f_reg_required1"] == "on") echo "checked"?>> <?php $language->dump("EMAIL_REQ")?></label><br>
		<label><input type="checkbox" name="f_reg_required2" <?php if($s["f_reg_required2"] == "on") echo "checked"?>> <?php $language->dump("PHONE_REQ")?></label><br>
		<label><input type="checkbox" name="f_profile_language" <?php if($s["f_profile_language"] == "on") echo "checked"?>> <?php $language->dump("AUTPL")?></label>
	</div>
</div>

<br><h4><?php $language->dump("REMEMEMBER_FEAT")?></h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_remember_me" <?php if($s["f_en_remember_me"] == "on") echo "checked"?>> <?php $language->dump("ENABLE")?></label>
	</div>
</div>

<br><h4><?php $language->dump("MISC")?></h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_share_session" <?php if($s["f_share_session"] == "on") echo "checked"?>> <?php $language->dump("LOGIN_SESSION_SHARE")?></label>
	</div>
</div>

<br>
<button type="submit" class="btn btn-success"><?php $language->dump("SAVE_SET")?></button>
</form>
<div style="height:15vh"></div>
<script>$("#cf input").change()</script>
