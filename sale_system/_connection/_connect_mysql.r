<?

$host			=	"localhost";		//HOST NAME
$userhost		=	"root";				//Username Login DATABASE - MYSQL
$passhost		=	"LKG@Pa$$w0rd20i7";			//Password Login DATABASE - MYSQL
$dbname			=	"ecatalog_auth";			//NAME OF DATA BASE TO USE

mysql_connect("$host","$userhost","$passhost");
mysql_query("SET NAMES UTF8");
mysql_select_db("$dbname");

?>