<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.2") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.2.3
 */
?>
<script>var user_config_changed = 0;</script>
<script>function reloadGroup(){if(user_config_changed == 1){location.reload();}else{$('#groups').show();}}</script>
<script>function reloadUsers(){if(user_config_changed == 1){location.reload();}else{$('#userlist').show();}}</script>
<div class="container">
<ul class="nav nav-tabs" style="font-size:15pt;">
  <li onclick="$('.tab').hide();reloadUsers();$('.tabS').removeClass('active');$(this).addClass('active');hideMessage();" class="tabS active"><a href="#userlist"><i class="fa fa-list-ul"></i></a></li>
  <li onclick="$('.tab').hide();reloadGroup();$('.tabS').removeClass('active');$(this).addClass('active');hideMessage();" class="tabS"><a href="#groups"><i class="fa fa-users"></i></a></li>
</ul>
<div id="userlist" class="tab">
	<?php include("views/manage_user.php")?>
</div>
<div id="groups" class="tab" style="display:none;">
	<?php include("views/manage_group.php")?>
</div>
</div>
<script>
Bootstrap_LinkTab();
$(window).on('hashchange', function() {
	Bootstrap_LinkTab();
});
</script>