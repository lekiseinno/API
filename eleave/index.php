<?php

header('Content-type: application/json');

include 'route.php';
include '_connection/_connect_srvsql.r';

$route	=	new Route();



$route->add('/get_data',	function() { 
	echo get_data_user();
});

$route->submit();




function get_data_user()
{
	global	$connect;

	$sql	=	"
				SELECT	TOP	5	emp_code
				FROM	[LKS_HR_Eleaves].[dbo].[employees]
				";
	$query	=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]		=	$row['emp_code'];
		//$data	=	$row['emp_code'];
	}
	echo json_encode($data);
}
?>


