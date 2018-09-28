<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */
?>
<?php ob_start()?>
<style>
.tmpl .col {
	cursor:pointer;
	text-align:center;
	border:1px solid #e0e0e0;
}
.tmpl .col:hover{
	background-color:#e0e0e0;
}
.tmpl .col:active{
	background-color:#a0a0a0;
	color:white;
}
.tmpl .col:first-child { margin-left: 0; }

.tmpl .selected_t:before{
	color:white;
	content:"\f058";
	font-family:FontAwesome;
	font-size:40pt;
    position: absolute;
	bottom:10px;
	left:10px;
	text-shadow: 0px 0px 12px rgba(150, 150, 150, 1);
}

.tmpl .selected_t{
	position:relative;
	background-color:#606060!important;
	cursor:default!important;
	border:1px solid #606060;
}

.tmpl .selected_t h5{	
	color:white!important;
}
</style><?php echo Minifier::getCSSFile()?>
<div class="tmpl row" style="margin-left:5px;margin-right:5px;">
	<?php
		foreach(Template::listAll() as $d){
			$link = 'onclick="window.location=\''.__SITEURL.'/admin/changeTemplate/'.$d["name"].'\';"';
			$preview = glob(__ROOTDIR . "/templates/".$d["name"]."/preview.*");
			if($preview[0]=="") $preview="";
			else $preview = IO::publish($preview[0]);
			echo('
			<div style="margin:0px;" class="col col-md-4 '.($d["active"] == 1?"selected_t":"").'" '.($d["active"] == 1?"":$link).'>
				<div style="background-image:url(\''.$preview.'\');background-repeat:no-repeat;background-size:contain;width:100%;height:180px;background-position:center;"></div>
				<h5>'.$d["title"].'</h5>
			</div>
			');
		}
	?>
</div>