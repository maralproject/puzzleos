<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */

?>
<meta name="description" content="<?php echo POSConfigGlobal::$meta_description;?>"/>
<meta name="generator" content="PuzzleOS"/>
<?php ob_start();?>
<style type="text/css">
@media(max-width:786px){
	.modal-footer button,#confirmation_bar button, #confirmation_bar input[type=button], #confirmation_bar input[type=submit]{		
		padding:10px;
		font-size:12px;
	}
}
#confirmation_bar{
	background-color:#fbfbfb;
	position:fixed;
	right:0px;
	left:0px;
	bottom:0px;
	width:100%;
	text-align:right;
	padding:10px;
	z-index:998;
	-webkit-box-shadow: 0px -1px 4px 0.5px rgba(0,0,0,0.14);
	box-shadow: 0px -1px 4px 0.5px rgba(0,0,0,0.14);
}
#confirmation_bar button, #confirmation_bar input[type=button],#confirmation_bar input[type=submit],#confirmation_bar span.caret{
	margin-left:5px;
}
.ellipsis{
	overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
.ripple {
  position: relative;
  overflow: hidden;
  transform: translate3d(0, 0, 0);
}
.ripple:after {
	content: "";
	display: block;
	position: absolute;
	width: 100%;
	height: 100%;
	top: 0;
	left: 0;
	background-image: -webkit-radial-gradient(circle, #444 10%, transparent 10.01%);
	background-image: -o-radial-gradient(circle, #444 10%, transparent 10.01%);
	background-image: radial-gradient(circle, #444 10%, transparent 10.01%);
	background-repeat: no-repeat;
	-webkit-background-size: 1000% 1000%;
	background-size: 1000% 1000%;
	background-position: 50%;
	opacity: 0;
	pointer-events: none;
	-webkit-transition: background .5s, opacity 1s;
	-o-transition: background .5s, opacity 1s;
	transition: background .5s, opacity 1s;
}
.ripple:active:after {
	-webkit-background-size: 0% 0%;
	background-size: 0% 0%;
	opacity: .2;
	-webkit-transition: 0s;
	-o-transition: 0s;
	transition: 0s;
}
.material_card {
  background: #fff;
  border-radius: 2px;
  cursor:pointer;
  display: inline-block;
  margin: 1rem;
  position: relative;
  box-shadow: 0 0 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
  transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}
.btn-link:hover{
	text-decoration:none!important;
}
.material_card:hover {
  border-bottom: 5px solid #5fbae9;
  box-shadow: 0 3px 8px rgba(0,0,0,0.25), 0 1px 1px rgba(0,0,0,0.22);
}
.card_container{
	padding:5px;
}
.card_container .material_card{
	width:95%;
	padding:10px;
}

.click_available{
	cursor:pointer;
}

.click_available:hover{
	color:grey;
	font-weight:bold;
}

.click_available:active{
	color:#BFBFBF;
	font-weight:bold;
}
.systemMessage_wrap{
	position: fixed;
    bottom: 0px;
    left: 0px;
    right: 0px;
    max-width: 600px;
    margin: auto;
    z-index: 2000;
	backgound-color:white;
	-webkit-box-shadow: 0px -1px 4px 0.5px rgba(0,0,0,0.14);
	-moz-box-shadow: 0px -1px 4px 0.5px rgba(0,0,0,0.14);
	box-shadow: 0px -1px 4px 0.5px rgba(0,0,0,0.14);
}
.systemMessage_wrap .systemMessage ul{
	padding-left:10px;
	list-style:none;
	font-size:13pt;
}
.systemMessage_wrap .systemMessage{
	width: inherit;
	padding: 20px;
	display:none;
}
</style>
<?php 
	echo FastCache::getCSSFile();
	ob_start();
?>
<script>
function showMessage(data,type,key,auto_dismiss){
	hideMessage();
	if(key === undefined) key = "";
	if(auto_dismiss === undefined) auto_dismiss = true;
	var ad = "yes";
	if(auto_dismiss === false) ad = "no";
	$(".systemMessage_wrap").append('<div auto_dismiss="' + ad + '" class="systemMessage m_' + key + ' alert-'+type+'"><button onclick="hideMessage()" type="button" class="close">×</button><ul><li>' + data + '</li></ul></div>');
	slideMessage();
}
function dismissMessage(key){
	var s = $(".systemMessage_wrap .m_" + key);
	s.fadeOut(500,function(){
		s.remove();
	});
}
function hideMessage(){
	$(".systemMessage_wrap").html("");
	window["systemMessageExists"] = false;
}
function slideMessage(){
	setTimeout(function(){
	$(".systemMessage_wrap .systemMessage").slideDown(130,function(){
		window["systemMessageExists"] = ($(".systemMessage_wrap .systemMessage").length > 0);
		var s = $(this);
		if(s.attr("auto_dismiss") == "yes"){
			setTimeout(function(){
				s.fadeOut(500,function(){
					window["systemMessageExists"] = ($(".systemMessage_wrap .systemMessage").length > 0);
					s.remove();
				});
			},3000);
		}else{
			s.find("input").first().focus();
		}
	});
	},1);
}
window["systemMessageExists"] = false;
$(document).on("change focusout","input[inputmode='numeric'][lang='en-150']",function(e){
	e.stopPropagation();	
	$(this).val(parseFloat($(this).val().replace("+","").replace("-","")));
	if($(this).val() == "") $(this).val(0);
});
setTimeout(function(){
	slideMessage();
},300);
$(window).on("click",function(e){
	if($(e.toElement).closest(".systemMessage_wrap").length < 1){
		if(window["systemMessageExists"] == true) hideMessage();
	}
});
</script>
<?php echo(FastCache::getJSFile())?>