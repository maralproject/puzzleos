<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.1.3") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.admin
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */

$l = new Language;
?>
<style>
	@media(min-width:1020px){
		.label-control{
			text-align:right;
		}
	}
</style>
<div class="row">
	<div class="col-md-6">	
		<legend style="margin-bottom:0;"><?php $l->dump("ERROR_REPORTING")?></legend>
		<fieldset style="margin-left:25px;">
		<label class="radio">
		  <input type="radio" data-toggle="tooltip" title="<?php $l->dump("INFO1")?>" name="ep" value="<?php echo E_ALL?>" <?php if(error_reporting() == (E_ALL)) echo("checked");?>>
		  <?php $l->dump("DEBUG_MODE")?>
		</label>
		<label class="radio">
		  <input type="radio" data-toggle="tooltip" title="<?php $l->dump("INFO2")?>" name="ep" value="<?php echo (E_ERROR | E_PARSE | E_COMPILE_ERROR)?>" <?php if(error_reporting() == (E_ERROR | E_PARSE | E_COMPILE_ERROR)) echo("checked");?>>
		  <?php $l->dump("NORMAL_REPORT")?>
		</label>
		<label class="radio">
		  <input type="radio" data-toggle="tooltip" title="<?php $l->dump("INFO3")?>" name="ep" value="<?php echo (0)?>"  <?php if(error_reporting() == 0) echo("checked");?>>
		  <?php $l->dump("NONE")?>
		</label>
		</fieldset>
	</div>
	<div class="col-md-6">	
		<legend style="margin-bottom:10px;"><?php $l->dump("mdomain")?></legend>
		<fieldset style="margin-left:25px;margin:15px 0;">
			<label class="checkbox-inline"><input type="checkbox" id="allow_mdomain" name="allow_mdomain" <?php if(ConfigurationGlobal::$use_multidomain) echo "checked";?>/>
				<?php $l->dump("mdomain_ask")?>
			</label>
		</fieldset>
		<?php if(ConfigurationGlobal::$use_multidomain):?>
		<div class="row" style="max-width:600px;">
			<div class="col-md-3 label-control"><?php $l->dump("rnd")?>:</div>
			<div class="col-md-9">
				<div class="input-group">
				  <input type="text" class="form-control" id="newDomain" placeholder="subdomain.domain.com">
				  <span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="let e=jQuery.Event('keypress');e.keyCode=13;$('#newDomain').trigger(e);"><?php $l->dump("save")?></button>
				  </span>
				</div>
			</div>
		</div><br>
		<div><span class="label label-success"><?php $l->dump("MANAGING")?></span> <?php echo PuzzleOSGlobal::$domain_zone?></div>
		<br><br>
		<?php endif;?>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<legend style="margin-bottom:10px;"><?php $l->dump("DATABASE")?> <i data-toggle="tooltip" title="<?php $l->dump("INFO4")?>" class="fa fa-exclamation-triangle"></i></legend>
		<fieldset style="max-width:600px;">
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-user"></i></span>
			  <input type="text" class="form-control" placeholder="<?php $l->dump("USERNAME")?>" name="dbuser" value="<?php echo ConfigurationDB::$username?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-key"></i></span>
			  <input type="password" class="form-control" name="dbpass" placeholder="<?php $l->dump("no_password")?>" value="<?php echo ConfigurationDB::$password?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-server"></i></span>
			  <input type="text" class="form-control" name="dbhost" placeholder="<?php $l->dump("HOST")?>" value="<?php echo ConfigurationDB::$host ?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-database"></i></span>
			  <input type="text" class="form-control" name="dbdb" placeholder="<?php $l->dump("DATABASE")?>" value="<?php echo ConfigurationDB::$database_name ?>">
		</div>
		</fieldset><br>
	</div>
	<div class="col-md-6">
		<legend style="margin-bottom:10px;"><?php $l->dump("ADDITIONAL_OPTIONS")?></legend>
		<fieldset style="max-width:600px;">
		<?php echo LangManager::getForm("deflang",__SITELANG,true,true)?>
		<div class="input-group" style="margin-top:10px;">
			  <span class="input-group-addon"><i class="fa fa-clock-o"></i></span>
			  <?php Timezone::dumpDropdownList("timezone",__TIMEZONE);?>
		</div>
		<div class="input-group" style="margin-top:10px;">
			  <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
			  <input type="text" class="form-control" name="sitename" placeholder="<?php $l->dump("SITE_NAME")?>" value="<?php echo __SITENAME?>">
		</div>
		<div class="input-group" style="margin-top:10px;">
			  <span class="input-group-addon"><i class="fa fa-copyright"></i></span>
			  <input type="text" class="form-control" name="copytext" placeholder="<?php $l->dump("COPYRIGHT")?>" value="<?php echo ConfigurationGlobal::$copyright?>">
		</div>
		<div class="input-group" style="margin-top:10px;">
			  <span class="input-group-addon"><i class="fa fa-info"></i></span>
			  <input type="text" class="form-control" name="metadesc" placeholder="<?php $l->dump("META_DESCRIPTION")?>" value="<?php echo ConfigurationGlobal::$meta_description?>">
		</div>
		</fieldset><br>
	</div>
</div>
<div class="row">
<div class="col-md-6" style="margin-bottom:10px;">
<legend style="margin-bottom:10px;"><?php $l->dump("MAILER_OPTIONS")?></legend>
<fieldset style="max-width:600px;">
<div class="input-group" style="padding:5px;">
	  <span class="input-group-addon"><i class="fa fa-envelope-o"></i></span>
	  <input type="text" class="form-control" name="mailfrom" placeholder="<?php $l->dump("EMAIL_FROM")?>" value="<?php echo ConfigurationMailer::$From?>">
</div>
<div class="input-group" style="padding:5px;">
	  <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
	  <input type="text" class="form-control" name="mailname" placeholder="<?php $l->dump("EMAIL_NAME")?>" value="<?php echo ConfigurationMailer::$Sender?>">
</div>
<div class="input-group" style="padding:5px;">
	  <button type="button" onclick="testEmail();" class="btn btn-sm btn-info"><?php $l->dump("email_test")?></button>
</div>
</fieldset>
</div>
<div class="col-md-6">
<legend style="margin-bottom:10px;"><?php $l->dump("smtp_option")?></legend>
<label class="checkbox-inline"><input type="checkbox" onclick="$('#smtp_details').slideToggle(200);" id="use_smtp" name="use_smtp" <?php if(!ConfigurationMailer::$UsePHP) echo "checked";?>/><?php $l->dump("USE_SMTP_EN")?></label>
<div id="smtp_details" style="<?php if(ConfigurationMailer::$UsePHP) echo "display:none;";?>">
<div class="input-group" style="padding:5px 0;">
	<span class="input-group-addon">
		<input value="none" type="radio" name="smtp_enc" id="radio0" <?php if(ConfigurationMailer::$smtp_encryption == "none") echo "checked";?>> <label for="radio0"><?php $l->dump("SMTP_NO_ENC")?></label>
    </span>
	<span class="input-group-addon">
		<input type="radio" value="tls" name="smtp_enc" id="radio1" <?php if(ConfigurationMailer::$smtp_encryption == "tls") echo "checked";?>> <label for="radio1">TLS</label>
    </span>
	<span class="input-group-addon">
		<input type="radio" value="ssl" name="smtp_enc" id="radio2" <?php if(ConfigurationMailer::$smtp_encryption == "ssl") echo "checked";?>> <label for="radio2">SSL</label>
    </span>
</div><br>
<label class="checkbox-inline"><input type="checkbox" onclick="$('#smtp_auth_details').slideToggle(200);" id="use_smtp_auth" name="smtp_auth" <?php if(ConfigurationMailer::$smtp_use_auth) echo "checked";?>/><?php $l->dump("use_smtp_auth")?></label>
<div id="smtp_auth_details" style="<?php if(!ConfigurationMailer::$smtp_use_auth) echo "display:none;";?>">
<div class="input-group" style="padding:5px 0;">
	  <span class="input-group-addon"><i class="fa fa-user"></i></span>
	  <input type="text" class="form-control" placeholder="<?php $l->dump("username")?>" name="smtp_user" value="<?php echo ConfigurationMailer::$smtp_username;?>">
</div>
<div class="input-group" style="padding:5px 0;">
	  <span class="input-group-addon"><i class="fa fa-key"></i></span>
	  <input type="password" class="form-control" name="smtp_pass" placeholder="<?php $l->dump("no_password")?>" value="<?php echo ConfigurationMailer::$smtp_password;?>">
</div>
</div>
<div class="input-group" style="padding:5px 0;">
	  <span class="input-group-addon"><i class="fa fa-server"></i></span>
	  <input type="text" class="form-control" name="smtp_host" placeholder="<?php $l->dump("host")?>" value="<?php echo ConfigurationMailer::$smtp_host;?>">
</div>
<div class="input-group" style="padding:5px 0;">
	  <span class="input-group-addon"><i class="fa fa-wrench"></i></span>
	  <input type="text" class="form-control" name="smtp_port" type="number" pattern="[0-9]*" inputmode="numeric" onkeypress='return event.charCode >= 48 && event.charCode <= 57' placeholder="<?php $l->dump("port")?>" value="<?php echo ConfigurationMailer::$smtp_port?>">
</div>
</div>
</div>
</div>
<div id="te_form" style="display:none!important;">
<?php $l->dump("email_test")?><br>
<div class="row" style="margin-top:10px;">
		<div class="col-xs-12">
			<table style="width:100%;">
			<tr>
				<td><input autocomplete="off" class="emailDes" id="emailDes" style="height:38px;width:100%;margin-right:35px;color:black;padding:5px;" onkeypress="if(event.charCode==13)$(this).closest('table').find('button').click();" type="text" placeholder="someone@example.com"/></td>
				<td><button style="width:100%;height:38px;" class="btn btn-default" onclick="testEmail($(this).closest('div.row').find('input.emailDes').val());" type="button"><i style="color:inherit;" class="fa fa-check"></i></button></td>
			</tr>
			</table>
		</div>
</div>
</div>
<?php ob_start()?>
<script>
$("#newDomain").on("keypress",function(e){	
	if(e.keyCode != 13) return true;
	e.preventDefault();
	$(".has-error").removeClass("has-error");
	let x=$(this);
	if(x.val()=="") {
		x.parent().addClass("has-error");
		return true;
	}
	$.post("<?php echo __SITEURL?>/admin/addDomain",{
		trueForm:1,
		domain:x.val()
	},function(d){
		if(d == "yes") {
			showMessage("<?php $l->dump('success')?>","success");			
			x.val("");
		}
		else showMessage("<?php $l->dump('action_failed')?>","danger");
	});
});
function testEmail(addr){
	if(addr === undefined){
		showMessage($("#te_form").html(),"info","eform",false);
		setTimeout(function(){$("#systemMessage_wrap #emailDes").focus();},10);
	}else{
		$.post("<?php echo __SITEURL?>/admin/testEmailSend",{des: addr},function(r){
			if(r == "f"){
				showMessage("<?php $l->dump("success")?>","success");
			}else{
				showMessage(r,"danger");				
			}
		});
		showMessage("Sending email..","warning","eform",false);
	}
}
</script>
<?php echo FastCache::outJSMin();?>