<?php $s = Accounts::getSettings()?>

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
<h3>Pengaturan</h3>

<h4>Google Rechaptcha</h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_recaptcha" onchange="$('.rf').toggle($(this).prop('checked'))" <?php if($s["f_en_recaptcha"] == "on") echo "checked"?>> Gunakan Recaptcha</label>
	</div>
</div>
<div class="row s_field rf">
	<div class="col-md-4">Site key</div>
	<div class="col-md-8"><input type="text" name="f_recaptcha_site" class="form-control" value="<?php echo $s["f_recaptcha_site"]?>"></div>
</div>
<div class="row s_field rf">
	<div class="col-md-4">Secret key</div>
	<div class="col-md-8"><input type="text" name="f_recaptcha_secret" class="form-control" value="<?php echo $s["f_recaptcha_secret"]?>"></div>
</div>

<br><h4>Pendaftaran</h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_registration" onchange="$('.grf').toggle($(this).prop('checked'))" <?php if($s["f_en_registration"] == "on") echo "checked"?>> Izinkan tamu untuk mendaftar</label>
	</div>
</div>
<div class="row s_field grf">
	<div class="col-md-4">Daftarkan pengguna baru sebagai</div>
	<div class="col-md-8"><?php echo Accounts::getGroupPromptButton("f_reg_group",($s["f_reg_group"] == ""?Accounts::getRootGroupId(USER_AUTH_REGISTERED):$s["f_reg_group"]),USER_AUTH_REGISTERED)?></div>
</div>

<br><h4>Kelengkapan Profil</h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_reg_required1" <?php if($s["f_reg_required1"] == "on") echo "checked"?>> Email wajib diisi</label><br>
		<label><input type="checkbox" name="f_reg_required2" <?php if($s["f_reg_required2"] == "on") echo "checked"?>> Telepon wajib diisi</label>
	</div>
</div>

<br><h4>Fitur Ingat saya</h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_en_remember_me" <?php if($s["f_en_remember_me"] == "on") echo "checked"?>> Hidupkan</label>
	</div>
</div>

<br><h4>Lainnya</h4>
<div class="row s_field">
	<div class="col-md-12">
		<label><input type="checkbox" name="f_share_session" <?php if($s["f_share_session"] == "on") echo "checked"?>> Bagikan sesi login dengan subdomain</label>
	</div>
</div>

<br>
<button type="submit" class="btn btn-success">Simpan</button>
</form>
<div style="height:15vh"></div>
<script>$("#cf input").change()</script>