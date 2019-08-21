<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

if (PuzzleUser::isAccess(USER_AUTH_REGISTERED)) :
    $language = new Language();
    ?>
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle" style="
        color:#000;
        font-size: 11pt;
        max-width: inherit;
        border-radius: 0px;
        z-index: 1000;
        background-color: white;
        border: none;
        box-shadow: 0 2px 4px 0 #909090, 0 -3px 1px 0 #909090;
        padding: 10px 15px;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        margin-left: 10px;" type="button" data-toggle="dropdown"><?php h(PuzzleUser::active()->fullname) ?></button>
    <div class="dropdown-menu dropdown-menu-right">
        <?php include "mini-login-dropdown.php" ?>
    </div>
</div>

<?php
endif;
