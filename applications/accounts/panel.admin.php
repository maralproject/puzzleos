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
?>
<script>
	var user_config_changed = 0;
	function reloadGroup(){if(user_config_changed == 1){location.reload();}else{$('#groups').show();}}
	function reloadUsers(){if(user_config_changed == 1){location.reload();}else{$('#userlist').show();}}
</script>

<div class="container">
	<ul class="nav nav-tabs" style="font-size:15pt;">
		<li onclick="$('.tab').hide();reloadUsers();$('.tabS').removeClass('active');$(this).addClass('active');hideMessage();return false;" class="tabS active"><a data-toggle="tab" href="#userlist"><i class="fa fa-list-ul"></i></a></li>
		<li onclick="$('.tab').hide();reloadGroup();$('.tabS').removeClass('active');$(this).addClass('active');hideMessage();return false;" class="tabS"><a data-toggle="tab" href="#groups"><i class="fa fa-users"></i></a></li>
		<li onclick="$('.tab').hide();$('#setting').show();$('.tabS').removeClass('active');$(this).addClass('active');hideMessage();return false;" class="tabS"><a data-toggle="tab" href="#setting"><i class="fa fa-wrench"></i></a></li>
	</ul>
	<div id="userlist" class="tab">
		<?php include("views/manage_user.php")?>
	</div>
	<div id="groups" class="tab" style="display:none;">
		<?php include("views/manage_group.php")?>
	</div>
	<div id="setting" class="tab" style="display:none;">
		<?php include("views/manage_config.php")?>
	</div>
</div>