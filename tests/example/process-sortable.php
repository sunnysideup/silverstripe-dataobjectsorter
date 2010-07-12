<?php
/* This is where you would inject your sql into the database
   but we're just going to format it and send it back
*/
$bt = defined('DB::USE_ANSI_SQL') ? "\"" : "`";
foreach ($_GET['listItem'] as $position => $item) :

	$sql[] = "UPDATE {$bt}table{$bt} SET {$bt}position{$bt} = $position WHERE {$bt}id{$bt} = $item";
endforeach;

print_r ($sql);
?>
