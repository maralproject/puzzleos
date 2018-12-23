<!DOCTYPE html>
<html>
	<head>
		<?php $tmpl->dumpHeaders(); ?>
		<title><?php echo $tmpl->title;?> - <?php echo __SITENAME;?></title>
		<?php include "css.php"?>
	</head>
	<body>
		<div style="float:right;position: fixed;top: 0;right: 20px;z-index: 999;"><?php $tmpl->navigation->loadView("login_bar");?></div>
		<div class="container" style="padding-top:30px">
			<?php 
				if($tmpl->http_code == 200) $tmpl->app->loadMainView();
				else if($tmpl->http_code == 403){
					if(Accounts::authAccess(USER_AUTH_REGISTERED)){
						include("404.php");
					}else
						redirect("users?redir=/".urlencode(__HTTP_REQUEST));
				}else include("404.php");
			?>
		</div>
		<?php echo $tmpl->postBody?>
		<?php Prompt::printPrompt()?>
	</body>
</html>
