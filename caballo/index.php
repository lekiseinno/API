<?php
session_start();

header('Content-type: application/json');

include 'route.php';
include '_connection/_connect_srvsql.r';

$route	=	new Route();

$route->add('/products/syncdata', function() {
	echo syncdata();
});

$route->add('/customers', function() {
	echo get_customer($_GET['q']);
});
$route->add('/employees', function() {
	echo get_employee($_GET['q']);
});
$route->add('/products', function() {
	echo get_products($_GET['q']);
});
$route->add('/orders_list', function() {
	echo get_orders_list($_GET['q']);
});
$route->add('/orders_sum', function() {
	echo get_orders_sum($_GET['q']);
});
$route->add('/auth', function() {
	echo post_auth($_POST['emp_username'],$_POST['emp_password']);
});
$route->add('/addtocart', function() {
	echo addtocart($_POST['session'],$_POST['item_No'],$_POST['item_Desc'],$_POST['item_Qty']);
});
$route->add('/updatetocart', function() {
	echo updatecart($_POST['session'],$_POST['item_No'],$_POST['item_Qty']);
});
$route->add('/deletetocart', function() {
	echo deletecart($_POST['session'],$_POST['item_No']);
});
$route->add('/deletesession', function() {
	echo deletesession($_POST['session']);
});
$route->add('/getitemcart', function() {
	echo getitemcart($_POST['session']);
});
$route->add('/confirm_orders', function() {
	echo confirm_orders($_POST['session'],$_POST['customer_code'],$_POST['employee_code']);
});



/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */
/* --------------------------------------------------------------------------- */



/* Pattern  */
$route->add('/pattern/get_all', function() {
	echo get_pattern_all();
});
/* Products  */
$route->add('/products/get_all', function() {
	echo get_products_all();
});

$route->add('/products/get_img_all', function() {
	echo get_products_img_all();
});

$route->add('/products/post_image', function() {
	echo post_products_image($_GET['Item_Image_pattern_No']);
});

$route->add('/products/search/post', function() {
	echo post_search($_POST['Item_No']);
});

$route->add('/products/get_screen', function() {
	echo post_products_screen($_POST['Item_screen'],$_POST['Item_Category_Code'],$_POST['text']);
});

$route->add('/products/group_size', function() {
	echo post_group_size($_POST['Item_screen'],$_POST['Item_Category_Code']);
});

$route->add('/products/group_color', function() {
	echo post_group_color($_POST['Item_screen'],$_POST['Item_Category_Code']);
});

$route->add('/products/group_type', function() {
	echo post_group_type($_POST['Item_screen'],$_POST['Item_Category_Code']);
});

$route->submit();


function post_auth($emp_username,$emp_password)
{
	global	$connect;
	$sql	=	"
				SELECT	*
				FROM	[CBL-POS].[dbo].[employees]
				WHERE	(
						emp_username		=	'".$emp_username."'
						AND
						emp_password	   =	'".MD5($emp_password)."'
						)
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	save_log($emp_username,'auth username = "'.$emp_username.'"| ','post_auth');
	echo json_encode($row);
}

function get_customer($q)
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM	[customers]
					WHERE	(
								[Customer_code]		LIKE	'%".$q."%'	OR
								[Customer_FName]	LIKE	'%".$q."%'	OR
								[Customer_LName]	LIKE	'%".$q."%'	OR
								[Customer_Tel]		LIKE	'%".$q."%'	OR
								[Customer_Email]	LIKE	'%".$q."%'
							)
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function get_employee($q)
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[employees]
					WHERE	[CBL-POS].[dbo].[employees].[emp_code]	=	'".$q."'
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function get_products($id)
{
	global	$connect;
	$sql	=	"
				SELECT	*
				FROM	[CBL-POS].[dbo].[item\$]
				WHERE	[CBL-POS].[dbo].[item\$].[No_]	LIKE	'%".$id."%'
				";
	$query	=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function get_orders_list($q)
{
	global	$connect;
	$sql		=	"
					SELECT	[Orders_No]
					FROM	[Orders]
					WHERE	[Orders].[Orders_No]	LIKE	'%".$q."%'
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
		$sqls		=	"
						SELECT		*
						FROM		[Orders_detail]
						WHERE		[Orders_detail].[Orders_No]	=	'".$row['Orders_No']."'
						";
		$querys		=	sqlsrv_query($connect,$sqls);
		while($rows	=	sqlsrv_fetch_array($querys,SQLSRV_FETCH_ASSOC))
		{
			$datas[$row['Orders_No']][]	=	$rows;
		}
	}
	return json_encode($datas);
}

function get_orders_sum($q)
{
	global	$connect;
	$sql	=	"
				
				SELECT 		[Orders].[Orders_No],
							[Orders].[emp_code],
							[employees].[emp_name],
							[customers].[Customer_code],
							CONCAT([customers].[Customer_FName],' ',[customers].[Customer_LName]) as 'Customer_Name',
							SUM([Orders_detail].[Orders_detail_Qty]) as 'Orders_Qty',
							SUM([Orders_detail].[Orders_detail_Price]) as 'Orders_Price',
							[Orders].[Orders_Status],
							[Orders].[Orders_Date]
				FROM		[Orders]
				INNER JOIN	[Orders_detail]	ON	[Orders_detail].[Orders_No]	=	[Orders].[Orders_No]
				INNER JOIN	[employees]		ON	[employees].[emp_code]		=	[Orders].[emp_code]
				INNER JOIN	[customers]		ON	[customers].[Customer_code]	=	[Orders].[Customer_code]
				WHERE		[Orders].[Orders_No]	LIKE	'%".$q."%'
				GROUP BY	[Orders].[Orders_No],
							[Orders].[emp_code],
							[employees].[emp_name],
							[customers].[Customer_code],
							[customers].[Customer_FName],
							[customers].[Customer_LName],
							[Orders].[Orders_Status],
							[Orders].[Orders_Date]
				";
	$query		=	sqlsrv_query($connect,$sql) or die( 'SQL Error = '.$sql.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');;
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function addtocart($session,$item_no,$item_desc,$item_qty)
{
	global	$connect;
	$sql_check_seq		=	"
							SELECT	MAX([dbo].[Orders_tmp].[Orders_tmp_seq]) as 'max'
							FROM	[dbo].[Orders_tmp]
							WHERE	[dbo].[Orders_tmp].[Orders_tmp_session]	=	'".$session."'
							";
	$query_check_seq	=	sqlsrv_query($connect,$sql_check_seq) or die( 'SQL Error = '.$sql_check_seq.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$row_check_seq		=	sqlsrv_fetch_array($query_check_seq);
	$sql_tmp			=	"
							INSERT INTO [dbo].[Orders_tmp] ([Orders_tmp_seq],[Orders_tmp_session],[Orders_tmp_Item_No],[Orders_tmp_Descriptions],[Orders_tmp_Qty],[Orders_tmp_Price],[Orders_tmp_Status],[Orders_Date],[Orders_Time])
							VALUES
							(
								'".($row_check_seq['max']+1)."',
								'".$session."',
								'".$item_no."',
								'".$item_desc."',
								'".$item_qty."',
								'',
								'in cart',
								GETDATE(),
								GETDATE()
							)
							";
	$query_tmp	=	sqlsrv_query($connect,$sql_tmp) or die( 'SQL Error = '.$sql_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	save_log($session,'Add new item to cart ON session = "'.$session.'"','addtocart');
	return 'success';
}

function updatecart($session,$item_no,$item_qty)
{
	global	$connect;
	$sql_tmp	=	"
					UPDATE	[dbo].[Orders_tmp]	SET
							[Orders_tmp_Qty]		=	'".$item_qty."'
					WHERE	[Orders_tmp_session]	=	'".$session."'
					AND		[Orders_tmp_Item_No]	=	'".$item_no."'
					";
	$query_tmp	=	sqlsrv_query($connect,$sql_tmp) or die( 'SQL Error = '.$sql_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	save_log($session,'Update item in intemcart ON "'.$item_no.'" @ "'.$session.'"','updatecart');
	return 'success';
}

function deletecart($session,$item_no)
{
	global	$connect;
	$sql_tmp	=	"
					DELETE FROM	[dbo].[Orders_tmp]
					WHERE		[Orders_tmp_session]	=	'".$session."'
					AND			[Orders_tmp_Item_No]	=	'".$item_no."'
					";
	$query_tmp	=	sqlsrv_query($connect,$sql_tmp) or die( 'SQL Error = '.$sql_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	save_log($session,'Delete item in intemcart ON "'.$item_no.'" @ "'.$session.'"','deletecart');
	return 'success';
}

function deletesession($session)
{
	global	$connect;
	$sql_tmp	=	"
					DELETE FROM	[dbo].[Orders_tmp]
					WHERE		[Orders_tmp_session]	=	'".$session."'
					";
	$query_tmp	=	sqlsrv_query($connect,$sql_tmp) or die( 'SQL Error = '.$sql_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	save_log($session,'Delete item in intemcart ON "'.$session.'"','deletecart');
	return 'success';
}

function getitemcart($session)
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[Orders_tmp]
					WHERE	[dbo].[Orders_tmp].[Orders_tmp_session]	=	'".$session."'
					AND		Orders_tmp_Status	=	'in cart'
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	save_log($session,'Get item in intemcart ON "'.$item_no.'"','getitemcart');
	return json_encode($data);
}

function confirm_orders($session,$customers,$employee)
{
	global	$connect;
	$sql_get_doc_no		=	"
							SELECT	COUNT(Orders_No) as 'Orders_No'
							FROM	[CBL-POS].[dbo].[Orders]
							WHERE	[CBL-POS].[dbo].[Orders].[Orders_Date]	LIKE	'".date('Y-m')."%'
							";
	$query_get_doc_no	=	sqlsrv_query($connect,$sql_get_doc_no) or die( 'SQL Error = '.$sql_get_doc_no.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$row_get_doc_no		=	sqlsrv_fetch_array($query_get_doc_no);
	$po_number			=	"PO-POS-".date('Ym')."-".sprintf("%03d",($row_get_doc_no['Orders_No']+1));
	$sql_head			=	"
							INSERT INTO	[dbo].[Orders] ([Orders_No],		[emp_code],			[Customer_code],	[Orders_Date],	[Orders_Time],	[Orders_VAT],	[Orders_Discount],	[Orders_Session],	[Orders_Status],	[datecreate],	[lastupdate])
							SELECT TOP 1				'".$po_number."',	'".$employee."',	'".$customers."',	[Orders_Date],	[Orders_Time],	'7',			'0',				'".$session."',		'New',				GETDATE(),		GETDATE()
							FROM		[dbo].[Orders_tmp]
							WHERE		Orders_tmp_session	=	'".$session."'
							AND			Orders_tmp_Status	=	'in cart'
							";
	$query_head			=	sqlsrv_query($connect,$sql_head) or die( 'SQL Error = '.$sql_head.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$sql_detail			=	"
							INSERT INTO	[dbo].[Orders_detail] (	[Orders_No],		[Item_No],				[Orders_detail_Price],		[Orders_detail_Qty],		[Orders_detail_Remark],		[Orders_detail_Sequence])
							SELECT								'".$po_number."',	[Orders_tmp_Item_No],	'',							[Orders_tmp_Qty],			'',							[Orders_tmp_seq]
							FROM		[dbo].[Orders_tmp]
							WHERE		Orders_tmp_session	=	'".$session."'
							AND			Orders_tmp_Status	=	'in cart'
							";
	$query_detail		=	sqlsrv_query($connect,$sql_detail) or die( 'SQL Error = '.$sql_detail.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$sql_clear_tmp		=	"
							UPDATE [dbo].[Orders_tmp]	SET
								[Orders_tmp_Status]		=	'Post to order'
							WHERE [Orders_tmp_session]	=	'".$session."'
							";
	$query_clear_tmp	=	sqlsrv_query($connect,$sql_clear_tmp) or die( 'SQL Error = '.$sql_clear_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	save_log($session,'sessoion = "'.$session.'" | Orders_No = "'.$po_number.'" | employee = "'.$employee.'" | customers = "'.$customers.'"','confirm_orders');
	return 'success';
}


/*
function confirm_orders_detail($session,$item_no,$item_qty,$item_price)
{
	global	$connect;
	$sql_get_doc_no		=	"
							SELECT	COUNT(Orders_No) as 'Orders_No'
							FROM	[CBL-POS].[dbo].[Orders]
							WHERE	[CBL-POS].[dbo].[Orders].[Orders_Date]	LIKE	'".date('Y-m')."%'
							";
	$query_get_doc_no	=	sqlsrv_query($connect,$sql_get_doc_no) or die( 'SQL Error = '.$sql_get_doc_no.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$row_get_doc_no		=	sqlsrv_fetch_array($query_get_doc_no);

	$sql_get_session	=	"
							SELECT		TOP 1 *
							FROM		[CBL-POS].[dbo].[Orders]
							ORDER BY	Orders_ID	DESC
							";
	$query_get_session	=	sqlsrv_query($connect,$sql_get_session) or die( 'SQL Error = '.$sql_get_session.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	$row_get_session	=	sqlsrv_fetch_array($query_get_session);

	$po_number	=	"PO-POS-".date('Ym')."-".sprintf("%03d",($row_get_doc_no['Orders_No']));

	for($i=0;$i<count($item_no);$i++)
	{
		$sql_order_detail[$i]	=	"
									INSERT INTO	[dbo].[Orders_detail]	([Orders_No],[Item_No],[Orders_detail_Price],[Orders_detail_Qty],[Orders_detail_Remark],[Orders_detail_Sequence])
									VALUES
									(
										'".$po_number."',
										'".$item_no[$i]."',
										'".$item_price[$i]."',
										'".$item_qty[$i]."',
										'remark is here',
										'".($i+1)."'
									)
									";
		$query_order_detail	=	sqlsrv_query($connect,$sql_order_detail[$i]) or die( 'SQL Error = '.$sql_order_detail[$i].'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	}

	
	$sql_clear_tmp		=	"
							UPDATE [dbo].[Orders_tmp]	SET
								[Orders_tmp_Status] = 'Post to order'
							WHERE [Orders_tmp_session]	=	'".$session."'
							";
	$query_clear_tmp	=	sqlsrv_query($connect,$sql_clear_tmp) or die( 'SQL Error = '.$sql_clear_tmp.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	return 'success';
	
}
*/


















function get_pattern_all()
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[Item_pattern]
					WHERE	(
								[Item_Pattern_Name]	=	'BLACK'	OR
								[Item_Pattern_Name]	=	'TD'	OR
								[Item_Pattern_Name]	=	'4DX'	OR
								[Item_Pattern_Name]	=	'CTD'	OR
								[Item_Pattern_Name]	=	'Kids'	OR
								[Item_Pattern_Name]	=	'PANTS'	OR
								[Item_Pattern_Name]	=	'HOOD'	OR
								[Item_Pattern_Name]	=	'SX'	OR
								[Item_Pattern_Name]	=	'BAG'	
							)
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function get_products_all()
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[item\$]
					";
	$query		=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function get_products_img_all()
{
	$dir					=	"D:/www_appserv/CBL-POS/images/pattern";
	if(is_dir($dir)){
		if ($directory		=	opendir($dir)){
			$index			=	0;
			while (($path	=	readdir($directory)) !== false) {
				if($index	>	1)
				{
					$dir2				=	$dir."/".$path;
					if(is_dir($dir2)){
						if ($directory2		=	opendir($dir2)){
							$index2			=	0;
							while (($path2	=	readdir($directory2)) !== false) {
								if($index2	>	1)
								{
									$img	=	explode("-", $path2);
									$data[$path][]	.=	$img[1];
								}
								$index2++;
							}
							closedir($directory2);
						}
					}
				}
				$index++;
			}
			closedir($directory);
		}
	}
	return json_encode($data);
}

function post_products_image($id)
{
	global	$connect;
	$sql		=	"
					SELECT	*
					FROM  [CBL-POS].[dbo].[Item_image]
					WHERE [CBL-POS].[dbo].[Item_image].[Item_Image_pattern_No] = '".$id."'
					";
	$query	=	sqlsrv_query($connect,$sql);
	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function post_products_search($id)
{
	global	$connect;
	$sql	=	"
				SELECT	*
				FROM	[CBL-POS].[dbo].[item\$]
				WHERE	[CBL-POS].[dbo].[item\$].[Item_No]	=	'".$id."'
				";
	$query	=	sqlsrv_query($connect,$sql);
	$row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC);
	return json_encode($row);
}

function post_products_screen($id,$id2,$id3)
{
	global	$connect;
	if($id3!="\"\"")
	{
		$sql	=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[item\$]
					WHERE	[CBL-POS].[dbo].[item\$].[Item_screen]			=	'".$id."'
					AND     [CBL-POS].[dbo].[item\$].[Manual Category Code]	=	'".$id2."' " .$id3;
	}
	else
	{
		$sql	=	"
					SELECT	*
					FROM	[CBL-POS].[dbo].[item\$]
					WHERE	[CBL-POS].[dbo].[item\$].[Item_screen]			=	'".$id."'
					AND     [CBL-POS].[dbo].[item\$].[Manual Category Code]	=	'".$id2."'
					";
	}
	$query	=	sqlsrv_query($connect,$sql);

	while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	$row;
	}
	return json_encode($data);
}

function post_group_type($id,$id2)
{
	global	$connect;
	$sql	=	"
					SELECT Item_Type_Code ,Item_Type_Name FROM	[CBL-POS].[dbo].[Item_type]
					LEFT JOIN [CBL-POS].[dbo].[item\$]
					ON      [CBL-POS].[dbo].[Item_type].Item_Type_Code = [CBL-POS].[dbo].[item\$].Item_type 
					WHERE	[CBL-POS].[dbo].[item\$].[Item_screen]	=	'".$id."'
					AND     [CBL-POS].[dbo].[item\$].[Item Category Code]	=	'".$id2."'
					GROUP BY Item_Type_Code,Item_Type_Name;			
				";
	$query	=	sqlsrv_query($connect,$sql);

		while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	str_replace(array("\r\n", "\r", "\n"), "", $row);
	}

	return json_encode($data);
}

function post_group_color($id,$id2)
{
	global	$connect;
	$sql	=	"
					SELECT Item_Color_Code,Item_Color_Name FROM	[CBL-POS].[dbo].[Item_color]
					LEFT JOIN [CBL-POS].[dbo].[item\$]
					ON      [CBL-POS].[dbo].[Item_color].Item_Color_Code = [CBL-POS].[dbo].[item\$].Item_color 
					WHERE	[CBL-POS].[dbo].[item\$].[Item_screen]	=	'".$id."'
					AND     [CBL-POS].[dbo].[item\$].[Item Category Code]	=	'".$id2."'
					GROUP BY Item_Color_Code,Item_Color_Name			
				";
	$query	=	sqlsrv_query($connect,$sql);

		while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	str_replace(array("\r\n", "\r", "\n"), "", $row);
	}

	return json_encode($data);
}

function post_group_size($id,$id2)
{
	global	$connect;
	$sql	=	"
					SELECT Item_Size_Code,Item_Size_Name FROM	[CBL-POS].[dbo].[Item_size]
					LEFT JOIN [CBL-POS].[dbo].[item\$]
					ON      [CBL-POS].[dbo].[Item_size].Item_Size_Code = [CBL-POS].[dbo].[item\$].Item_size  
					WHERE	[CBL-POS].[dbo].[item\$].[Item_screen]	=	'".$id."'
					AND     [CBL-POS].[dbo].[item\$].[Item Category Code]	=	'".$id2."'
					GROUP BY Item_Size_Code,Item_Size_Name;			
				";
	$query	=	sqlsrv_query($connect,$sql);

		while($row	=	sqlsrv_fetch_array($query,SQLSRV_FETCH_ASSOC))
	{
		$data[]	=	str_replace(array("\r\n", "\r", "\n"), "", $row);
	}

	return json_encode($data);
}


function save_log($who,$doing,$where)
{
	global	$connect;
	$sql	=	"
				INSERT INTO	[dbo].[log_transection]([log_datetime],[log_user],[log_doing],[log_where],[remark])
				VALUES
				(
					GETDATE(),
					'".$who."',
					'".$doing."',
					'".$where."',
					'-'
				)
				";
	$query	=	sqlsrv_query($connect,$sql) or die( 'SQL Error = '.$sql.'<hr><pre>'. 	print_r( sqlsrv_errors(), true) . '</pre>');
	return	'success';
}