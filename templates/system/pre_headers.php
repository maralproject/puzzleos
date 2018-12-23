<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */
?>
<meta name="description" content="<?php h(POSConfigGlobal::$meta_description)?>"/>
<meta name="generator" content="PuzzleOS"/>

<?php ob_start()?>
<style>
.alert-danger {
    color: #fff;
    background-color: var(--red);
}
.alert-success {
    color: #fff;
    background-color: var(--green);
}
.alert-info {
    color: #fff;
    background-color: var(--info);
}
.alert-primary {
    color: #fff;
    background-color: var(--primary);
}
.alert-warning {
    color: #000;
    background-color: var(--warning);
}
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
    margin:10px 5px;
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
.systemMessage_wrap.o{
	transition:.13s transform;
	transform:translate3d(0,-100%,0);
}
.systemMessage_wrap{
	position: fixed;
	top:100%;
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
	margin:0px;
}
.systemMessage_wrap .systemMessage{
	width: inherit;
	padding: 20px;
	display:grid;
	grid-template-columns: auto min-content;
	align-items: center;
}
</style>
<?php echo Minifier::outCSSMin()?>

<?php ob_start()?>
<script>
(function(){
	window["showMessage"] = function(data,type,key,auto_dismiss){
		hideMessage();
		if(typeof key != "string") key = "";
		if(typeof auto_dismiss != "boolean") auto_dismiss = true;
		var d=$('<div auto_dismiss="' + (auto_dismiss === false?"no":"yes") + '" class="systemMessage m_' + key + ' alert-'+type+'"><ul><li>' + data + '</li></ul><button onclick="hideMessage()" type="button" class="close">×</button></div>').appendTo(".systemMessage_wrap");
		slideMessage(d,auto_dismiss);
	};

	window["dismissMessage"] = function(key){
		var s = $(".systemMessage_wrap .m_" + key);
		s.fadeOut(500,function(){
			s.remove();
		});
	};

	window["hideMessage"] = function (){
		$(".systemMessage_wrap").html("").removeClass("o");
	};
	
	function checkHideMessage(){
		if($(".systemMessage_wrap .systemMessage").length < 1) hideMessage();
	}
	
	function slideMessage(d,i){
        if($(".systemMessage_wrap .systemMessage").length > 0){
            setTimeout(function(){
                $(".systemMessage_wrap").addClass("o");
                if(i !== false){
                    setTimeout(function(){
                        (typeof d=="undefined"?$(".systemMessage_wrap .systemMessage"):d).fadeOut(500,function(){
                            $(this).remove();
                            checkHideMessage();
                        });
                    },3000);
                }
            },1);
        }
	}

	$(document).on("change focusout","input[inputmode='numeric'][lang='en-150']",function(e){
		e.stopPropagation();
		$(this).val(parseFloat($(this).val().replace("+","").replace("-","")));
		if($(this).val() == "") $(this).val(0);
	}).ready(function(){
		setTimeout(function(){slideMessage()},300);
	}).on("click","body",function(e){
		if($(e.toElement).closest(".systemMessage_wrap").length < 1) checkHideMessage();
	});
}());
</script>
<?php echo Minifier::outJSMin()?>