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

return $Params['Module']->redirectTo( '/xrowecommerce/checkout/' );
?>