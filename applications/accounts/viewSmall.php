<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

$func = [
	"group_button" => function($input_name,$group,$level_option = USER_AUTH_PUBLIC){
		switch($level_option){
			case USER_AUTH_SU:
			case USER_AUTH_EMPLOYEE:
			case USER_AUTH_REGISTERED:
			case USER_AUTH_PUBLIC:
			break;
			default:
				return("");
		}
		if(!defined("__UGLB_OUT")){
			$dataLvl  = [];
			$dataLvl[0] = Database::readAll("app_users_grouplist","WHERE `level`=0");
			$dataLvl[1] = Database::readAll("app_users_grouplist","WHERE `level`=1");
			$dataLvl[2] = Database::readAll("app_users_grouplist","WHERE `level`=2");
			$dataLvl[3] = Database::readAll("app_users_grouplist","WHERE `level`=3");
			ob_start();
			?>
			<style>
			.user_card{
				font-size:9pt;
				float:left;
				padding:12px;
				cursor:pointer;
			}
			.user_card:before{
				font-family:"Font Awesome 5 Free";
				font-weight: 700;
				content:"\f007";
				margin-right:10px;
			}
			.group_card:before{
				font-family:"Font Awesome 5 Free";
				font-weight: 700;
				content:"\f0c0"!important;
				margin-right:10px;
			}
			.group_card{
				color:black!important;
			}
			.ugitem:hover{
				border-bottom:none!important;
			}
			</style>
			<?php $t1 = Minifier::outCSSMin(); ob_start();?>
			<script>
			$(document).on("click","button.uglb_trig",function(){
				var btn = $(this);
				hideMessage();
				showMessage($("#groupListSystem").text(),"info","GroupSel",false);
				$(".ugsel").attr("inputid",btn.attr("inputid"));
				switch(btn.attr("level")){
					case "0":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=1]").remove();
					case "1":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=2]").remove();
					case "2":
						$(".ugsel[inputid=" + btn.attr("inputid") + "] .ugitem[level=3]").remove();
					case "3":
					break;
				}
				$(".ugsel[inputid=" + btn.attr("inputid") + "] .group_card").on("click",function(){
					hideMessage();
					$("#" + $(this).parent().attr("inputid")).val($(this).attr("uid")).trigger("change");
					$("#UGLB_" + $(this).parent().attr("inputid")).text($(this).text());
				});
			});
			</script>
			<?php $t2 = Minifier::outJSMin(); ob_start(); echo $t1; echo $t2;?>
			<div id="groupListSystem" style="display:none!important;">
				<div>Select User Group</div>
				<div inputid="" class="ugsel" style="max-height:250px;overflow:auto;">
				<?php
				foreach($dataLvl[0] as $d){
					echo('<div level="0" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="1" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[1] as $d){
					echo('<div level="1" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="2" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[2] as $d){
					echo('<div level="2" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div level="3" style="clear:both;" class="ugitem"></div>
				<?php
				foreach($dataLvl[3] as $d){
					echo('<div level="3" uid="'.$d["id"].'" class="ugitem group_card user_card material_card ripple">'.$d["name"].'</div>');
				}
				?>
				<div style="clear:both;"></div>
				</div>
			</div>
			<?php Template::appendBody(ob_get_clean());
			unset($dataLvl);
		}
		define("__UGLB_OUT",1);
		?>
		<input type="hidden" class="usergroup-input" name="<?php echo $input_name?>" id="<?php echo $input_name?>" value="<?php echo $group?>">
		<button level="<?php echo $level_option?>" inputid="<?php echo $input_name?>" type="button" class="uglb_trig btn btn-secondary btn-sm dropdown-toggle" style="border-radius:50px;">
			<span id="UGLB_<?php echo $input_name?>"><?php h(PuzzleUserGroup::get($group)->name)?></span> <span class="caret"></span>
		</button>
		<?php
	},
	"group_dropdown" => function($value = null, $class = null){
		$g = PuzzleUserGroup::getList();
		?>
		<select class="custom-select <?php h($class)?>">
			<optgroup label="Superuser">
				<?php foreach($g[USER_AUTH_SU] as $r):?>
				<option value="<?php h($r->id)?>" <?php if($value && $value == $r->id) echo 'selected'?>><?php h($r->name)?></option>
				<?php endforeach?>
			</optgroup>
			<optgroup label="Employee">
				<?php foreach($g[USER_AUTH_EMPLOYEE] as $r):?>
				<option value="<?php h($r->id)?>" <?php if($value && $value == $r->id) echo 'selected'?>><?php h($r->name)?></option>
				<?php endforeach?>
			</optgroup>
			<optgroup label="Registered">
				<?php foreach($g[USER_AUTH_REGISTERED] as $r):?>
				<option value="<?php h($r->id)?>" <?php if($value && $value == $r->id) echo 'selected'?>><?php h($r->name)?></option>
				<?php endforeach?>
			</optgroup>
			<optgroup label="Public">
				<?php foreach($g[USER_AUTH_PUBLIC] as $r):?>
				<option value="<?php h($r->id)?>" <?php if($value && $value == $r->id) echo 'selected'?>><?php h($r->name)?></option>
				<?php endforeach?>
			</optgroup>
		</select>
		<?php
	}
];

call_user_func_array($func[$arguments[0]],$arguments[1]);

return true;