<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
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
}
.systemMessage_wrap .systemMessage{
	width: inherit;
	padding: 20px;
}
</style>
<?php 
	echo FastCache::outCSSMin();
	ob_start();
?>
<script>
(function() {
  function e() {
    1 > $(".systemMessage_wrap .systemMessage").length && hideMessage();
  }
  function f(a, b) {
    setTimeout(function() {
      $(".systemMessage_wrap").addClass("o");
      !1 !== b && setTimeout(function() {
        ("undefined" == typeof a ? $(".systemMessage_wrap .systemMessage") : a).fadeOut(500, function() {
          $(this).remove();
          e();
        });
      }, 3000);
    }, 1);
  }
  window.showMessage = function(a, b, d, c) {
    hideMessage();
    "string" != typeof d && (d = "");
    "boolean" != typeof c && (c = !0);
    a = $('<div auto_dismiss="' + (!1 === c ? "no" : "yes") + '" class="systemMessage m_' + d + " alert-" + b + '"><button onclick="hideMessage()" type="button" class="close">\u00d7</button><ul><li>' + a + "</li></ul></div>").appendTo(".systemMessage_wrap");
    f(a, c);
  };
  window.dismissMessage = function(a) {
    var b = $(".systemMessage_wrap .m_" + a);
    b.fadeOut(500, function() {
      b.remove();
    });
  };
  window.hideMessage = function() {
    $(".systemMessage_wrap").html("").removeClass("o");
  };
  $(document).on("change focusout", "input[inputmode='numeric'][lang='en-150']", function(a) {
    a.stopPropagation();
    $(this).val(parseFloat($(this).val().replace("+", "").replace("-", "")));
    "" == $(this).val() && $(this).val(0);
  }).ready(function() {
    setTimeout(function() {
      f();
    }, 300);
  }).on("click", "body", function(a) {
    1 > $(a.toElement).closest(".systemMessage_wrap").length && e();
  });
})();
</script>
<?php echo(FastCache::outJSMin())?>