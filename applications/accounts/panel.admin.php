<?php include "initjs.php" ?>
<h1>User Account</h1><br>
<div>
	<ul class="nav nav-tabs">
		<li class="nav-item">
			<a class="nav-link active" data-toggle="tab" href="#uusers">
				<i style="margin-right:5px" class="fa fa-user"></i>Users
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#ugroups">
				<i style="margin-right:5px" class="fa fa-users"></i>Groups
			</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" data-toggle="tab" href="#usettings">
				<i style="margin-right:5px" class="fa fa-wrench"></i>Settings
			</a>
		</li>
	</ul>
	<div style="clear:both;height:25px;"></div>
	<div class="tab-content" style="padding-bottom:15vh;">
		<div id="uusers" class="tab-pane active">
			<?php include "admin/user.php" ?>
		</div>
		<div id="ugroups" class="tab-pane">
			<?php include "admin/group.php" ?>
		</div>
		<div id="usettings" class="tab-pane">
			<?php include "admin/config.php" ?>
		</div>
	</div>
</div>