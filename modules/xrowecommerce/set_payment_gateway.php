<?php
/**
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Dec 2012
 **/

$http  = eZHTTPTool::instance();
$order = eZOrder::fetch( $http->sessionVariable( 'MyTemporaryOrderID' ) );
if( !is_object( $order ) ) {
    return $Params['Module']->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

$ini = eZINI::instance();
$order->detachProductCollection();
if( $ini->variable( 'ShopSettings', 'ClearBasketOnCheckout' ) == 'enabled' ) {
    $basket = eZBasket::currentBasket();
    $basket->remove();
}

if(
    $Params['Gateway'] !== null
    && in_array( $Params['Gateway'], xrowEPayment::allowedGatewayListByUser() )
) {
    $http->setSessionVariable( 'paymentgateway', $Params['Gateway'] );
}

// some controller checking to confirm they can use the relevant gateway
$pass = true;
switch ( $Params['Gateway'] ) {
    case 'xrowCashOnDeliveryGateway' :
        // Only country is UAE currently
        $pass = eZINI::instance( 'shopping.ini' )->variable('General', 'FreeGatewayEnabled' )
            == 'enabled';
        break;
    case 'xrowAdvancepayment' :
        //TODO review business logic required for advanced payment
        // but disabling for now
        $pass = false;
        break;
    case 'free' :
        $pass = eZINI::instance( 'shopping.ini' )->variable('General', 'FreeGatewayEnabled' )
            == 'enabled';
        break;
}
if ($pass !== true) {
    return $Params['Module']->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

return $Params['Module']->redirectTo( '/xrowecommerce/checkout/' );
?>
