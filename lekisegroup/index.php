<?php
session_start();

header('Content-type: application/json');

include 'route.php';
include '_connection/_connect_srvsql.r';

$route	=	new Route();

$route->add('/auth', function() {

	echo fn_auth0($_POST['signinusername'],$_POST['signinpassword'],'appname');

});
$route->submit();
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */



/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */




function fn_auth0($nameuser,$wordpass,$appname)
{
	global	$connect;
	$sql	=	"
				SELECT	*
				FROM		[LeKise_Group].[dbo].[Employees_login]
				INNER JOIN	[LeKise_Group].[dbo].[Employees]					ON	[LeKise_Group].[dbo].[Employees].[emp_code]			=	[LeKise_Group].[dbo].[Employees_login].[emp_code]
				INNER JOIN	[LeKise_Group].[dbo].[company]						ON	[LeKise_Group].[dbo].[Employees].[company_code]		=	[LeKise_Group].[dbo].[company].[company_Code]
				INNER JOIN	[LeKise_Group].[dbo].[department]					ON	[LeKise_Group].[dbo].[Employees].[company_code]		=	[LeKise_Group].[dbo].[department].[company_Code]	AND	[LeKise_Group].[dbo].[department].[department_Code]	=	[LeKise_Group].[dbo].[Employees].[department_code]
				INNER JOIN	[LeKise_Group].[dbo].[division]						ON	[LeKise_Group].[dbo].[Employees].[company_code]		=	[LeKise_Group].[dbo].[division].[company_Code]		AND	[LeKise_Group].[dbo].[division].[division_Code]		=	[LeKise_Group].[dbo].[Employees].[division_code]
				INNER JOIN	[LeKise_Group].[dbo].[section]						ON	[LeKise_Group].[dbo].[Employees].[company_code]		=	[LeKise_Group].[dbo].[section].[company_Code]		AND	[LeKise_Group].[dbo].[section].[section_Code]		=	[LeKise_Group].[dbo].[Employees].[section_code]
				INNER JOIN	[LeKise_Group].[dbo].[position]						ON	[LeKise_Group].[dbo].[Employees].[company_code]		=	[LeKise_Group].[dbo].[position].[company_Code]		AND	[LeKise_Group].[dbo].[position].[position_Code]		=	[LeKise_Group].[dbo].[Employees].[position_code]
				INNER JOIN	[LeKise_Group].[dbo].[Employees_Auth0_Application]	ON	[LeKise_Group].[dbo].[Employees].[emp_code]			=	[LeKise_Group].[dbo].[Employees_Auth0_Application].[emp_code]
				WHERE		(
							[LeKise_Group].[dbo].[Employees_login].[emp_code]		=	'".$nameuser."'
							AND
							[LeKise_Group].[dbo].[Employees_login].[emp_password]	=	'".strtoupper(MD5($wordpass))."'
							)
				";
	$query	=	sqlsrv_query($connect,$sql)	or die( 'SQL Error = '.$sql.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	save_log($nameuser,$appname);
	return json_encode($row);
}


function save_log($nameuser,$appname)
{
	global	$connect;
	$sql	=	"
				INSERT INTO	[LeKise_Group].[dbo].[log_login]VALUES
				(
					'".$nameuser."',
					'".date('Y-m-d')."',
					'".date('H:i:s')."',
					'".$ua['platform']."',
					'".$devices."',
					'API-auth0',
					'".$ua['name']."',
					'".$ua['version']."',
					'".$appname."',	/*WTL_POS,http://lekise.info*/
					'".$ips."',
					'Login Success'
				);
				";
	$query	=	sqlsrv_query($connect,$sql) or die( 'SQL Error = '.$sql.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	return	'success';
}