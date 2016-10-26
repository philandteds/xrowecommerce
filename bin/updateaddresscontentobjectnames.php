<?php

require 'autoload.php';

$CLASSES_TO_UPDATE = array('address');

// script initializing
$cli = eZCLI::instance();
$script = eZScript::instance( array(
    'description' => ( "Update the names of all address objects" ) ,
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


// get user objects
$userNodeId = eZURLAliasML::fetchNodeIDByPath('/Users');
if (!$userNodeId) {
    $cli->error("/Users node note found");
    $script->shutdown(1);
}

$userNode = eZContentObjectTreeNode::fetch($userNodeId);

// Get top node
$topNodeArray = array($userNode);

$subTreeCount = 0;
foreach ( $topNodeArray as $node )
{
    $subTreeCount += $node->subTreeCount(
        array(
            'Limitation' => array()
        )
    );
}

$cli->output( "Number of objects to update: $subTreeCount" );

$i = 0;
$dotMax = 70;
$dotCount = 0;
$limit = 50;

foreach ( $topNodeArray as $node )
{
    $offset = 0;
    $subTree = $node->subTree(
        array(
            'Offset' => $offset,
            'Limit' => $limit,
            'Limitation' => array()
        )
    );

    while ( $subTree != null )
    {
        foreach ( $subTree as $innerNode )
        {
            $object = $innerNode->attribute( 'object' );
            $class = $object->contentClass();

            if (in_array($class->Identifier, $CLASSES_TO_UPDATE)) {
                $object->setName( $class->contentObjectName( $object ) );
                $object->store();
            }
            unset( $object );
            unset( $class );

            // show progress bar
            ++$i;
            ++$dotCount;
            $cli->output( '.', false );
            if ( $dotCount >= $dotMax || $i >= $subTreeCount )
            {
                $dotCount = 0;
                $percent = number_format( ( $i * 100.0 ) / $subTreeCount, 2 );
                $cli->output( " " . $percent . "%" );
            }
        }
        $offset += $limit;
        unset( $subTree );
        $subTree = $node->subTree(
            array(
                'Offset' => $offset,
                'Limit' => $limit,
                'Limitation' => array()
            )
        );
    }
}



$cli->output( "Done." );
$script->shutdown( 0 );
