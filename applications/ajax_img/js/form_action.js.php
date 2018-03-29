<?php ob_start()?>
<script>
$(document).ready(function() { 
$('.img_ajax').submit(function() { 
	return false; 
}); 
		
$(".img_ajax input[type=file]").change(function(){
	if($(this).hasClass("disabled")) return false;
	var key = $(this).attr("key");
	$(this).attr("status","0");
	let maxsize = $(this).parent().parent().attr("msz");
	let preview = $(this).attr("preview");
	let upload_obj = $(this);
	let timer;
	$(this).parent().find("input[name='prev']").val($(preview).html());
	$(this).parent().parent().ajaxSubmit({ 
		target: preview,
		beforeSubmit: function(){
			if (window.File && window.FileReader && window.FileList && window.Blob){		
				if( !upload_obj.val()){
					showMessage(L_NO_FILE,"warning");
					return false;
				}else{
					let fsize = upload_obj[0].files[0].size;
					let ftype = upload_obj[0].files[0].type;
					if(fsize>maxsize){
						upload_obj.attr("status","0");
						showMessage(L_TOO_BIG,"danger");
						return false;
					}else{
						upload_obj.attr("status","1");
						upload_obj.prop("disabled",true);
						timer = setTimeout(function(){
							upload_obj.parent().hide();
							upload_obj.parent().parent().find(".upload_progress").show();
						},1200);
					}
				}
			}else{
				showMessage(L_UPGRADE,"warning");
				return false;
			}
		},
		success: function(){
			upload_obj.attr("status","2");
			clearTimeout(timer);
			upload_obj.parent().show();
			upload_obj.prop("disabled",false);
			upload_obj.parent().parent().find(".upload_progress").hide();
			upload_obj.parent().parent().find(".upload_progress .progress-bar").css("width","0%");
			upload_obj.parent().parent().find(".upload_progress .progress-bar").attr("aria-valuenow","0");
		},
		uploadProgress: function(event, position, total, percentComplete){			
			upload_obj.attr("status","1");
			upload_obj.parent().parent().find(".upload_progress .progress-bar").css("width",percentComplete + "%");
			upload_obj.parent().parent().find(".upload_progress .progress-bar").attr("aria-valuenow",percentComplete);
		},
		resetForm: true
	}); 
	return false;
});
});
</script>
<?php echo FastCache::getJSFile();?>