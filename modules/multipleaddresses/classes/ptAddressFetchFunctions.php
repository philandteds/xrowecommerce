<?php

class ptAddressFetchFunctions
{

    function fetchUserAddressSummaries() {
        $result = false;

        if (!eZUser::currentUser()->isAnonymous()) {
            $addresses = ptAddress::fetchAddresses(eZUser::currentUserID());

            $result = array();
            /** @var ptAddress $address */
            foreach ($addresses as $address) {
                $result[] = $address->addressSummary()                    ;
            }

            return array( 'result' => $result );
        }

    }

    function fetchUserAddressesAsJson()
    {
        $result = false;

        if (!eZUser::currentUser()->isAnonymous()) {
            $addresses = ptAddress::fetchAddresses(eZUser::currentUserID());

            $result = json_encode($addresses);
            return array( 'result' => $result );
        }
    }

}