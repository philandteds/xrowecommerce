<?php
$Module = array( "name" => "xrow e-commerce" );

$ViewList = array();

$ViewList["userregister"] = array(
    "functions" => array( 'buy' ),
    "script" => "userregister.php",
    'ui_component' => 'shop',
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezshopnavigationpart',
    'single_post_actions' => array( 'StoreButton' => 'Store',
                                    'CancelButton' => 'Cancel'
                                    )
    );
$ViewList["register"] = array(
    "functions" => array( ),
    'ui_component' => 'shop',
    "script" => "register.php"
    );
$ViewList["basket"] = array(
    "functions" => array( 'buy' ),
    "script" => "basket.php",
    'ui_component' => 'shop',
    "default_navigation_part" => 'ezshopnavigationpart',
    'unordered_params' => array( 'error' => 'Error' ),
    "params" => array(  ) );

$ViewList["cart"] = array(
    "functions" => array( 'buy' ),
    "script" => "cart.php",
    'ui_component' => 'shop',
    "default_navigation_part" => 'ezmynavigationpart',
    'unordered_params' => array( 'error' => 'Error' ),
    "params" => array(  ) );

$ViewList["multiadd"] = array(
    "functions" => array( 'buy' ),
    'ui_component' => 'shop',
    "script" => "multiadd.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array(  ) );

$ViewList["statistics"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "orderstatistics.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( 'StartYear', 'StartMonth', 'StopMonth', 'StartDay', 'StopDay' ) );

$ViewList["confirmorder"] = array(
    "functions" => array( 'buy' ),
    'ui_component' => 'shop',
    "script" => "confirmorder.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array(  ) );

$ViewList["checkout"] = array(
    "functions" => array( 'buy' ),
    'ui_component' => 'shop',
    "script" => "checkout.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array(  ) );

$ViewList["orderview"] = array(
    "functions" => array( 'buy' ),
    'ui_component' => 'shop',
    "script" => "orderview.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( "OrderID" ) );

$ViewList["invoiceprint"] = array(
    "functions" => array( 'buy' ),
    'ui_component' => 'shop',
    "script" => "invoiceprint.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( "OrderID" ) );

$ViewList["shippingplanprint"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "shippingplanprint.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( "OrderID" ) );

$ViewList["tin"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "tin.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$ViewList["priceimport"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "price_import.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$ViewList["priceexport"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "price_export.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$ViewList["productimport"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "product_import.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( 'object_id' => 'ObjectID' ) );

$ViewList["productexport"] = array(
    "functions" => array( 'administrate' ),
    'ui_component' => 'shop',
    "script" => "product_export.php",
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array( 'object_id' => 'ObjectID' ) );

$ViewList["directorder"] = array(
    "script" => "direct_order.php",
    'ui_component' => 'shop',
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$Payment = array(
    'name' => 'Payment',
    'values' => array(),
    'ui_component' => 'shop',
    'extension' => 'xrowecommerce',
    'path' => 'classes/',
    'file' => 'xrowecommerce.php',
    'class' => 'xrowECommerce',
    'function' => 'paymentLimitationList',
    'parameter' => array( false )
    );
$ViewList["orderlist"] = array(
    "functions" => array( 'administrate' ),
    "script" => "orderlist.php",
    'ui_component' => 'shop',
    "default_navigation_part" => 'ezshopnavigationpart',
    "unordered_params" => array( "offset" => "Offset", "limit" => "Limit" ),
    "params" => array(  ) );

$ViewList["removeorder"] = array(
    "functions" => array( 'remove_order' ),
    "script" => "removeorder.php",
    'ui_component' => 'shop',
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$ViewList["archiveorder"] = array(
    "functions" => array( 'administrate' ),
    "script" => "archiveorder.php",
    'ui_component' => 'shop',
    'ui_context' => 'edit',
    "default_navigation_part" => 'ezshopnavigationpart',
    "params" => array() );

$ViewList["set_payment_gateway"] = array(
    "functions" => array( 'buy' ),
    "script" => "set_payment_gateway.php",
    "params" => array( "Gateway" ) );

$ViewList["json"] = array( "script" => "json.php", "params" => array ( 'object', 'method' ) );
$ViewList["ordersearch"] = array(
    "functions" => array( 'administrate' ),
    "script" => "ordersearch.php",
    'ui_component' => 'shop',
    "default_navigation_part" => 'ezshopnavigationpart',
    "unordered_params" => array( "offset" => "Offset", "limit" => "Limit" ),
    "params" => array( 'Query' ) );


$FunctionList['buy'] = array( );
$FunctionList['administrate'] = array( );
$FunctionList['remove_order'] = array( );
$FunctionList['bypass_captcha'] = array( );
$FunctionList['payment'] = array( 'Payment' => $Payment );
?>
