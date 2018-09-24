<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

if(Accounts::authAccess(USER_AUTH_REGISTERED)):

/**
 * This file is part of menus
 */
?>
<?php $language = new Language; $language->app = "users";?>
<div class="btn-group" style="max-width:inherit;">
 <button type="button" class="dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="max-width:inherit;border-radius:0px;z-index:1000;background-color:white;border:none;-webkit-box-shadow:0px 0px 12px 0px rgba(0,0,0,0.40);-moz-box-shadow: 0px 0px 12px 0px rgba(0,0,0,0.40);box-shadow:0px 0px 12px 0px rgba(0,0,0,0.40);padding:10px 15px;border-bottom-left-radius:5px;border-bottom-right-radius:5px;margin-left:10px;">
    <div style="float:right;"><span class="caret"></span></div><div style="max-width:inherit;overflow:hidden;text-overflow:ellipsis;"><?php echo $_SESSION['account']['name'];?></div>
  </button>
  <ul class="dropdown-menu dropdown-menu-right">
    <li><a href="<?php echo __SITEURL?>/users/changepassword"><?php $language->dump("c_pass"); ?></a></li>
    <li><a href="<?php echo __SITEURL?>/users/profile"><?php $language->dump("a_set"); ?></a></li>
    <li role="separator" class="divider"></li>
    <li><a href="<?php echo __SITEURL?>/users/logout"><?php $language->dump("logout"); ?></a></li>
  </ul>
</div>

<?php endif;?>