<?php ob_start()?>
<script>
(function(){
	var upload=function($j,file){
		if($j.prop("disabled") || !(file instanceof File)) return false;
		$j.attr("status",0); //0=>Idle, 1=>Uploading, 2=>Just Finished
		var $f = $j.closest("form");
		var preview = $($j.attr("preview"));
		var timer;
		var oldprev = preview.find("div").css("background-image");

		var formdata = new FormData();
		formdata.append("file",file,file.name);
		formdata.append("key",$j.attr("key"));
		$.ajax({
			url:"/upload_img_ajax/upload",
			contentType: false,
			processData: false,
			cache: false,
			type: 'POST',
			data:null,
			xhr: function() {
				var xhr = $.ajaxSettings.xhr();
				if (xhr.upload) {
					xhr.upload.onprogress = function(e) {
						var percent = 0;
						var position = e.loaded;
						var total = e.total;
						if (e.lengthComputable) percent = Math.ceil(position / total * 100);
						$j.attr("status",1);
						$f.find(".upload_progress .progress-bar").css("width",percent + "%").attr("aria-valuenow",percent);
					};
				}
				return xhr;
			},
			beforeSend: function(xhr, o) {
				o.data = formdata;
				preview.trigger("clear");
				if (window.File && window.FileReader && window.FileList && window.Blob && window.URL){
					var fsize = file.size;
					if(fsize > <?php echo php_max_upload_size()?>){
						$j.attr("status",0);
						showMessage(L_TOO_BIG,"danger");
						xhr.abort();
					}else{
						$j.attr("status",1);
						$j.prop("disabled",true);
						timer = setTimeout(function(){
							$j.parent().hide();
							$f.find(".upload_progress").show();
						},1200);
					}
				}else{
					showMessage(L_UPGRADE,"warning");
					xhr.abort();
				}
			},
			success: function(d){
				clearTimeout(timer);
				d = JSON.parse(d);
				if(d.success){
					preview.find("div").css("background-image","url("+URL.createObjectURL(file)+")");
				}else{
					preview.find("div").css("background-image",oldprev);
					showMessage(d.reason,"danger");
				}
				$j.attr("status",2).prop("disabled",false).parent().show();
				$f.find(".upload_progress").hide();
				$f.find(".upload_progress .progress-bar").css("width","0%").attr("aria-valuenow","0");
				$f[0].reset();
				preview.change();
			}
		});
	};

	$(document).on("change",".img_ajax input[type=file]",function(e,f){
		$(this).trigger("filechange",[this.files[0]]);
		upload($(this),this.files[0]);
	}).on("upload",".img_ajax input[type=file]",function(e,d){
		(d instanceof File) ? upload($(this),d) : null;
	}).on("submit",".img_ajax",function(e){
		e.preventDefault();
		return false;
	});

}());
</script>
<?php echo Minifier::getJSFile()?>