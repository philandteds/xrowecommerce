<?php

$http = eZHTTPTool::instance();
$module = $Params['Module'];

$orderID = $http->sessionVariable( 'MyTemporaryOrderID' );
$order = eZOrder::fetch( $orderID );

if ( $order instanceof eZOrder )
{
    if ( $order->attribute( 'is_temporary' ) )
    {

        $paymentObj = xrowPaymentObject::fetchByOrderID( $orderID );
        if ( $paymentObj != null )
        {
            $startTime = time();
            while( ( time() - $startTime ) < 25 )
            {
                eZDebug::writeDebug( "next iteration", "checkout" );
                if ( $order->attribute( 'is_temporary' ) == 0 )
                {
                    break;
                }
                else
                {
                    sleep ( 2 );
                }
            }
        }
        if (  $order->attribute( 'is_temporary' ) == 1 && $paymentObj == null  )
        {
            $email = $order->accountEmail();
            $order->setAttribute( 'email', $email );
            $order->store();

            $http->setSessionVariable( "UserOrderID", $order->attribute( 'id' ) );

            $operationResult = eZOperationHandler::execute( 'shop', 'checkout', array( 'order_id' => $order->attribute( 'id' ) ) );
            switch( $operationResult['status'] )
            {
            	case eZModuleOperationInfo::STATUS_REPEAT:
                case eZModuleOperationInfo::STATUS_HALTED:
                {
                    if (  isset( $operationResult['redirect_url'] ) )
                    {
                        $module->redirectTo( $operationResult['redirect_url'] );
                        return;
                    }
                    else if ( isset( $operationResult['result'] ) )
                    {
                        $result = $operationResult['result'];
                        $resultContent = false;
                        if ( is_array( $result ) )
                        {
                            if ( isset( $result['content'] ) )
                            {
                                $resultContent = $result['content'];
                            }
                            if ( isset( $result['path'] ) )
                            {
                                $Result['path'] = $result['path'];
                            }
                        }
                        else
                            $resultContent = $result;
                        $Result['content'] = $resultContent;
                        return;
                    }
                }break;
                case eZModuleOperationInfo::STATUS_CANCELLED:
                {
                    $Result = array();

                    $tpl = eZTemplate::factory();
                    $tpl->setVariable( 'operation_result', $operationResult );

                    $Result['content'] = $tpl->fetch( "design:shop/cancelcheckout.tpl" ) ;
                    $Result['path'] = array( array( 'url' => false,
                                                    'text' => ezpI18n::tr( 'kernel/shop', 'Checkout' ) ) );

                    return;
                }

            }
        }
        else
        {
            if ( $order->attribute( 'is_temporary' ) == 0 )
            {
                $http->removeSessionVariable( "CheckoutAttempt" );
                $module->redirectTo( '/xrowecommerce/orderview/' . $orderID );
                return;
            }
            else
            {
                // Get the attempt number and the order.
                $attempt = $http->sessionVariable("CheckoutAttempt", 0 );
                
                // This attempt is for another order. So reset the attempt.
                if ($attempt != 0) 
                {
                    $attempt = 0;
                }
                $http->setSessionVariable("CheckoutAttempt", ++$attempt);
                $http->setSessionVariable("CheckoutAttemptOrderID", $orderID);

                if ( $attempt < 4)
                {
                    $Result = array();

                    $tpl = eZTemplate::factory();
                    $tpl->setVariable( 'attempt', $attempt );
                    $tpl->setVariable( 'orderID', $orderID );
                    $Result['content'] = $tpl->fetch( "design:shop/checkoutagain.tpl" ) ;
                    $Result['path'] = array( array( 'url' => false,
                                                    'text' => ezpI18n::tr( 'kernel/shop', 'Checkout' ) ) );
                    return;
                }
                else
                {
                    // Got no receipt or callback from the payment server.
                    $http->removeSessionVariable( "CheckoutAttempt" );

                    $Result = array();

                    $tpl = eZTemplate::factory();
                    $tpl->setVariable ("ErrorCode", "NO_CALLBACK");
                    $tpl->setVariable ("OrderID", $orderID);

                    $Result['content'] = $tpl->fetch( "design:shop/cancelcheckout.tpl" ) ;
                    $Result['path'] = array( array( 'url' => false,
                                                    'text' => ezpI18n::tr( 'kernel/shop', 'Checkout' ) ) );
                    return;
                }
            }
        }
   }
   $module->redirectTo( '/xrowecommerce/orderview/' . $orderID );
   return;

}
