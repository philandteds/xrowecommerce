<?php

class eZCouponWorkflowType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = 'ezcouponworkflow';
    const STATE_CANCEL = 2;
    const STATE_INVALID_CODE = 3;
    const STATE_VALID_CODE = 1;
    const BASE = 'event_ezcoupon';

    /*!
     Constructor
    */
    function eZCouponWorkflowType()
    {
        $this->eZWorkflowEventType( eZCouponWorkflowType::WORKFLOW_TYPE_STRING, ezpI18n::tr( 'kernel/workflow/event', "Coupon" ) );
        $this->setTriggerTypes( array(
            'shop' => array(
                'confirmorder' => array(
                    'before'
                )),
            'recurringorders' => array(
                'checkout' => array(
                    'before'
                )
            )
        ) );
    }

    function execute( $process, $event )
    {
        $http = eZHTTPTool::instance();
        $this->fetchInput( $http, eZCouponWorkflowType::BASE, $event, $process );
        if ( $process->attribute( 'event_state' ) == eZCouponWorkflowType::STATE_CANCEL )
        {
            return eZWorkflowEventType::STATUS_ACCEPTED;
        }
        if ( $process->attribute( 'event_state' ) != eZCouponWorkflowType::STATE_VALID_CODE )
        {
            $process->Template = array();
            $process->Template['templateName'] = 'design:workflow/coupon.tpl';
            $process->Template['templateVars'] = array(
                'process' => $process ,
                'event' => $event ,
                'base' => eZCouponWorkflowType::BASE
            );

            return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;

        }
        $ini = eZINI::instance( 'xrowcoupon.ini' );
        $coupon = new xrowCoupon( $event->attribute( "data_text1" ) );
        $attribute = $coupon->fetchAttribute();
        $data = $attribute->content();

        $description = $ini->variable( "CouponSettings", "Description" ) . " " . $event->attribute( "data_text1" );

        $parameters = $process->attribute( 'parameter_list' );
        $orderID = $parameters['order_id'];

        $order = eZOrder::fetch( $orderID );
        $orderItems = $order->attribute( 'order_items' );
        $addShipping = true;
        $newxml = new SimpleXMLElement( $order->attribute( 'data_text_1' ) );
        $shippintype = $newxml->shippingtype;
        foreach ( array_keys( $orderItems ) as $key )
        {
            $orderItem = & $orderItems[$key];
            if ( $orderItem->attribute( 'type' ) == "shippingcost" )
            {
                $shippingvalue = $orderItem->attribute( 'price' );
            }
            if ( $orderItem->attribute( 'description' ) == $description )
            {
                $addShipping = false;
                break;
            }
        }
        $shippingini = eZINI::instance( 'shipping.ini' );
        $shippinggateways = array();
        $shippinggateways = $shippingini->variable( "Settings", "FreeShippingHandlingGateways" );
        if ( (count( $shippinggateways ) != '0') AND ( $data['discount_type'] == ezCouponType::DISCOUNT_TYPE_FREE_SHIPPING ) AND ( !in_array($shippintype, $shippinggateways) ) )
        {
            $addShipping = false;
        }
        if ( $addShipping )
        {
            $price = self::getProductDiscountAmount( $order, $data );
            // Remove any existing order coupon before appending a new item
            $list = eZOrderItem::fetchListByType( $orderID, 'coupon' );
            if ( count( $list ) > 0 )
            {
                foreach ( $list as $item )
                {
                    $item->remove();
                }

            }
            if( $price > 0 ) {
	            $orderItem = new eZOrderItem( array(
	                'order_id' => $orderID ,
	                'description' => $description ,
	                'price' => round( $price, 2 ) * -1,
	                'type' => 'coupon' ,
	                'vat_is_included' => true ,
	                'vat_type_id' => 1
	            ) );
	            $orderItem->store();
            } else {
				$process->Template = array();
				$process->Template['templateName'] = 'design:workflow/coupon_not_applicable.tpl';
				$process->Template['templateVars'] = array(
					'process' => $process,
					'event'   => $event,
					'base'    => eZCouponWorkflowType::BASE
				);

				return eZWorkflowType::STATUS_FETCH_TEMPLATE_REPEAT;            	
            }
        }

        return eZWorkflowEventType::STATUS_ACCEPTED;
    }

    function fetchInput( &$http, $base, &$event, &$process )
    {

        $var = $base . "_code_" . $event->attribute( "id" );
        $cancel = $base . "_CancelButton_" . $event->attribute( "id" );
        $select = $base . "_SelectButton_" . $event->attribute( "id" );
        if ( $http->hasPostVariable( $cancel ) and $http->postVariable( $cancel ) )
        {
            $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_CANCEL );
            return;
        }
        if ( $http->hasPostVariable( $var ) and $http->hasPostVariable( $select ) and count( $http->postVariable( $var ) ) > 0 )
        {
            $coupon = new xrowCoupon( $http->postVariable( $var ) );
            $event->setAttribute( "data_text1", $coupon->code );
            if ( $coupon->isValid() )
            {
                $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_VALID_CODE );
                return;
            }
            else
            {
                $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_INVALID_CODE );
                return;
            }
        }
        $parameters = $process->attribute( 'parameter_list' );

        $order = eZOrder::fetch( $parameters['order_id'] );
        if ( $order instanceof eZOrder )
        {
            $xml = new SimpleXMLElement( $order->attribute( 'data_text_1' ) );

            if ( $xml != null )
            {
                $code = (string) $xml->coupon_code;
                // we have an empty code this mean a coupon has been supplied at the user register page, so we cancle here
                if ( ! $code )
                {
                    $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_CANCEL );
                    return;
                }
                $coupon = new xrowCoupon( $code );
                if ( $coupon->isValid() )
                {
                    $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_VALID_CODE );
                    $event->setAttribute( "data_text1", $coupon->code );
                    return;
                }
                else
                {
                    $process->setAttribute( 'event_state', eZCouponWorkflowType::STATE_INVALID_CODE );
                    return;
                }
            }
        }
    }

	public static function getProductDiscountAmount( eZOrder $order, $couponData ) {
		$discountableAmount = 0;
		$discountAmount     = 0;

		$products = $order->attribute( 'product_items' );
		foreach( $products as $product ) {
			$options = $product['item_object']->attribute( 'option_list' );
			if( count( $options ) === 0 ) {
				// SKU is not selected, so it is not MK poduct
				continue;
			}

			$object = $product['item_object']->attribute( 'contentobject' );
			$SKU    = $options[0]->attribute( 'value' );
			if( self::isSaleProduct( $object, $SKU ) === false ) {
				$discountableAmount += $product['total_price_ex_vat'];
			}
		}

		if( $discountableAmount > 0 ) {
			if( $couponData['discount_type'] == ezCouponType::DISCOUNT_TYPE_FLAT ) {
				$discountAmount = $couponData['discount'];
			} elseif( $couponData['discount_type'] == ezCouponType::DISCOUNT_TYPE_FREE_SHIPPING ) {
				$discountAmount = $shippingvalue;
			} else {
				$discountAmount = $discountableAmount * $couponData['discount'] / 100;
			}
		}

		return $discountAmount;
	}

	public static function isSaleProduct( eZContentObject $product, $SKU ) {
		$dataMap = $product->attribute( 'data_map' );
		if( (bool) $dataMap['override_price']->attribute( 'content' ) ) {
			return true;
		}

		$currentRegion = eZLocale::instance()->LocaleINI['default']->variable( 'RegionalSettings', 'Country' );
		$db = eZDB::instance();
		$q  = '
			SELECT product_price.*
			FROM product_price
			WHERE
				product_price.LongCode = "' . $db->escapeString( $SKU ) . '"
				AND product_price.Region = "' . $db->escapeString( $currentRegion ) . '"';
		$r  = $db->arrayQuery( $q );
		if(
			count( $r ) > 0
			&& (bool) $r[0]['Override']
		) {
			return true;
		}

		return false;
	}
}

eZWorkflowEventType::registerEventType( eZCouponWorkflowType::WORKFLOW_TYPE_STRING, "ezcouponworkflowtype" );

?>
