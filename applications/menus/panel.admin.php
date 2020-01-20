<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$fontawesome = iApplication::run("fontawesome");
$acc_app = iApplication::run("users");
$fontawesome->loadView("JSNewInput");
$l = new Language;
$upload = iApplication::run("upload_img_ajax"); ?>
<div id="menu">
	<h1><?php $l->dump("CUSTOM_NAV_MENU") ?></h1>
	<br>
	<?php ob_start() ?>
	<style>
		.tableMenu td {
			border: 1px solid #e0e0e0;
			padding: 5px;
			font-size: 11pt;
		}

		.tableMenu select {
			padding-left: 0px;
			padding-right: 0px;
		}

		.qtyin {
			height: 30px;
			font-weight: bold;
			border: solid 0px #00E3E3;
			font-size: 14px;
			color: #474747;
			min-width: 100px;
			width: 100%;
			-webkit-transition: all 0.30s ease-in-out;
			-moz-transition: all 0.30s ease-in-out;
			-ms-transition: all 0.30s ease-in-out;
			-o-transition: all 0.30s ease-in-out;
			outline: none;
			padding: 3px 0px 3px 3px;
			margin: 5px 1px 3px 0px;
		}

		.qtyin:focus {
			box-shadow: 0 0 5px rgba(81, 203, 238, 1);
			padding: 3px 0px 3px 3px;
			margin: 5px 1px 3px 0px;
			border: 1px solid rgba(81, 203, 238, 1);
		}
	</style>
	<?php echo Minifier::getCSSFile() ?>
	<div class="table-responsive">
		<table style="width:100%;" class="tableMenu">
			<tr style="font-weight:bold;">
				<td style="width:70px;"><?php $l->dump("ICON") ?></td>
				<td><?php $l->dump("NAME") ?></td>
				<td><?php $l->dump("LINK") ?></td>
				<td style="width:100px;"><span><?php $l->dump("AUTH") ?></span></td>
				<td style="width:100px;"><span><?php $l->dump("POS") ?></span></td>
				<td style="width:70px;"><?php $l->dump("DELETE") ?></td>
			</tr>
			<?php foreach (Database::readAll("app_menus_main") as $d) : ?>
				<tr name="<?php echo $d["id"] ?>">
					<td style="text-align:center;width:70px;"><?php $fontawesome->loadview("getdropChoiceInput", array("fa_" . $d["id"], $d["fa"])) ?></td>
					<td><input class="qtyin info2 name" type="text" name="name_<?php echo $d["id"] ?>" value="<?php echo $d["name"] ?>" title="<?php $l->dump("click_here") ?>" placeholder="<?php $l->dump("name") ?>"></td>
					<td><input class="qtyin info2 link" type="text" name="link_<?php echo $d["id"] ?>" value="<?php echo $d["link"] ?>" title="<?php $l->dump("click_here") ?>" placeholder="<?php $l->dump("la") ?>"></td>
					<td style="width:100px;text-align:center;"><span class="info2" title="<?php $l->dump("choose_who") ?>"><?php $acc_app->loadView("group_button", ["auth_" . $d["id"], $d["minUser"]]) ?></span></td>
					<td style="width:100px;text-align:center;">
						<select class="form-control menu_pos" name="pos_<?php echo $d["id"] ?>" x="<?php echo $d["location"] ?>">
							<option value="<?php echo MENU_DEFAULT_POSITION_LEFT ?>"><?php $l->dump("pos_left") ?></option>
							<option value="<?php echo MENU_DEFAULT_POSITION_TOP ?>"><?php $l->dump("pos_top") ?></option>
							<option value="<?php echo MENU_DEFAULT_POSITION_RIGHT ?>"><?php $l->dump("pos_right") ?></option>
							<option value="<?php echo MENU_DEFAULT_POSITION_BOTTOM ?>"><?php $l->dump("pos_bottom") ?></option>
						</select>
					</td>
					<td style="text-align:center;width:70px;" class="click_available delcol"><i class="fa fa-remove"></i></td>
					<script>
						$("select[name=pos_<?php echo $d["id"] ?>]").val(<?php echo $d["location"] ?>)
					</script>
				</tr>
			<?php endforeach; ?>
			<tr class="addBigButton" style="display:none;">
			</tr>
		</table>
	</div>
	<div style="text-align:center;margin-top:25px;"><button class="btn btn-info new_btn"><?php $l->dump("new_item") ?></button></div>
	<?php ob_start(); ?>
	<script>
		(function() {
			function grey() {
				showMessage("<?php $l->dump("SAVED") ?>", "success");
			}
			$(document).on("DOMSubtreeModified", "#faviconPreview", function() {
				$.ajax({
					url: "<?php echo __SITEURL ?>/menus/change_favicon"
				});
			});
			$(document).on("DOMSubtreeModified", "#logoPreview", function() {
				$.ajax({
					url: "<?php echo __SITEURL ?>/menus/change_header"
				});
			});
			$(document).on("click", ".delcol", function() {
				$.post("<?php echo __SITEURL ?>/menus/delete", {
					trueData: "yes",
					name: $(this).parent().attr("name")
				}, function(data) {
					$(".tableMenu tr[name='" + data + "']").remove();
					grey();
				});
			});
			$(document).on("change", ".tableMenu .fa-input-hidden", function() {
				$.post("<?php echo __SITEURL ?>/menus/changeIcon", {
					trueData: "yes",
					name: $(this).attr("name").replace("fa_", ""),
					val: $(this).val()
				}, function(data) {
					grey();
				});
			});
			$(document).on("change", ".tableMenu .usergroup-input", function() {
				$.post("<?php echo __SITEURL ?>/menus/changeAuth", {
					trueData: "yes",
					name: $(this).attr("name").replace("auth_", ""),
					val: $(this).val()
				}, function(data) {
					grey();
				});
			});
			$(document).on("change", ".tableMenu .name", function() {
				$.post("<?php echo __SITEURL ?>/menus/changeName", {
					trueData: "yes",
					name: $(this).attr("name").replace("name_", ""),
					val: $(this).val()
				}, function(data) {
					grey();
				});
			});
			$(document).on("change", ".tableMenu .link", function() {
				$.post("<?php echo __SITEURL ?>/menus/changeLink", {
					trueData: "yes",
					name: $(this).attr("name").replace("link_", ""),
					val: $(this).val()
				}, function(data) {
					grey();
				});
			});
			$(document).on("change", ".tableMenu .menu_pos", function() {
				$(this).attr("x", $(this).val());
				$.post("<?php echo __SITEURL ?>/menus/changePos", {
					trueData: "yes",
					name: $(this).attr("name").replace("pos_", ""),
					val: $(this).val()
				}, function(data) {
					grey();
				});
			});
			$(document).on("change", "#filter_<?php echo MENU_DEFAULT_POSITION_LEFT ?>", function() {
				if ($(this).prop("checked"))
					$(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_LEFT ?>]").parent().parent().show();
				else $(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_LEFT ?>]").parent().parent().hide();
			});
			$(document).on("change", "#filter_<?php echo MENU_DEFAULT_POSITION_TOP ?>", function() {
				if ($(this).prop("checked"))
					$(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_TOP ?>]").parent().parent().show();
				else $(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_TOP ?>]").parent().parent().hide();
			});
			$(document).on("change", "#filter_<?php echo MENU_DEFAULT_POSITION_RIGHT ?>", function() {
				if ($(this).prop("checked"))
					$(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_RIGHT ?>]").parent().parent().show();
				else $(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_RIGHT ?>]").parent().parent().hide();
			});
			$(document).on("change", "#filter_<?php echo MENU_DEFAULT_POSITION_BOTTOM ?>", function() {
				if ($(this).prop("checked"))
					$(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_BOTTOM ?>]").parent().parent().show();
				else $(".menu_pos[x=<?php echo MENU_DEFAULT_POSITION_BOTTOM ?>]").parent().parent().hide();
			});
			$(document).on("click", "button.new_btn", function() {
				var name = "";
				$.post("<?php echo __SITEURL ?>/menus/new", {
					trueData: "yes"
				}, function(data) {
					name = data;
					var html = '<tr name="' + name + '">\
					<td style="text-align:center;width:70px;">' + getNewIconInput("fa_" + name, "tags") + '</td>\
					<td><input class="qtyin info2 name" type="text" name="name_' + name + '" value="" title="<?php $l->dump("click_here") ?>" placeholder="<?php $l->dump("name") ?>"></td>\
					<td><input class="qtyin info2 link" type="text" name="link_' + name + '" value="" title="<?php $l->dump("click_here") ?>" placeholder="<?php $l->dump("la") ?>"></td>\
					<td style="width:100px;text-align:center;"><span class="info2" title="<?php $l->dump("choose_who") ?>"><?php $acc_app->loadView("group_button", ["auth_' + name + '", PuzzleUserGroup::get(USER_AUTH_PUBLIC)->id]) ?></span></td>\
					<td style="width:100px;text-align:center;">\
					<select class="form-control menu_pos" name="pos_' + name + '" x="0">\
						<option value="<?php echo MENU_DEFAULT_POSITION_LEFT ?>"><?php $l->dump("pos_left") ?></option>\
						<option value="<?php echo MENU_DEFAULT_POSITION_TOP ?>"><?php $l->dump("pos_top") ?></option>\
						<option value="<?php echo MENU_DEFAULT_POSITION_RIGHT ?>"><?php $l->dump("pos_right") ?></option>\
						<option value="<?php echo MENU_DEFAULT_POSITION_BOTTOM ?>"><?php $l->dump("pos_bottom") ?></option>\
					</select>\
					</td>\
					<td style="text-align:center;width:70px;" class="click_available delcol"><i class="fa fa-remove"></i></td>\
					</tr>';
					$(".addBigButton").before(html);
					_fa_configureIconInput("fa_" + name, "tags");
					grey();
				});
			});
		}());
	</script>
	<?php echo Minifier::outJSMin(); ?>
</div>