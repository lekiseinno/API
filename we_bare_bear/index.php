<?php

header('Content-type: application/json');

include	'route.php';
include	'_connection/_connect_srvsql.r';

$route	=	new Route();

$route->add('/get_data',	function()	{	echo	get_data($_GET['f']);	});

$route->submit();

function get_data($food)
{
	global	$connect;
	$sql	=	"
				SELECT	*
				FROM	[we_bare_bear].[dbo].[data_Foods]
				WHERE	[food_Name] LIKE '".$food."%'
				";
	$query	=	sqlsrv_query($connect,$sql);
	$query	=	sqlsrv_query($connect, $sql, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET )) or die( 'SQL Error = '.$sql1.'<hr><pre>'. print_r( sqlsrv_errors(), true) . '</pre>');
	$nums	=	sqlsrv_num_rows($query);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	if($nums	==	0)
	{
		$rows	=	array('หมีหา '.$food.' ไม่เจอคับ');
	}
	else
	{
		$rows	=	$row;
	}
	echo	json_encode($rows);
	
}
/*
function get_data_json()
{
	return	$json_data	=	file_get_contents('data.json');
}
*/
