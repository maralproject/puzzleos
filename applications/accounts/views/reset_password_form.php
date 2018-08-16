<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.2
 */

$language = new Language; $language->app = "users";
?>
<div style="display:table;width:100%;height:100%;max-width:480px;margin: auto;">
<div id="loginCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
	<div style="font-weight:300;margin-bottom:20px;" class="ellipsis">
	<span style="font-size:20pt;font-weight:500;"><button onclick="history.back()" class="btn btn-link" style="margin-right:15px;padding: 6px;"><i  style="font-size:20pt;font-weight:500;" class="fa fa-chevron-left"></i></button><?php $language->dump("nh")?></div>
	<form onsubmit="$(this).find('button').prop('disabled',true)" method="post" style="text-align:center;">
		<div class="input-group">
		  <input type="hidden" name="datafromforgotout" value="1">
		  <span class="input-group-addon"><i class="fa fa-user"></i></span>
		  <input autocomplete="off" name="user" autofocus type="text" class="form-control" placeholder="Masukkan username Anda" required>
		</div><br>
		<input type="hidden" name="realforgotpaswd" value="1">
		<button type="submit" class="btn btn-default"><?php $language->dump("rmp")?></button>
	</form><br><br>

	<div class="helpform" style="text-align:center;">
	<?php $language->dump("OWN_ACC_CONFIRM")?> <b><a href="<?php echo __SITEURL?>/users"><?php $language->dump("LOGIN")?></a></b>
	</div>
</div>
</div>
