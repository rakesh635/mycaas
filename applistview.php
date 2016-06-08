<?php
	include "lib/class.app.php";
	include "conf/variables.php";
	$app = new appClass();
	$appListArray = $app->applist();
?>
<table>
	<tr>
		<th> App Id </th>
		<th> App Name </th>
		<th> Git Repo </th>
		<th> Status </th>
		<th> Created Date </th>
		<th> Updated Date </th>
		<th> Completed Date </th>
	</tr>
<?php
	foreach($appListArray as $appList=>$appListVal)
	{
			echo("<tr><td>".$appListVal['app_id']."</td><td>".$appListVal['app_name']."</td><td>".$appListVal['git_url']."</td><td>".$app_status[$appListVal['status']]."</td><td>".$appListVal['created_date']."</td><td>".$appListVal['updated_date']."</td><td>".$appListVal['completed_date']."</td></tr>");
	}
?>