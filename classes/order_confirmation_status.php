<?php
/**
 * @package xRowEcommerce
 * @class   OrderConfirmationStatus
 * @author  Serhey Dolgushev <dolgushev.serhey@gmail.com>
 * @date    25 Nov 2012
 **/

class OrderConfirmationStatus extends eZPersistentObject
{
	public static function definition() {
		return array(
			'fields'              => array(
				'order_id' => array(
					'name'     => 'OrderID',
					'datatype' => 'integer',
					'default'  => null,
					'required' => true
				),
				'is_sent' => array(
					'name'     => 'IsSent',
					'datatype' => 'integer',
					'default'  => 0,
					'required' => true
				),
				'sent_date' => array(
					'name'     => 'SentDate',
					'datatype' => 'integer',
					'default'  => time(),
					'required' => true
				)
			),
			'function_attributes' => array(),
			'keys'                => array( 'order_id' ),
			'sort'                => array( 'order_id' => 'asc' ),
			'class_name'          => __CLASS__,
			'name'                => 'order_confirmation_status'
		);
	}

	public static function fetchByOrderID( $id ) {
		return eZPersistentObject::fetchObject(
			self::definition(),
			null,
			array( 'order_id' => $id ),
			true
		);
	}

	public static function fetchList( $conditions = null, $limitations = null ) {
		return eZPersistentObject::fetchObjectList(
			self::definition(),
			null,
			$conditions,
			null,
			$limitations
		);
	}

	public function fetchListWrapper( $conditions = null, $limitations = null ) {
		return array(
			'result' => self::fetchList( $conditions, $limitations )
		);
	}
}
