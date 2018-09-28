<!DOCTYPE html>
<html lang="en" style="height:100%">
	<head>
		<?php $tmpl->dumpHeaders()?>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,minimum-scale=1.0">
		<meta name="description" content="Creative - Bootstrap 3 Responsive Admin Template">
		<meta name="author" content="GeeksLabs">
		<meta name="keyword" content="Creative, Dashboard, Admin, Template, Theme, Bootstrap, Responsive, Retina, Minimal">
		<link rel="shortcut icon" href="<?php echo $tmpl->url?>/img/favicon.png">
		<title><?php echo $tmpl->title;?> - <?php echo __SITENAME;?></title>		
		<script src="<?php echo $tmpl->url?>/jquery.mobile.custom.min.js"></script>
		<link href="<?php echo $tmpl->url?>/css/elegant-icons-style.css" rel="stylesheet" />		
		<link href="<?php echo $tmpl->url?>/css/style.css" rel="stylesheet">
		<meta name="theme-color" content="#1a2732" />
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="mobile-web-app-capable" content="yes">
		<meta name="application-name" content="<?php echo __SITENAME?>">
		<?php ob_start();?>
		<script>
		$(document).ready(function(){
			if('ontouchstart' in document.documentElement){
				$("#openArea").on("swiperight",function(e){			
					e.stopPropagation();
					$('#sidebar').addClass('shown');
				});
				$(document).on("swipeleft",function(e){			
					e.stopPropagation();
					$('#sidebar').removeClass('shown');
				});
			}
			$("div.toggle-nav").on("click",function(e){
				e.stopPropagation();
				$('#sidebar').toggleClass('shown');
			});
			$(document).on("click",function(){
				$('#sidebar').removeClass('shown');
			});
		});
		</script>
		<?php echo Minifier::outJSMin()?>
	</head>	
	<body style="height:100%">
		<div id="openArea" style="z-index:10000;background:black;opacity:0;width:20px;position:fixed;left:0;height:100%;"></div>
		<div style="position: fixed;z-index: 1040;right: 15px;"><?php $tmpl->navigation->loadView("login_bar");?></div>		
		<section id="container" style="height:100%">
			<header class="header dark-bg">
				<div class="toggle-nav">
					<div class="icon-reorder tooltips" data-original-title="Toggle Navigation" data-placement="bottom"><i class="icon_menu"></i></div>
				</div>
				<!--logo start-->
				<a href="<?php echo __SITEURL?>" class="logo"><?php echo __SITENAME?></a>
				<!--logo end-->
			</header>
			<!--header end-->
			<div id="sidebar" class="nav-collapse">
				<!-- sidebar menu start-->
				<ul class="sidebar-menu">
					<?php $tmpl->navigation->loadView("left");?>
				</ul>
			</div>
			<div class="container" style="height:100%">
				<!--main content start-->
				<section id="main-content" style="padding:5px;padding-top:80px;margin-left:0px;height:100%;">      
					<?php if($tmpl->http_code == 200) $tmpl->app->loadMainView(); ?>
					<?php if($tmpl->http_code == 404) include("404.php"); ?>
					<?php 
					if($tmpl->http_code == 403){
						if(Accounts::authAccess(USER_AUTH_REGISTERED)){
							include("404.php");
						}else
							redirect("users?redir=/".urlencode(__HTTP_REQUEST));
					}
					?>
				</section>
				<!--main content end-->
			</div>
		</section>
		<!-- container section start -->
		<?php echo $tmpl->postBody;?>
		<?php Prompt::printPrompt(); ?>
	</body>
</html>