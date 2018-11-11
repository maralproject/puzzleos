<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

if(Accounts::$customET_RP === NULL):?>
<p>Hi <?php echo Database::read("app_users_list","name","email",$_POST['email'])?>!</p>
<p><?php $language->dump("E20")?><strong><a href="<?php echo $link?>">[<?php $language->dump("e12")?>]</a></strong> <?php $language->dump("e13")?> <strong><?php $language->dump("e14")?>&nbsp;</strong><?php $language->dump("e15")?></p>
<p><?php $language->dump("e16")?></p>
<?php 
else:
echo str_replace(
	["{link}","{name}"],
	[$link,Database::read("app_users_list","name","email",$email)],
	Accounts::$customET_CE
);
endif;