<?php

require 'autoload.php';


function findOrders($limit) {

    $anonymousUserId = eZUser::anonymousId();

    // this SQL works by joining the orders back to users by email addresses. This catches the case where users
    // who have ordered were not logged in at the time.
    $sql = "
        SELECT DISTINCT ezuser.contentobject_id as user_id, id as order_id, ezorder.user_id as user_id_from_order  
        FROM ezorder 
          inner join ezuser as ezuser on ezorder.email=ezuser.email
        WHERE 
          is_temporary = 0 and ezuser.contentobject_id <> $anonymousUserId
        order by user_id, order_id
        limit $limit
    ";

    $db = eZDB::instance();
    $result = $db->arrayQuery($sql);

    $users = array();

    // package the return into a nested array:
    // $userId => array($orderId, $orderId)...
    foreach ($result as $row) {

        $userId = $row['user_id'];
        $orderId = $row['order_id'];

        if (isset($users[$userId])) {
            $users[$userId][] = $orderId;
        } else {
            $users[$userId] = array($orderId);
        }
    }

    return $users;
}

// script initializing
$cli = eZCLI::instance();
$script = eZScript::instance( array(
    'description' => ( "Create addresses objects from submitted eZOrders" ) ,
    'use-session' => false ,
    'use-modules' => true ,
    'use-extensions' => true ,
    'user' => true
) );
$script->startup();

$scriptOptions = $script->getOptions( "[limit:]", "", array(
    'limit' => 'Maximum number of orders to retrieve from the database'
), false, array(
    'user' => true
) );

$script->initialize();

$limit = PHP_INT_MAX;
if ($scriptOptions['limit']) {
    $limit = $scriptOptions['limit'];
    if (!is_numeric($limit)) {
        $cli->error('limit must be a number');
        $script->shutdown(1);
    }
}

// retrieve the users and orders
$users = findOrders($limit);

// iterate the orders, adding any addresses that do not exist
$userCount = count($users);
$userIndex = 0;
foreach ($users as $userId => $orderIds) {

    $user = eZUser::fetch($userId);
    $email = $user->attribute('email');
    $userRealName = $user->contentObject()->name();

    $percentageComplete = floor(($userIndex / $userCount) * 100);
    $cli->notice("\n\nUser: $userId: $email: $userRealName ($percentageComplete% complete)");

    foreach ($orderIds as $orderId) {
        $order = eZOrder::fetch($orderId);

        $billingAddress = ptAddress::billingAddressFromOrder($order);
        $cli->notice("Billing Address: " . $billingAddress->canonicalAddress());

        if (!ptAddress::addressExists($userId, $billingAddress)) {
            $cli->notice("Billing address NOT found. Creating.");
            $billingAddress->saveAddress($userId);
        }

        $shippingAddress = ptAddress::shippingAddressFromOrder($order);
        $cli->notice("Shipping Address: " . $shippingAddress->canonicalAddress());

        if (!ptAddress::addressExists($userId, $shippingAddress)) {
            $cli->notice("Shipping address NOT found. Creating.");
            $shippingAddress->saveAddress($userId);
        }


    }

    $userIndex ++;
}

$cli->output( "Done." );
$script->shutdown( 0 );
