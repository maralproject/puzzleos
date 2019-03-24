<?php 
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

if(Accounts::$customET_AC === NULL):?>
<p>Hi <?php echo $user['name']?>!</p>
<p><?php $language->dump("TFA_MAIL1")?></p>
<p style="font-size:24px;letter-spacing:1px;"><?php echo $challengeCode?></p>
<?php 
else: 
echo str_replace(
	["{code}","{name}"],
	[$challengeCode, $user["name"]],
	Accounts::$customET_AC
);
endif;