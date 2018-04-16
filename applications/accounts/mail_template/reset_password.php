<?php 
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("1.2.3") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 1.1.1
 */

$language = new Language;
if(Accounts::$customET_RP === NULL):
?>
<p>Hi <?php echo Database::read("app_users_list","name","email",$_POST['email'])?>!</p>
<p><?php $language->dump("E20")?><strong><a href="<?php echo $link?>">[<?php $language->dump("e12")?>]</a></strong> <?php $language->dump("e13")?> <strong><?php $language->dump("e14")?>&nbsp;</strong><?php $language->dump("e15")?></p>
<p><?php $language->dump("e16")?></p>
<?php 
else: 
echo str_replace("{link}",$link,str_replace("{name}",Database::read("app_users_list","name","email",$_POST['email']),Accounts::$customET_RP));
endif;
?>