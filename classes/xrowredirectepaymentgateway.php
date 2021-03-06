<?php

/**
 * The class eZRedirectGateway is a base class for payment gateways which
 * support payment through redirection to the payment site and payment notifications
 * throught callbacks(postbacks)
 *
 * @package xrowecommerce
 */

class xrowRedirectEPaymentGateway extends xrowEPaymentGateway
{
    const OBJECT_NOT_CREATED = 1;
    const OBJECT_CREATED = 2;

    function name()
    {
        return null;
    }

    function costs()
    {
        return 0.00;
    }

    function execute( $process, $event )
    {
        
        //__DEBUG__
        $this->logger->writeTimedString( "execute" );
        //___end____
        

        $processParameters = $process->attribute( 'parameter_list' );
        $processID = $process->attribute( 'id' );
        $orderID = $processParameters['order_id'];
        switch ( $process->attribute( 'event_state' ) )
        {
            case self::OBJECT_CREATED:
                {

                    eZDebug::writeDebug( "case eZRedirectGateway::OBJECT_CREATED" );

                    $thePayment = xrowPaymentObject::fetchByOrderID($orderID);
                    if ( is_object( $thePayment ) && $thePayment->approved() )
                    {
                        eZDebug::writeDebug( "Payment accepted." );
                        return eZWorkflowType::STATUS_ACCEPTED;
                    }
                    else
                    {
                        if (!is_object( $thePayment )) {
                            eZDebug::writeError( "Error. unable to fetch PaymentObject for order $orderID", __METHOD__ );
                        } else {
                            eZDebug::writeError( "Error. Payment rejected: payment status 'not approved' for order $orderID", __METHOD__ );
                        }
                    }
                    return eZWorkflowType::STATUS_REJECTED;
                }
                break;
            default:
                {
                    $orderID = $processParameters['order_id'];
                    $paymentObject = self::createPaymentObject( $processID, $orderID );
                    
                    if ( is_object( $paymentObject ) )
                    {
                        $paymentObject->store();
                        $process->setAttribute( 'event_state', self::OBJECT_CREATED );
                        
                        $process->RedirectUrl = $this->createRedirectionUrl( $process );
                    }
                    else
                    {
                        eZDebug::writeError( "Unable to create PaymentObject. Payment rejected.", __METHOD__ );
                        return eZWorkflowType::STATUS_REJECTED;
                    }
                }
                break;
        }
        
        eZDebug::writeDebug( "return eZWorkflowType::STATUS_REDIRECT_REPEAT" );
        
        return eZWorkflowType::STATUS_REDIRECT_REPEAT;
    }

    function needCleanup()
    {
        return true;
    }

    /*!
    Removes temporary eZPaymentObject from database.
    */
    function cleanup( $process, $event )
    {
        eZDebug::writeDebug( "cleanup" );
    }

    /**
     * 
     * Creates instance of subclass of eZPaymentObject which stores
     * information about payment processing(orderID, workflowID, ...).
     * Must be overridden in subclass.
     * @param int $processID
     * @param int $orderID
     */
    static function createPaymentObject( $processID, $orderID )
    {
        $list = eZPersistentObject::fetchObjectList( xrowPaymentObject::definition(), null, array( 
            'order_id' => $orderID, 'status' => xrowPaymentObject::STATUS_NOT_APPROVED
        ) );
        foreach($list as $item)
        {
            $item->remove();
        }
        return xrowPaymentObject::createNew( $processID, $orderID, eZPaypalGateway::GATEWAY_STRING, array( 'workflowprocess_id', $processID ) );
    }
    /*!
    Creates redirection url to payment site.
    Must be overridden in subclass.
    */
    function createRedirectionUrl( $process )
    {
        eZDebug::writeError( "You have to override this" , __METHOD__ );
        throw new Exception( "You have to override " . __METHOD__ );
    }
}