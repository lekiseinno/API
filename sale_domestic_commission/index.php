<?php

header('Content-type: application/json');

include 'route.php';
include '_connection/_connect_srvsql.r';

$route	=	new Route();

$route->add('/auth', function() {

	global	$connect;

	$sql	=	"
				SELECT	*
				FROM	[LKS_DOMESTIC_SALE].[dbo].[user_login]
				WHERE	(
						username	=	'".$_POST["username"]."'
						AND
						password	=	'".md5($_POST["password"])."'
						)
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	echo json_encode($row);
});


$route->add('/get_data_user',	function() { 
	echo get_data_user($id);
});
$route->add('/post_data_user',	function() {
	echo post_data_user($id);
});


$route->add('/get_data',		function() { echo get_data_json(); });
$route->add('/get_data_sale',	function() { echo get_data_json_sale($_POST['id']); });
$route->add('/update_data',		function() { echo update_data(); });
$route->add('/count_data',		function() {

	$data_count			=	json_decode(count_data(), true);
	$data_count_json	=	count(count_data_json()).PHP_EOL;

	if($data_count[counts]	!=	$data_count_json)
	{
		echo update_data();
	}
	else
	{
		echo "Last Update : ".substr(max(count_data_json()),0, 10);
	}
});


$route->submit();





function get_data_user($id)
{
	global	$connect;

	$sql	=	"
				SELECT	*
				FROM	[LKS_DOMESTIC_SALE].[dbo].[user_login]
				WHERE	username	=	'".$id."'
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	echo json_encode($row);
}

function post_data_user($id)
{
	global	$connect;

	$sql	=	"
				UPDATE	[dbo].[user_login]
					SET [salename] = '',
						[username] = '',
						[password] = ''
				WHERE	[salecode] = '".$id."'
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	echo json_encode($row);
}






function update_data()
{
	global	$connect;

	$sql	=	"
				SELECT		C.code, C.Name, A.[Order Date], B.Amount
				FROM		[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Sales Invoice Header]		A
				INNER JOIN	[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Sales Invoice Line]			B	ON	B.[Document No_]	=	A.[No_]
				INNER JOIN	[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Salesperson_Purchaser]		C	ON	C.[code]			=	A.[Salesperson Code]
				WHERE		A.[Order Date]	> '2018-01-01'
				ORDER BY	A.[Order Date] ASC
				";
	$query	=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	$fp = fopen('data.json', 'w');
	fwrite($fp, json_encode($data));
	fclose($fp);

	return 'success';
	
}


function count_data()
{
	global	$connect;

	$sql	=	"
				SELECT		COUNT(*)	as	'counts'
				FROM		[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Sales Invoice Header]		A
				INNER JOIN	[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Sales Invoice Line]			B	ON	B.[Document No_]	=	A.[No_]
				INNER JOIN	[10.10.2.9].[LKS-GO-LIVE].[dbo].[บริษัท ลี้ กิจเจริญแสง จำกัด\$Salesperson_Purchaser]		C	ON	C.[code]			=	A.[Salesperson Code]
				WHERE		[Order Date]	> '2018-01-01'
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	return json_encode($row);
}

function count_data_json()
{
	$json_data	=	file_get_contents('data.json');
	$json		=	json_decode($json_data, true);
	
	foreach($json as $val)
	{
		$datemax[]	=	$val['Order Date']['date'];
	}
	return $datemax;
}


function get_data_json()
{
	return	$json_data	=	file_get_contents('data.json');
}



function get_data_json_sale($id)
{
	$json_data	=	file_get_contents('data.json');
	$json 		= 	json_decode( $json_data , true );

	foreach($json as $val){

		if($id	==	$val[code])
		{

			$value['code']		=	$val['code'];
			$value['name']		=	$val['Name'];
			$value['Amount']	+=	$val['Amount'];

		}
	}
	$data=array(	
					'code'		=>	$value['code'],
					'name'		=>	$value['name'],
					'Amount'	=>	$value['Amount']
				);

	echo json_encode($data) ;
	
}










?>


