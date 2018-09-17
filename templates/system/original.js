// ==ClosureCompiler==
// @output_file_name default.js
// @compilation_level ADVANCED_OPTIMIZATIONS
// @formatting pretty_print
// ==/ClosureCompiler==

(function(){
	var systemMessageExists = false;

	window["showMessage"] = function(data,type,key,auto_dismiss){
		hideMessage();
		if(typeof key != "string") key = "";
		if(typeof auto_dismiss != "boolean") auto_dismiss = true;
		var d=$('<div auto_dismiss="' + (auto_dismiss === false?"no":"yes") + '" class="systemMessage m_' + key + ' alert-'+type+'"><button onclick="hideMessage()" type="button" class="close">×</button><ul><li>' + data + '</li></ul></div>').appendTo(".systemMessage_wrap");
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

	$(document).on("change focusout","input[inputmode='numeric'][lang='en-150']",function(e){
		e.stopPropagation();
		$(this).val(parseFloat($(this).val().replace("+","").replace("-","")));
		if($(this).val() == "") $(this).val(0);
	}).ready(function(){
		setTimeout(function(){slideMessage();},300);
	}).on("click","body",function(e){
		if($(e.toElement).closest(".systemMessage_wrap").length < 1){
			checkHideMessage();
		}
	});
}());