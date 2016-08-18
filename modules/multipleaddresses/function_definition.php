<?php

$FunctionList = array();

$FunctionList['address_summaries'] = array( 'name' => 'address_summaries',
    'operation_types' => array( 'read' ),
    'call_method' => array( 'class' => 'ptAddressFetchFunctions',
        'method' => 'fetchUserAddressSummaries' ),
    'parameter_type' => 'standard',
    'parameters' => array( ));


$FunctionList['addresses_json'] = array( 'name' => 'addresses_json',
                               'operation_types' => array( 'read' ),
                               'call_method' => array( 'class' => 'ptAddressFetchFunctions',
                                                       'method' => 'fetchUserAddressesAsJson' ),
                               'parameter_type' => 'standard',
                               'parameters' => array( ));
?>
