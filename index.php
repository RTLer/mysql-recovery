<?php
session_start();
$filePath = 'bigsqldb.sql';
// MySQL database info
$mysql_host = "127.0.0.1";
$mysql_username = "root";
$mysql_password = "";
$mysql_database = "bigsqldb";


if(!isset($_SESSION['dbb_Stats'])){
	$querylist=array();
	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($filePath);
	// Loop through each line
	foreach ($lines as $line)
	{
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;

		// Add this line to the current segment
		$templine .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';')
		{
			// add the query
			array_push($querylist, $templine);
			// Reset temp variable to empty
			$templine = '';
		}
	}
	$_SESSION['dbb_querylist'] = $querylist;
	$_SESSION['dbb_Stats'] = 'set_querylist';?>
	<script>
	 window.location.href = "<?php echo$_SERVER["REQUEST_URI"]?>";
	</script>
	<?php
}elseif($_SESSION['dbb_Stats'] == 'set_querylist'){
	function secend(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	$timeLimit = ini_get('max_execution_time')-5; 
	$start = secend();
	// Connect to MySQL server
	$link = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database) or die('error on connecting to database<br/>');
	mysqli_query($link,"SET NAMES utf8");
	mysqli_query($link,"SET CHARACTER_SET utf8");
	foreach($_SESSION['dbb_querylist'] as $queryKey => $queryValue){
		mysqli_query($link,$queryValue) 
			or print('<strong>error on recovering query: \'</strong>' . mysqli_error($link) . '<br /><br />');
		array_splice($_SESSION['dbb_querylist'],$queryKey,0);
		if (secend()-$start >= $timeLimit){
			?>
			<script>
			 window.location.href = "<?php echo $_SERVER["REQUEST_URI"]?>";
			</script>
			<?php
			break;
		}
	}
	echo "recovery finished";
	unset($_SESSION['dbb_Stats'], $_SESSION['dbb_querylist']);
}
?>