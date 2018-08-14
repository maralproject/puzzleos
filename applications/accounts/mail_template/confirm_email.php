<?php 
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.users
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */

$language = new Language;
if(Accounts::$customET_CE === NULL):
?>
<p>Hi <?php echo $_SESSION['account']['name']?>!</p>
<?php if(!$new_account):?>
<p><?php $language->dump("e10")?> <?php echo $_POST['email']?></p>
<?php else:?>
<p><?php $language->dump("e17")?></p>
<?php endif?>
<p><?php $language->dump("e11")?> <a href="<?php echo $link?>">[<strong><?php $language->dump("e12")?></strong>]</a> <?php $language->dump("e13")?> <strong><?php $language->dump("e14")?>&nbsp;</strong><?php $language->dump("e15")?></p>
<p><?php $language->dump("e16")?></p>
<?php 
else: 
echo str_replace(
	["{email}","{link}","{name}"],
	[$_POST["email"],$link,$_SESSION["account"]["name"]],
	Accounts::$customET_CE
);
endif;
?>