<?
	date_default_timezone_set('Asia/Bangkok');

	$host			=	"localhost";				//HOST NAME
	$userhost		=	"root";						//Username Login DATABASE - MYSQL
	$passhost		=	"123456";					//Password Login DATABASE - MYSQL
	$dbname			=	"ecatalog_auth";			//NAME OF DATA BASE TO USE

	$connect		=	mysqli_connect("$host", "$userhost", "$passhost", "$dbname");

	if (!$connect) {
		echo "Error: Unable to connect to MySQL." . PHP_EOL;
		echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
		exit;
	}
	//mysqli_close($connect);
?>