<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

$language = new Language;
?>
<div style="display:table;width:100%;height:100%;max-width:480px;margin: auto;">
    <div id="loginCtn" style="display:table-cell;vertical-align:middle;padding:20px;">
        <div style="font-size:20pt;font-weight:500;margin-bottom:15px;"><?php $language->dump("c_pass")?></div>
        <form action="<?php echo __SITEURL?>/users/changepassword" method="post" style="text-align:center;">
            <input type="hidden" name="datafromresetpassafterverify" value="ok"><br>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input name="passnew" autofocus type="password" class="form-control" placeholder="<?php $language->dump("new_pass")?>" required>
            </div><br>		
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fa fa-key"></i></span>
                </div>
                <input name="passver" type="password" class="form-control" placeholder="<?php $language->dump("ver_pass")?>" required>
            </div><br>	
            <input type="hidden" name="realcpass" value="1"> 
            <button type="submit" class="btn btn-secondary"><?php $language->dump("c_pass")?></button><br><br>
        </form>
    </div>
</div>
