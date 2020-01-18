<html>

<head>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title><?php echo __SYSTEM_NAME ?> Error</title>
	<style>
		li {
			margin-left: -40px;
			list-style: none;
			margin-bottom: 10px;
		}

		@media(min-width:650px) {
			#wrap {
				margin: auto;
				margin-top: 5vh;
				padding: 15px 25px !important;
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
		<div style="overflow: auto;margin-top:15px;font-family:monospace;">
			<div style="width:max-content;"><?php hnl2br($msg) ?></div>
		</div>
		<br>
		<p style="font-size:9pt;text-align:right;color:#a0a0a0;">For more information please see error.log</p>
	</div>
</body>

</html>