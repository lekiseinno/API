<?php

header('Content-type: application/json');

include 'route.php';

$route = new Route();


$route->add('/auth', function() {


	include_once('_connection/_connect_mysqli.r');

	$sql	=	"
				SELECT	*
				FROM	users
				WHERE	(
						users_username	=	'".$_POST["username"]."'
						AND
						users_password	=	'".md5($_POST["password"])."'
						)
				";
	$query	=	mysqli_query($connect,$sql) or die( ' SQL Error = '.$sql.'<hr><pre>'.print_r(mysqli_error($connect)).'</pre>');
	$row	=	mysqli_fetch_array($query,MYSQLI_ASSOC);

	echo json_encode($row);
});







$route->submit();
