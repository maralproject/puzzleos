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
?>
<p>Hi <?php echo $_SESSION['account']['name']?>!</p>
<p><?php $language->dump("e10")?> <?php echo $_POST['email']?></p>
<p><?php $language->dump("e11")?> <a href="<?php echo $link?>">[<strong><?php $language->dump("e12")?></strong>]</a> <?php $language->dump("e13")?> <strong><?php $language->dump("e14")?>&nbsp;</strong><?php $language->dump("e15")?></p>
<p><?php $language->dump("e16")?></p>
