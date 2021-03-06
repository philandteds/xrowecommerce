$(document).ready(function() {
    $('#shipping-link').mousedown(function(){
        $('input#shipping-checkbox').trigger('click');
    });
    

});
if( window.YUI !== undefined ){
YUI().use( 'node', function(Y)
{
    Y.on( 'domready', function() 
    {
        if ( Y.one( '#country' ) && Y.one( '#state' ) )
        {
            updateSubdivisions( Y.one( '#country' ) );
        }
        if ( Y.one( '#shipping-checkbox' ) )
        {
            if ( Y.one( '#s_country' ) && Y.one( '#s_state' ) )
            {
                updateSubdivisions( Y.one( '#s_country' ) );
            }
            Y.on( 'change', function( e )
            {
                if ( Y.one( '#shipping-checkbox' ).get( 'checked' ) )
                {
                    //updateShipping();
                }
                if ( Y.one( '#country' ) && Y.one( '#state' ) )
                {
                    updateSubdivisions( e.currentTarget );
                }
            }, '#country');
            Y.on( 'change', function( e )
            {
                if ( !Y.one( '#shipping-checkbox' ).get( 'checked' ) )
                {
                    //updateShipping();
                }
                if ( Y.one( '#country' ) && Y.one( '#s_state' ) )
                {
                updateSubdivisions( e.currentTarget );
                }
            }, '#s_country');
            Y.on( 'click', function( e )
            {
                changeShipping();
                //updateShipping();
            }, '#shipping-checkbox');
        }
    });
});

YUI().use("node", "event-mouseenter", function(Y) {
    if ( Y.one("#AutomaticDeliveryTooltip")) {
        var nodeTip = Y.one("#AutomaticDeliveryTooltip")
        Y.on("mouseenter", function (e) {
            nodeTip.removeClass('hide');
        }, "#overlay-text p");
        Y.on("mouseleave", function (e) {
            nodeTip.addClass('hide');
        }, "#overlay-text p");
    }
});

}

/* uncomment for debugging
YUI({
    filter: 'debug',
    timeout: 10000
}).use( 'node', 'console', 'console-filters', 'dd-plugin', function (Y) {
    if (Y.one("#debug") ) {
        Y.one("BODY").prepend('<div id="yconsole"></div>' );
        Y.one("BODY").addClass( 'yui-skin-sam');
// Configure the Console's logSource to Y.Global to make it universal
new Y.Console({
    boundingBox: '#yconsole',
    plugins: [ Y.Plugin.Drag ], //, Y.Plugin.ConsoleFilters
    logSource: Y.Global,
    style: 'separate',
    newestOnTop: true
}).render();
    }
});
*/

function ShowHide(id)
{
    YUI().use( 'node', function(Y)
    { 
        var node = Y.one( id );
        if ( node.hasClass( 'hide') )
        {
            node.removeClass('hide');
            node.addClass('show');
        }
        else
        {
            node.removeClass('show');
            node.addClass('hide');
        }
    });
}

function ezjson(uri, callback, args) 
{
    // Create business logic in a YUI sandbox using the 'io' and 'json' modules
    YUI().use('node', 'io', 'io-ez', 'dump', 'json-parse', function(Y) 
    {
        function onFailure(transactionid, response)
        {
            Y.log('Async call failed!');
        }
        function onComplete(transactionid, response, callback, args)
        {
            // transactionid : The transaction's ID.
            // response: The response object.
            // arguments: Object containing an array { complete: ['foo', 'bar'] }.
            Y.log('RAW JSON DATA: ' + response.responseText);

            // Process the JSON data returned from the server
            try 
            {
                var data = null;
                data = Y.JSON.parse(response.responseText);
                Y.log('PARSED DATA: ' + Y.Lang.dump(data));
            } 
            catch (e) 
            {
                Y.log('JSON Parse failed!');
                return;
            }
            callback(data, args);
        }
        
        Y.on('io:failure', onFailure, this);
        Y.on('io:complete', onComplete, this, callback, args);
        
        // Make the call to the server for JSON data
        transaction = Y.io('/xrowecommerce/json/' + uri, callback);
    });
}

function updateSubdivisions( country_node ) 
{
    if ( country_node.get( 'selectedIndex' ) == -1 ) return false;
    
	YUI().use( 'node', 'io', 'io-ez', function( Y )
    {
        var country = country_node.get( 'options' ).item( country_node.get( 'selectedIndex' ) ).get( 'value' );
        if( Y.one( '#s_state' ) )
        {
            Y.one( '#s_state' ).set( 'disabled', 'disabled' );
        }
        if( Y.one( '#state' ) )
        {
            Y.one( '#state' ).set( 'disabled', 'disabled' );
        }
        Y.io.ez( 'xrowecommerce::getSubdivisions::' + country, 
        {
            arguments: country_node,
            on: 
            {
                success: function( id, r, country_node)
                {
                    YUI().use('node', function(Y) 
                    {
                        var data = r.responseJSON.content;

                        if( country_node.get('id') == 'country' )
                        {
                            var subdivision_node = Y.one( '#state' );
                        }
                        else
                        {
                            var subdivision_node = Y.one( '#s_state' );
                        }
                        
                        if (subdivision_node == null) return false;

                        // If state is selected: get the old value for checking it later
                        if ( subdivision_node.get( 'selectedIndex' ) > 0 )
                        {
                            var stateSelIndex = subdivision_node.get( 'selectedIndex' );
                            var oldStateValue = subdivision_node.get( 'options' ).item( stateSelIndex ).get( 'value' );
                        }

                        if ( !oldStateValue )
                        {
                            if( country_node.get('id') == 'country' && Y.one( '#hidden_state' ) )
                            {
                                var oldStateValue = Y.one( '#hidden_state' ).get( 'value' );
                            }
                            else if ( country_node.get('id') == 's_country' && Y.one( '#hidden_s_state' ) )
                            {
                                var oldStateValue = Y.one( '#hidden_s_state' ).get( 'value' );
                            }
                        }

                        var nodes = subdivision_node.all( 'option' );
                        var deleteNodes = function(n, a, b)
                        {
                            n.get( 'parentNode' ).removeChild(n);
                        };
                        nodes.each(deleteNodes);
                        var node = Y.Node.create( '<option>&nbsp;</option>' );
			subdivision_node.appendChild(node);
			//Just a simple append for allowing extra options. Just a one off. If getting repeated, need to comeup with better solution
			if(country == 'USA') {
				var additionalStateAA = '<option value="AA">Armed Forces (Americas)</option>';
				var additionalStateAE = '<option value="AE">Armed Forces (Europe)</option>';
				var additionalStateAP = '<option value="AP">Armed Forces (Pacific)</option>';
				subdivision_node.appendChild(additionalStateAA);
				subdivision_node.appendChild(additionalStateAE);
				subdivision_node.appendChild(additionalStateAP);
			}
                        
			var index = 0;
                        for (i in data ) 
                        {
                            index++;
                            if (oldStateValue == i) 
                            {
                                var stateSelected = (country == 'USA') ? index+3 : index;
                            }
                            var node = Y.Node.create( '<option value="' + i + '">' + data[i] + '</option>' );
                            subdivision_node.appendChild(node);
                        }
                        if ( typeof( stateSelected ) != 'undefined' ) 
                        {
                            subdivision_node.set( 'selectedIndex', stateSelected );
                        }
                        else
                        {
                            subdivision_node.set('selectedIndex', 0);
                        }
                        if( Y.one( '#s_state' ) ) Y.one( '#s_state' ).removeAttribute( 'disabled' );
                        if( Y.one( '#state' ) ) Y.one( '#state' ).removeAttribute( 'disabled' );
                    });
                }
            }
        });
    });
}

function updateShipping() 
{
    YUI().use( 'node', 'io', 'io-ez', 'dump', 'json-parse', function( Y ) 
    {
        if ( !Y.one('#shippingtype') )
        {
            return false;
        }

        if ( Y.one( '#shipping-checkbox' ).get( 'checked' ) ) 
        {
            var selectedIndex = Y.one( '#country' ).get( 'selectedIndex' );
            var country = Y.one( '#country' ).get( 'options' ).item( selectedIndex ).get( 'value' );
        } 
        else 
        {
            var selectedIndex = Y.one( '#s_country').get( 'selectedIndex' );
            var country = Y.one( '#s_country' ).get( 'options' ).item( selectedIndex ).get( 'value' );
        }

        var doit = function(data) 
        {
            if ( Y.one( '#shippingtype' ).get( 'tagName' ) == 'INPUT' )
            {
                return false;
            }

            // If shippingtype is selected: get the old value for checking it later
            if ( Y.one( '#shippingtype' ).get( 'selectedIndex' ) > 0 )
            {
                var oldShippSelIndex = Y.one( '#shippingtype' ).get( 'selectedIndex' );
                var oldname = Y.one( '#shippingtype' ).get( 'options' ).item( oldShippSelIndex ).get( 'text' );
                var old = Y.one( '#shippingtype' ).get( 'options' ).item( oldShippSelIndex ).get( 'value' );
            }

            var nodes = Y.all('#shippingtype option');
            var deleteNodes = function(n, a, b) 
            {
                n.get('parentNode').removeChild(n);
            };
            nodes.each(deleteNodes);
            var node = Y.Node.create('<option>&nbsp;</option>');
            for (i = 0; i < data.length; i++) 
            {
                if ( data[i][2] == false ) 
                {
                    var node = Y.Node.create('<option value="' + data[i][1] + '" disabled>' + data[i][0] + '</option>');
                } 
                else 
                {
                    if ( old == data[i][1] ) 
                    {
                        var selected = i;
                    }
                    var node = Y.Node.create('<option value="' + data[i][1] + '">' + data[i][0] + '</option>');
                }
    
                Y.one( '#shippingtype' ).appendChild( node );
            }
            if (typeof (selected) != 'undefined') 
            {
                Y.one('#shippingtype').set('selectedIndex', selected);
            } 
            else if ( oldShippSelIndex )
            {
                if ( Y.one( '#shippingtype' ).get( 'selectedIndex' ) != -1 )
                {
                    var replace = new Array();
                    replace['%old%'] = oldname;
                    var newShippSelIndex = Y.one( '#shippingtype' ).get( 'selectedIndex' );
                    var newname = Y.one( '#shippingtype' ).get( 'options' ).item( newShippSelIndex ).get( 'text' );
                    replace['%new%'] = newname;
                    if ( oldname )
                    {
                        ez18nAlert("The shipping method '%old%' is not available for your country of destination and was changed to '%new%'.", replace);
                    }
                }
            }
            Y.log('INFO2: ' + Y.Lang.dump(Y.one('#shippingtype').get('options')));
        };
        //ezjson('getshipping?country=' + country, doit);
    });
}

function ez18nAlert(text, args) 
{
    YUI().use( 'node', 'io-ez', function(Y) 
    {
        Y.io.ez( 'xrowecommerce::translate::', 
        {
            data: 'text=' + text,
            arguments: args,
            on: 
            {
                success: function( id, r, args)
                {
                    var data = r.responseJSON.content;
                    YUI().use('node', function(Y) 
                    {
                        for ( var x in args) 
                        {
                            data = data.replace(x, args[x]);
                        }
                        alert(data);
                    });
                }
            }
        });
    });
}
    
function changeShipping() 
{
    YUI().use( 'node', function(Y) 
    {
        if (Y.one( '#shipping-checkbox' ).get( 'checked' ) ) 
        {
            Y.one( '#shippinginfo' ).setStyle( 'display', 'none' );
        } 
        else 
        {
            Y.one( '#shippinginfo' ).setStyle('display', 'block' );
            
            if ( Y.one( '#s_country' ) && Y.one( '#country' ) && Y.one( '#country' ).get( 'selectedIndex' ) != '' )
            {
                updateSubdivisions( Y.one( '#s_country' ) );
            }
            
        }
    });
};

function toggleCOS()
{
    YUI().use( 'node', function(Y) 
    { 
        var container = Y.one('#cos-content');
        if ( container )
        {
            if ( container.getStyle('display') == 'block' )
            {
                container.setStyle('display', 'none');
            }
            else
            {
                container.setStyle('display', 'block');
            }
        }
    });
};


/**
 * @param node
 *            DomNode to receive click event
 * @param image
 *            Path to the full image
 * @param imagetext
 *            Alternate footer text
 * @param doubleclick
 *            Double or single click
 */
function generatePopup(node, image, imagetext, doubleclick) 
{
    YUI().use('node', 'overlay', 'imageloader', function(Y) 
    {
        var xy = Y.one(node).getXY();
        imageNode = Y.Node.create('<img />');
        imageNode.set('id', Y.guid());
        var overlay = new Y.Overlay( 
        {
            headerContent : 'Popup: Click to close.',
            bodyContent : imageNode,
            width : 'auto',
            height : 'auto',
            centered : node,
            visible : false,
            xy : [ xy[0] + 10, xy[1] + 35 ]
        });
        if (imagetext) 
        {
            overlay.set('footerContent', imagetext);
        }
        var myFirstGroup = new Y.ImgLoadGroup( 
        {
            timeLimit : 2
        });
        myFirstGroup.registerImage( 
        {
            domId : imageNode.get('id'),
           srcUrl : image
        });

        overlay.render();

        Y.on('click', Y.bind(overlay.hide, overlay), overlay.get('contentBox'));
        if (doubleclick) 
        {
            Y.on('dblclick', function(e) 
            {
                overlay.show();
            }, node);
        } 
        else 
        {
            Y.on('click', function(e) {
                overlay.show();
            }, node);
        }
    });
};

