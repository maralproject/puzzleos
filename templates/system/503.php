<!doctype html>
<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0,minimum-scale=1.0">
		<title><?php echo __SYSTEM_NAME?></title>
		<style>
			@import url('https://fonts.googleapis.com/css?family=Roboto:100,400');
			@-webkit-keyframes fadeIn { from{ opacity:0; } to { opacity:1; } }
			@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
			@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }	
			@media(min-width:1068px){
				.offline{
					font-size:80pt!important;
				}
			}
			body{
				margin:0px;width:100%;font-family:Roboto, sans-serif, arial;font-size:13pt;
			}							
			.offline{
				text-align:center;
				right:0px;
				left:0px;
				font-size:30pt;
				position:absolute;
				top:35vh;
				padding:0 45px;
				font-weight: 100;
				color: #a0a0a0;
				opacity:1;
				-webkit-animation:fadeIn ease-in .5s;
				-moz-animation:fadeIn ease-in .5s;
				animation:fadeIn ease-in .5s;
			}
		</style>
	</head>
	<body>
		<div class="offline">We'll be right back.</div>
	</body>
</html>