<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$language = new Language; $language->app = "users";
?>
<div class="container">
    <div style="max-width:600px;">
        <h2><?php $language->dump("c_pass")?></h2><br>
        <form action="<?php echo __SITEURL?>/users/changepassword" method="post">			
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input name="passold" required autofocus type="password" class="form-control" placeholder="<?php $language->dump("old_pass")?>" >
            </div><br>		
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input type="hidden" name="datafromresetpassafterverify" value="ok">
                <input name="passnew" required type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" >
            </div><br>		
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input name="passver" required type="password" class="form-control" placeholder="<?php $language->dump("ver_pass")?>" >
            </div><br>		
            <input type="hidden" name="realcpass" value="1">
            <button type="submit" class="btn btn-primary"><?php $language->dump("c_pass")?></button>
        </form>		
    </div>
</div>