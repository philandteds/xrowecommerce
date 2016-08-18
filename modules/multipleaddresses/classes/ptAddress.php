<?php

/**
 * Utility methods to handle Phil & Ted's Address records
 *
 * Class ptAddress
 */
class ptAddress
{
    function saveAddress($userId)
    {

        $userNode = $this->fetchUserNode($userId);
        if (!$userNode) {
            return false;
        }

        $userNodeId = $userNode->NodeID;

        // look for a consumer profile under the user.
        /** @var eZContentObjectTreeNode $userChild */
        $consumerProfileId = null;
        $userChildren = $userNode->children();
        foreach ($userChildren as $userChild) {
            if ($userChild->ClassIdentifier == 'consumer_profile') {
                $consumerProfileId = $userChild->object()->ID;
                break;
            }
        }

        eZContentFunctions::createAndPublishObject(
            $params = array(
                'class_identifier' => 'address',
                'parent_node_id' => $userNodeId,
                'attributes' => array(
                    'street_address' => $this->StreetAddress,
                    'city' => $this->City,
                    'state' => $this->State,
                    'country' => $this->Country,
                    'zip' => $this->Zip,
                    'consumer_profile' => $consumerProfileId
                )
            )
        );
    }

    /**
     * Convenience method to fetch the User's contentObjectTreeNode.
     *
     * @param $userId int
     * @return eZPersistentObject[]|null
     */
    private static function fetchUserNode($userId) {
        return eZContentObjectTreeNode::fetchByContentObjectID($userId)[0];
    }

    /**
     * Fetches all the addresses for a user
     *
     * @param $userId int
     * @return array array of ptAddress objects
     */

    public static function fetchAddresses($userId) {
        $userNode = self::fetchUserNode($userId);

        if (!$userNode) {
            return false;
        }

        $addresses = array();

        $userChildren = $userNode->children();
        foreach ($userChildren as $userChild) {
            if ($userChild->ClassIdentifier == 'address') {
                $addresses[] = self::fromObject($userChild->object());
            }
        }

        return $addresses;
    }


    /**
     * Check to see if an address already exists under a userId
     *
     * @param $ptAddress
     * @return bool true if the address exists.
     */
    public static function addressExists($userId, $ptAddress)
    {
        $addresses = self::fetchAddresses($userId);

        $canonicalAddress = $ptAddress->canonicalAddress();

        foreach ($addresses as $address) {
            if (strtolower($address->canonicalAddress()) == strtolower($canonicalAddress)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Takes a separated street address line 1 and street address line 2, and returns a single-line representation.
     * This is required because xrowecommerce uses a single-line street_address field in the address contentobject,
     * while the eZOrder stores the address in two lines.
     *
     * @param $address1 street address line 1
     * @param $address2 street address line 2
     * @return string collapsed street address
     */
    private static function collapseStreetAddressLines($address1, $address2)
    {

        $addressFragments = array();

        if (trim($address1) != '')
            $addressFragments[] = $address1;

        if (trim($address2) != '')
            $addressFragments[] = $address2;

        return implode(', ', $addressFragments);
    }

    private static function countryName($countryCode)
    {
        if (!$countryCode) {
            return false;
        }

        $country = eZCountryType::fetchCountry($countryCode, 'Alpha3');
        if ($country) {
            return $country['Name'];
        } else {
            return $countryCode;
        }
    }

    private static function stateName($stateCode, $countryCode)
    {
        if (!$stateCode) {
            return false;
        }

        $state = xrowGeonames::getSubdivisionName($countryCode, $stateCode);
        if ($state) {
            return $state;
        }

        return $stateCode;
    }


    /**
     * Collapses an address into a simple, easy-to-compare one line format
     * @return string string the address, collapsed into one line
     * @internal param string $address1 Address Line 1
     * @internal param string $address2 Address Line 2
     * @internal param string $city City
     * @internal param string $state State
     * @internal param string $country Country
     */
    public function canonicalAddress()
    {
        return trim(implode(';', array($this->StreetAddress, $this->City, $this->State, $this->Country, $this->Zip)));
    }

    /**
     * Collapses an address into a brief, simplified one-liner for human consumption. Used to populate dropdown lists etc.
     */
    public function addressSummary() {
        return trim(implode(',', array($this->StreetAddress, $this->City)));
    }

    /**
     * Constructs a new ptAddress from an existing Address content object.
     *
     * @param $addressContentObject eZContentObject
     * @return ptAddress New ptAddress, initialized from the content object
     */
    public static function fromObject($addressContentObject) {

        if (!$addressContentObject) {
            return false;
        }

        $dataMap = $addressContentObject->dataMap();

        $country = $dataMap['country']->title();
        $state = $dataMap['state']->content();

        $address = new ptAddress();
        $address->StreetAddress = $dataMap['street_address']->content();
        $address->City = $dataMap['city']->content();
        $address->State = $state;
        $address->Country = $country;
        $address->Zip = $dataMap['zip']->content();

        return $address;
    }

    /**
     * Builds an address from a order's billing address
     *
     * @param $order
     * @return ptAddress
     */
    public static function billingAddressFromOrder($order) {
        return self::fromOrder($order);
    }

    /**
     * Builds an address from a order's shipping address
     *
     * @param $order
     * @return ptAddress
     */
    public static function shippingAddressFromOrder($order) {
        return self::fromOrder($order, "s_");
    }

    /**
     * Build an address from an order. Fields may be prefixed by a character sequence, such as s_ for shipping addresses
     *
     * @param $order eZOrder order to build the address from.
     * @return ptAddress new ptAddress
     */
    protected static function fromOrder($order, $prefix = "") {

        if (!$order) {
            return false;
        }

        $info = $order->accountInformation();

        $countryCode = $info["{$prefix}country"];

        $country = self::countryName($countryCode);
        $state = self::stateName($info["{$prefix}state"], $countryCode);

        $address = new ptAddress();
        $address->StreetAddress = self::collapseStreetAddressLines($info["{$prefix}address1"], $info["{$prefix}address2"]);
        $address->City = $info["{$prefix}city"];
        $address->State = $state;
        $address->Country = $country;
        $address->Zip = $info["{$prefix}zip"];

        return $address;
    }

    var $StreetAddress;
    var $City;
    var $State;
    var $Country;
    var $Zip;
}