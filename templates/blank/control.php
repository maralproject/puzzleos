<!DOCTYPE html>
<html>
	<head>
		<?php $tmpl->dumpHeaders(); ?>
		<title><?php echo $tmpl->title;?> - <?php echo __SITENAME;?></title>
		<style>
			html, body{
				width:100%;
				height:100%;
			}
			body{
				padding:0px 20px;
			}
			.form-control:focus{
				box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 0px 2px rgba(102,175,233,0.6);
				webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 0px 2px rgba(102,175,233,0.6);
			}
			.form-control, .input-group{
				border-radius:7px;
			}
		</style>
	</head>
	<body>
		<div style="float:right;"><?php $tmpl->navigation->loadView("login_bar");?></div>
		<?php if($tmpl->http_code == 200) $tmpl->app->loadMainView(); ?>
		<?php if($tmpl->http_code == 404) echo("404 Not Found"); ?>
		<?php 
		if($tmpl->http_code == 403){
			if(Accounts::authAccess(USER_AUTH_REGISTERED)){
				echo("404 Not Found");
			}else
				redirect("users?redir=/".urlencode(__HTTP_REQUEST));
		}
		?>
		<?php echo $tmpl->postBody;?>
		<?php Prompt::printPrompt(); ?>
	</body>
</html>
