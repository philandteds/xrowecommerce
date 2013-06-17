<?php
//
// Created on: <01-Aug-2002 10:40:10 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ Publish
// SOFTWARE RELEASE: 4.1.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2009 eZ Systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//


$Module = $Params['Module'];

$tpl = eZTemplate::factory();

$offset = $Params['Offset'];
$limit  = (int) $Params['Limit'] > 0 ? (int) $Params['Limit'] : 15;
$query  = $Params['Query'];
$http   = eZHTTPTool::instance();
if( $http->hasVariable( 'q' ) ) {
	$query = $http->variable( 'q' );
}

$db          = eZDB::instance();
$searchQuery = $db->escapeString( $query );
$paginator   = array(
	'offset' => (int) $offset,
	'limit'  => (int) $limit
);
// Standard SQL doesn't allow you to refer to a column alias in a WHERE clause. This restriction is imposed because when the WHERE code is executed, the column value may not yet be determined
$first_name = 'SUBSTRING( o.data_text_1, LOCATE( "<first_name>", o.data_text_1 ) + CHARACTER_LENGTH( "<first_name>" ), LOCATE( "</first_name>", o.data_text_1 ) - LOCATE( "<first_name>", o.data_text_1 ) - CHARACTER_LENGTH( "<first_name>" ) )';
$last_name  = 'SUBSTRING( o.data_text_1, LOCATE( "<last_name>", o.data_text_1 ) + CHARACTER_LENGTH( "<last_name>" ), LOCATE( "</last_name>", o.data_text_1 ) - LOCATE( "<last_name>", o.data_text_1 ) - CHARACTER_LENGTH( "<last_name>" ) )';
$q = '
	SELECT
		o.*,
		 ' . $first_name . ' as first_name,
		 ' . $last_name . ' as last_name
	FROM
	    ezorder o
	WHERE
		' . $first_name . ' LIKE "%' . $searchQuery . '%"
		OR ' . $last_name . ' LIKE "%' . $searchQuery . '%"
	ORDER BY first_name ASC
';

$orderCount = count( $db->arrayQuery( $q ) );
$orderArray = $db->arrayQuery( $q, $paginator );

$orderList = array();
foreach( $orderArray as $order ) {
	$orderList[] = new eZOrder( $order );
}

$orderIDs = array();
foreach( $orderList as $order ) {
	$orderIDs[] = $order->attribute( 'id' );
}

$tpl->setVariable( 'order_list', $orderList );
$tpl->setVariable( 'order_list_count', $orderCount );
$tpl->setVariable( 'limit', $limit );

$viewParameters = array(
    'offset' => $offset
);
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'sort_field', 'first_name' );
$tpl->setVariable( 'sort_order', 'desc' );
$tpl->setVariable( 'search', true );
$tpl->setVariable( 'query', $query );

$Result = array();
$Result['path'] = array(
    array(
        'text' => ezpI18n::tr( 'kernel/shop', 'Order search' ) ,
        'url' => false
    )
);

$Result['content'] = $tpl->fetch( 'design:shop/orderlist.tpl' );
?>
