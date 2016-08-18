<?php


/**
 * After placing an order, save the user's address to the database
 *
 * Class SaveOrderAddressType
 */
class SaveOrderAddressType  extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "saveorderaddress";
    public function __construct()
    {
        parent::__construct( SaveOrderAddressType::WORKFLOW_TYPE_STRING, 'Save Order Address' );
    }

    public function execute( $process, $event )
    {
        $parameters = $process->attribute( 'parameter_list' );
        $orderID = $parameters['order_id'];

        // find the user attached to this order, and update their address records
        $order = eZOrder::fetch($orderID);

        // email attribute may not be set yet. Pull it from the shipping info
        $accountInformation = $order->accountInformation();

        $email = $accountInformation['email'];
        $user = eZUser::fetchByEmail($email);
        if ($user) {

            $userId = $user->id();

            $billingAddress = ptAddress::billingAddressFromOrder($order);
            if (!ptAddress::addressExists($userId, $billingAddress)) {
                $billingAddress->saveAddress($userId);
            }

            $shippingAddress = ptAddress::shippingAddressFromOrder($order);
            if (!ptAddress::addressExists($userId, $shippingAddress)) {
                $shippingAddress->saveAddress($userId);
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}
eZWorkflowEventType::registerEventType(SaveOrderAddressType::WORKFLOW_TYPE_STRING, 'saveorderaddresstype');