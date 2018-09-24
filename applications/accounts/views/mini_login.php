<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
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

<div id="loginCtn" style="max-width:400px;">
	<h3><?php $language->dump("pltya")?></h3>
	<form action="<?php echo __SITEURL?>/users/login" method="post">
		<div class="input-group">
		  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-user"></i></span>
		  <input name="user" autocomplete="username" autocapitalize="none" type="text" class="form-control" placeholder="<?php echo $u_label?>">
		</div><br>
		<div class="input-group">
		  <span class="input-group-addon" id="sizing-addon1"><i class="fa fa-key"></i></span>
		  <input name="pass" autocomplete="off" type="password" class="form-control" placeholder="<?php $language->dump("password")?>" >
		</div><br>	
		<input type="hidden" name="redir" value="<?php echo $useless;?>">
		<input type="hidden" name="trueLogin" value="1">
		<button <?php if($en_recaptcha):?>data-sitekey="<?php echo Accounts::getSettings()["f_recaptcha_site"]?>" data-callback="onposlogin"<?php endif?> title="<?php $language->dump("login")?>" type="submit" class="g-recaptcha btn btn-info"><?php $language->dump("login")?></button>
		<a href="<?php echo __SITEURL?>/users/forgot"><button title="<?php $language->dump("f_pass")?>" type="button" class="btn btn-link"><?php $language->dump("nh")?></button></a>
	</form>		
</div>