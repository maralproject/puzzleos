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
		slideMessage(d);
	};

	window["dismissMessage"] = function(key){
		var s = $(".systemMessage_wrap .m_" + key);
		s.fadeOut(500,function(){
			s.remove();
		});
	};

	window["hideMessage"] = function (){
		$(".systemMessage_wrap").html("").removeClass("o");
		systemMessageExists = false;
	};

	function slideMessage(d){
		if(typeof d=="undefined") d=$(".systemMessage_wrap .systemMessage");
		setTimeout(function(){
			setTimeout(function(){
				systemMessageExists = ($(".systemMessage_wrap .systemMessage").length > 0);
				if(d.attr("auto_dismiss") == "yes"){
					setTimeout(function(){
						d.fadeOut(500,function(){
							d.remove();
							systemMessageExists = ($(".systemMessage_wrap .systemMessage").length > 0);
							if(systemMessageExists==true) hideMessage();
						});
					},3000);
				}else{
					d.find("input").first().focus();
				}
			},130);
			$(".systemMessage_wrap").addClass("o");
		},1);
	}

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
			if(systemMessageExists == true) hideMessage();
		}
	});
}());