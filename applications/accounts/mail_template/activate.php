<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

if(Accounts::$customET_AC === NULL):?>
<p>Hi <?php echo $_SESSION['account']['name']?>!</p>
<p><?php $language->dump("e18")?></p>
<p><?php $language->dump("e11")?> <a href="<?php echo $link?>">[<strong><?php $language->dump("e12")?></strong>]</a> <?php $language->dump("e13")?> <strong><?php $language->dump("e14")?>&nbsp;</strong><?php $language->dump("e15")?></p>
<p><?php $language->dump("e16")?></p>
<?php 
else: 
echo str_replace(
	["{email}","{link}","{name}"],
	[$email,$link,Database::read("app_users_list","name","email",$email)],
	Accounts::$customET_AC
);
endif;