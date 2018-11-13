<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo __SYSTEM_NAME ?> Error</title>
		<style>
			li{
				margin-left:-40px;
				list-style:none;
				margin-bottom:10px;
			}
			@media(min-width:650px){
				#wrap{
					margin: auto;
					margin-top: 5vh;
					padding: 15px 25px!important;
					width: 100%;
					max-width: 600px;
					box-shadow: 1px 1px 4px 0px #9E9E9E;
					border-radius: 15px;
				}
			}
		</style>
	</head>
	<body style="margin:0px;width:100%;font-family:Helvetica, sans-serif, arial;font-size:13pt;">
		<div id="wrap" style="padding:25px;">
		<div style="font-size:30pt;font-weight:bold;">Oops...</div>
		<ul>
			<li><?php echo nl2br($msg) ?></li>
			<?php if ($suggestion != "") : ?><li><?php echo $suggestion ?></li><?php endif; ?>
		</ul>
		<br>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
		</div>
	</body>
</html>