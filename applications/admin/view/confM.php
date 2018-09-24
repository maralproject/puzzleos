<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$l = new Language;
?>
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
		  <input type="radio" data-toggle="tooltip" title="<?php $l->dump("INFO3")?>" name="ep" value="<?php echo 0?>"  <?php if(error_reporting() == 0) echo("checked");?>>
		  <?php $l->dump("NONE")?>
		</label>
		</fieldset>
	</div>
	<div class="col-md-6">	
		<legend style="margin-bottom:10px;"><?php $l->dump("mdomain")?></legend>
		<fieldset style="margin-left:25px;margin:15px 0;">
			<label class="checkbox-inline"><input type="checkbox" id="allow_mdomain" name="allow_mdomain" <?php if(POSConfigGlobal::$use_multidomain) echo "checked";?>/>
				<?php $l->dump("mdomain_ask")?>
			</label>
			
			<?php if(POSConfigGlobal::$use_multidomain):?>
			<br><br><button type="button" data-toggle="modal" data-target="#domain_dlg" class="btn-sm btn btn-info"><?php $l->dump("mz")?></button>
			<?php endif?>
		</fieldset>
	</div>
</div>
<div class="row">
	<div class="col-md-6">
		<legend style="margin-bottom:10px;"><?php $l->dump("DATABASE")?> <i data-toggle="tooltip" title="<?php $l->dump("INFO4")?>" class="fa fa-exclamation-triangle"></i></legend>
		<fieldset style="max-width:600px;">
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-user"></i></span>
			  <input type="text" class="form-control" placeholder="<?php $l->dump("USERNAME")?>" name="dbuser" value="<?php echo POSConfigDB::$username?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-key"></i></span>
			  <input type="password" class="form-control" name="dbpass" placeholder="<?php $l->dump("no_password")?>" value="<?php echo POSConfigDB::$password?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-server"></i></span>
			  <input type="text" class="form-control" name="dbhost" placeholder="<?php $l->dump("HOST")?>" value="<?php echo POSConfigDB::$host ?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-database"></i></span>
			  <input type="text" class="form-control" name="dbdb" placeholder="<?php $l->dump("DATABASE")?>" value="<?php echo POSConfigDB::$database_name ?>">
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
			  <input type="text" class="form-control" name="copytext" placeholder="<?php $l->dump("COPYRIGHT")?>" value="<?php echo POSConfigGlobal::$copyright?>">
		</div>
		<div class="input-group" style="margin-top:10px;">
			  <span class="input-group-addon"><i class="fa fa-info"></i></span>
			  <input type="text" class="form-control" name="metadesc" placeholder="<?php $l->dump("META_DESCRIPTION")?>" value="<?php echo POSConfigGlobal::$meta_description?>">
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
			  <input type="text" class="form-control" name="mailfrom" placeholder="<?php $l->dump("EMAIL_FROM")?>" value="<?php echo POSConfigMailer::$From?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <span class="input-group-addon"><i class="fa fa-pencil"></i></span>
			  <input type="text" class="form-control" name="mailname" placeholder="<?php $l->dump("EMAIL_NAME")?>" value="<?php echo POSConfigMailer::$Sender?>">
		</div>
		<div class="input-group" style="padding:5px;">
			  <button type="button" onclick="testEmail();" class="btn btn-sm btn-info"><?php $l->dump("email_test")?></button>
		</div>
		</fieldset>
	</div>
	<div class="col-md-6">
		<legend style="margin-bottom:10px;"><?php $l->dump("smtp_option")?></legend>
		<label class="checkbox-inline"><input type="checkbox" onclick="$('#smtp_details').slideToggle(200);" id="use_smtp" name="use_smtp" <?php if(!POSConfigMailer::$UsePHP) echo "checked";?>/><?php $l->dump("USE_SMTP_EN")?></label>
		<div id="smtp_details" style="<?php if(POSConfigMailer::$UsePHP) echo "display:none;";?>">
			<div class="input-group" style="padding:5px 0;">
				<span class="input-group-addon">
					<input value="none" type="radio" name="smtp_enc" id="radio0" <?php if(POSConfigMailer::$smtp_encryption == "none") echo "checked";?>> <label for="radio0"><?php $l->dump("SMTP_NO_ENC")?></label>
				</span>
				<span class="input-group-addon">
					<input type="radio" value="tls" name="smtp_enc" id="radio1" <?php if(POSConfigMailer::$smtp_encryption == "tls") echo "checked";?>> <label for="radio1">TLS</label>
				</span>
				<span class="input-group-addon">
					<input type="radio" value="ssl" name="smtp_enc" id="radio2" <?php if(POSConfigMailer::$smtp_encryption == "ssl") echo "checked";?>> <label for="radio2">SSL</label>
				</span>
			</div><br>
			<label class="checkbox-inline"><input type="checkbox" onclick="$('#smtp_auth_details').slideToggle(200);" id="use_smtp_auth" name="smtp_auth" <?php if(POSConfigMailer::$smtp_use_auth) echo "checked";?>/><?php $l->dump("use_smtp_auth")?></label>
			<div id="smtp_auth_details" style="<?php if(!POSConfigMailer::$smtp_use_auth) echo "display:none;";?>">
				<div class="input-group" style="padding:5px 0;">
					  <span class="input-group-addon"><i class="fa fa-user"></i></span>
					  <input type="text" class="form-control" placeholder="<?php $l->dump("username")?>" name="smtp_user" value="<?php echo POSConfigMailer::$smtp_username;?>">
				</div>
				<div class="input-group" style="padding:5px 0;">
					  <span class="input-group-addon"><i class="fa fa-key"></i></span>
					  <input type="password" class="form-control" name="smtp_pass" placeholder="<?php $l->dump("no_password")?>" value="<?php echo POSConfigMailer::$smtp_password;?>">
				</div>
			</div>
			<div class="input-group" style="padding:5px 0;">
				  <span class="input-group-addon"><i class="fa fa-server"></i></span>
				  <input type="text" class="form-control" name="smtp_host" placeholder="<?php $l->dump("host")?>" value="<?php echo POSConfigMailer::$smtp_host;?>">
			</div>
			<div class="input-group" style="padding:5px 0;">
				  <span class="input-group-addon"><i class="fa fa-wrench"></i></span>
				  <input type="text" class="form-control" name="smtp_port" type="number" pattern="[0-9]*" inputmode="numeric" onkeypress='return event.charCode >= 48 && event.charCode <= 57' placeholder="<?php $l->dump("port")?>" value="<?php echo POSConfigMailer::$smtp_port?>">
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

<?php if(POSConfigGlobal::$use_multidomain):?>
<!-- Domain manager dialog -->
<div class="modal fade" role="dialog" id="domain_dlg">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php echo $l->dump("mdomain")?></h4>
			</div>
			<div class="modal-body" style="padding-top:0">
				<div style="margin-bottom:10px">
					<ul class="nav nav-pills nav-justified" style="margin-top:0">
						<li class="active"><a data-toggle="tab" href="#md_reg"><?php $l->dump("rnd")?></a></li>
						<li><a data-toggle="tab" href="#md_man"><?php $l->dump("rod")?></a></li>
					</ul>
				</div>
				<div class="tab-content">
					<div id="md_reg" class="contact_wrap tab-pane active">
						<div>
							<div class="input-group">
								<input type="text" class="form-control" id="newDomain" placeholder="subdomain.domain.com">
								<span class="input-group-btn">
									<button class="btn btn-success" type="button" onclick="let e=jQuery.Event('keypress');e.keyCode=13;$('#newDomain').trigger(e);"><?php $l->dump("save")?></button>
								</span>
							</div>
						</div>
					</div>
					<div id="md_man" class="tr_wrap tab-pane">
						<div>
							<div class="input-group">
								<select class="form-control" id="mdomain_list">
									<?php foreach(glob(__ROOTDIR . "/configs/*.config.php") as $d): 
										if(substr($d,0,1) == "{") continue;
										$d = str_replace(__ROOTDIR . "/configs/","",str_replace(".config.php","",$d));
									?>
									<option value="<?php echo htmlentities($d)?>"><?php echo $d?></option>
									<?php endforeach?>
								</select>
								<span class="input-group-btn">
									<button class="btn btn-danger" type="button" onclick="$('#mdomain_list').trigger('rmd')"><?php $l->dump("rod")?></button>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif;?>

<?php ob_start()?>
<script>
$('#mdomain_list').on('rmd',function(e){
	let x=$(this);
	if(x.find("option").length <= 1){
		showMessage("<?php $l->dump('crd')?>","danger");
		return;
	}
	if(!confirm("Hapus domain ini?")) return;
	$.post("<?php echo __SITEURL?>/admin/rmDomain",{
		trueForm:1,
		domain:x.val()
	},function(d){
		if(d == "yes") {
			showMessage("<?php $l->dump('success')?>","success");			
			x.find("option[value='"+x.val()+"']").remove();
		}
		else showMessage("<?php $l->dump('action_failed')?>","danger");
	});
});
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
			$('#mdomain_list').append("<option value='"+x.val()+"'>"+x.val()+"</option>");
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