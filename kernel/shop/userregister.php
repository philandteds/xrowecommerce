<?php
//
// Created on: <04-Mar-2003 10:22:42 bf>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
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

$http = eZHTTPTool::instance();
$module = $Params["Module"];

$tpl = eZTemplate::factory();

if ( $module->isCurrentAction( 'Cancel' ) )
{
    $module->redirectTo( '/shop/basket/' );
    return;
}

$user = eZUser::currentUser();

$firstName = '';
$lastName = '';
$email = '';
// Initialize variables
$title = $address1 = $address2 = $zip = $city = $state = $phone = $mi = '';

if ( $user->isLoggedIn() )
{
    $userObject = $user->attribute( 'contentobject' );
    $userMap = $userObject->dataMap();

    $companyName = $userMap['company_name']->content();
    $companyAdditional = $userMap['company_additional']->content();
    $taxid = $userMap['tax_id']->content();
    $title = $userMap['title']->content();
    $firstName = $userMap['first_name']->content();
    $mi = $userMap['mi']->content();
    $lastName = $userMap['last_name']->content();
    $address1 = $userMap['address1']->content();
    $address2 = $userMap['address2']->content();
    $city = $userMap['city']->content();
    $state = $userMap['state']->content();
    $zip = $userMap['zip']->content();
    $phone = $userMap['phone']->content();
    $fax = $userMap['fax']->content();
    $email = $user->attribute( 'email' );

    $scompanyName = $userMap['s_company_name']->content();
    $scompanyAdditional = $userMap['s_company_additional']->content();
    $staxid = $userMap['s_tax_id']->content();
    $stitle = $userMap['s_title']->content();
    $sfirstName = $userMap['s_first_name']->content();
    $smi = $userMap['s_mi']->content();
    $slastName = $userMap['s_last_name']->content();
    $saddress1 = $userMap['s_address1']->content();
    $saddress2 = $userMap['s_address2']->content();
    $scity = $userMap['s_city']->content();
    $sstate = $userMap['s_state']->content();
    $szip = $userMap['s_zip']->content();
    $scountry = $userMap['s_country']->content();
    $sphone = $userMap['s_phone']->content();
    $sfax = $userMap['s_fax']->content();
    $slastName = $userMap['s_last_name']->content();
    $email = $user->attribute( 's_email' );
}




// Check if user has an earlier order, copy order info from that one
$orderList = eZOrder::activeByUserID( $user->attribute( 'contentobject_id' ) );
if ( count( $orderList ) > 0 and  $user->isLoggedIn() )
{
    $accountInfo = $orderList[0]->accountInformation();
    $address1 = $accountInfo['address1'];
    $address2 = $accountInfo['address2'];
    $zip = $accountInfo['zip'];
    $city = $accountInfo['city'];
    $state = $accountInfo['state'];
    $phone = $accountInfo['phone'];
    $fax = $accountInfo['fax'];
    $mi = $accountInfo['mi'];
}

$tpl->setVariable( "input_error", false );
if ( $module->isCurrentAction( 'Store' ) )
{

    $inputIsValid = true;
    $companyName = $http->postVariable( "CompanyName" );
    $companyAdditional = $http->postVariable( "CompanyAdditional" );
    $taxId = $http->postVariable( "TaxId" );
    $title = $http->postVariable( "title" );
    $firstName = $http->postVariable( "FirstName" );
    if ( trim( $firstName ) == "" )
        $inputIsValid = false;
    $lastName = $http->postVariable( "LastName" );
    if ( trim( $lastName ) == "" )
        $inputIsValid = false;
    $mi = $http->postVariable( "MI" );

    $email = $http->postVariable( "EMail" );
    if ( ! eZMail::validate( $email ) )
        $inputIsValid = false;

    $address1 = $http->postVariable( "Address1" );

    $address2 = $http->postVariable( "Address2" );
        if ( trim( $address1 ) == "" )
            $inputIsValid = false;

    $state = $http->postVariable( "State" );
    if ( trim( $state ) == "" )
        $inputIsValid = false;

	$city = $http->postVariable( "City" );
    if ( trim( $city ) == "" )
        $inputIsValid = false;

    $zip = $http->postVariable( "Zip" );
    if ( trim( $zip ) == "" )
        $inputIsValid = false;

    $phone = $http->postVariable( "Phone" );
    if ( trim( $phone ) == "" )
        $inputIsValid = false;

    $fax = $http->postVariable( "Fax" );

    if ( $inputIsValid == true )
    {
        // Check for validation
        $basket = eZBasket::currentBasket();

        $db = eZDB::instance();
        $db->begin();
        $order = $basket->createOrder();

        $doc = new DOMDocument( '1.0', 'utf-8' );
        #$doc = new eZDOMDocument( 'account_information' );

        $root = $doc->createElement( "shop_account" );
        $doc->appendChild( $root );
        #$root = $doc->createElement( "shop_account" );
        #$doc->setRoot( $root );

        $companyNameNode = $doc->createElement( "company_name" );
        #$companyNameNode = $doc->createElementNode( "company_name" );
        $companyNameNode->appendChild( $doc->createTextNode( $companyName ) );
        $root->appendChild( $companyNameNode );

        $companyAdditionalNode = $doc->createElement( "company_additional" );
        #$companyAdditionalNode = $doc->createElementNode( "company_additional" );
        $companyAdditionalNode->appendChild( $doc->createTextNode( $companyAdditional ) );
        $root->appendChild( $companyAdditionalNode );

        $taxIdNode = $doc->createElement( "tax_id" );
        #$taxIdNode = $doc->createElementNode( "tax_id" );
        $taxIdNode->appendChild( $doc->createTextNode( $taxId ) );
        $root->appendChild( $taxIdNode );
        
        $titleNode = $doc->createElement( "title" );
        $titleNode->appendChild( $doc->createTextNode( $title ) );
        $root->appendChild( $titleNode );

        $firstNameNode = $doc->createElement( "first-name" );
        #$firstNameNode = $doc->createElementNode( "first-name" );
        $firstNameNode->appendChild( $doc->createTextNode( $firstName ) );
        $root->appendChild( $firstNameNode );

        $miNode = $doc->createElement( "mi" );
        #$miNode = $doc->createElementNode( "mi" );
        $miNode->appendChild( $doc->createTextNode( $mi ) );
        $root->appendChild( $miNode );

        $lastNameNode = $doc->createElement( "last-name" );
        #$lastNameNode = $doc->createElementNode( "last-name" );
        $lastNameNode->appendChild( $doc->createTextNode( $lastName ) );
        $root->appendChild( $lastNameNode );

        $address1Node = $doc->createElement( "address1" );
        #$address1Node = $doc->createElementNode( "address1" );
        $address1Node->appendChild( $doc->createTextNode( $address1 ) );
        $root->appendChild( $address1Node );

        $address2Node = $doc->createElement( "address2" );
        #$address2Node = $doc->createElementNode( "address2" );
        $address2Node->appendChild( $doc->createTextNode( $address2 ) );
        $root->appendChild( $address2Node );

        $cityNode = $doc->createElement( "city" );
        #$cityNode = $doc->createElementNode( "city" );
        $cityNode->appendChild( $doc->createTextNode( $city ) );
        $root->appendChild( $cityNode );

        $zipNode = $doc->createElement( "zip" );
        #$zipNode = $doc->createElementNode( "zip" );
        $zipNode->appendChild( $doc->createTextNode( $zip ) );
        $root->appendChild( $zipNode );

        $stateNode = $doc->createElement( "state" );
        #$stateNode = $doc->createElementNode( "state" );
        $stateNode->appendChild( $doc->createTextNode( $state ) );
        $root->appendChild( $stateNode );

        $phoneNode = $doc->createElement( "phone" );
        #$phoneNode = $doc->createElementNode( "phone" );
        $phoneNode->appendChild( $doc->createTextNode( $phone ) );
        $root->appendChild( $phoneNode );

        $faxNode = $doc->createElement( "fax" );
        #$faxNode = $doc->createElementNode( "fax" );
        $faxNode->appendChild( $doc->createTextNode( $fax ) );
        $root->appendChild( $phoneNode );

		$emailNode = $doc->createElement( "email" );
		#$emailNode = $doc->createElementNode( "email" );
        $emailNode->appendChild( $doc->createTextNode( $email ) );
        $root->appendChild( $emailNode );

        $order->setAttribute( 'data_text_1', $doc->saveXML() );
        #$order->setAttribute( 'data_text_1', $doc->toString() );
        $order->setAttribute( 'account_identifier', "ez" );

        $order->setAttribute( 'ignore_vat', 0 );

        $order->store();
        $db->commit();

        eZHTTPTool::setSessionVariable( 'MyTemporaryOrderID', $order->attribute( 'id' ) );

        $module->redirectTo( '/shop/confirmorder/' );
        return;
    }
    else
    {
        $tpl->setVariable( "input_error", true );
    }
}

$tpl->setVariable( "company_name", $companyName );
$tpl->setVariable( "company_additional", $companyAdditional );
$tpl->setVariable( "tax_id", $taxId );
$tpl->setVariable( "title", $title );
$tpl->setVariable( "first_name", $firstName );
$tpl->setVariable( "mi", $mi );
$tpl->setVariable( "last_name", $lastName );
$tpl->setVariable( "email", $email );

$tpl->setVariable( "address1", $address1 );
$tpl->setVariable( "address2", $address2 );
$tpl->setVariable( "zip", $zip );
$tpl->setVariable( "city", $city );
$tpl->setVariable( "state", $state );
$tpl->setVariable( "phone", $phone );
$tpl->setVariable( "fax", $fax );

$tpl->setVariable( "s_company_name", $scompanyName );
$tpl->setVariable( "s_company_additional", $scompanyAdditional );
$tpl->setVariable( "s_title", $stitle );
$tpl->setVariable( "s_first_name", $sfirstName );
$tpl->setVariable( "s_mi", $smi );
$tpl->setVariable( "s_last_name", $slastName );
$tpl->setVariable( "s_email", $semail );

$tpl->setVariable( "s_address1", $saddress1 );
$tpl->setVariable( "s_address2", $saddress2 );
$tpl->setVariable( "s_zip", $szip );
$tpl->setVariable( "s_city", $scity );
$tpl->setVariable( "s_state", $sstate );
$tpl->setVariable( "s_phone", $sphone );
$tpl->setVariable( "s_fax", $sfax );

$Result = array();
$Result['content'] = $tpl->fetch( "design:shop/userregister.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezpI18n::tr( 'kernel/shop', 'Enter account information' ) ) );
?>
