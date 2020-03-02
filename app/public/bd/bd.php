<?
include('setup.php');
$db = mysqli_connect($localhost,$user_db,$pass_db,$name_db);

mysqli_query ($db,'SET NAMES "UTF8"');
mysqli_query ($db,'SET collation_connection="utf8_general_ci"');
mysqli_query ($db,'SET collation_server="utf8_general_ci"');
mysqli_query ($db,'SET character_set_client="utf8"');
mysqli_query ($db,'SET character_set_connection="utf8"');
mysqli_query ($db,'SET character_set_results="utf8"');
mysqli_query ($db,'SET character_set_server="utf8"');

?>