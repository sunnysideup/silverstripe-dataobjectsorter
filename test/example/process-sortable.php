<?php
/* This is where you would inject your sql into the database 
   but we're just going to format it and send it back
*/

foreach ($_GET['listItem'] as $position => $item) :
	$sql[] = "UPDATE `table` SET `position` = $position WHERE `id` = $item";
endforeach;

print_r ($sql);
?>