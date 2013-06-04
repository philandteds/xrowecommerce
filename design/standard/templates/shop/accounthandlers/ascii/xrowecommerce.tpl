{def $shiplist=fetch( 'shipping', 'list_all_methods' )}{def $gateways=fetch( 'xrowecommerce', 'list_all_gateways' )}
{if and( eq(ezini( 'Fields', 'company_name', 'xrowecommerce.ini' ), 'enabled' ), $order.account_information.company_name)}{'Company'|i18n('extension/xrowecommerce')}: {$order.account_information.company_name}{array('0013')|chr()}{/if}
{if and( eq(ezini( 'Fields', 'company_additional', 'xrowecommerce.ini' ), 'enabled' ), $order.account_information.company_additional)}{'Company additional information'|i18n('extension/xrowecommerce')}: {$order.account_information.company_additional}{array('0013')|chr()}{/if}
{array('0013')|chr()}
{if and( eq(ezini( 'Fields', 'tax_id', 'xrowecommerce.ini' ).enabled, 'true' ), $order.account_information.tax_id)}{'Tax ID'|i18n('extension/xrowecommerce')}: {$order.account_information.tax_id} {if $order.account_information.tax_id_valid|not} ({'unconfirmed'|i18n('extension/xrowecommerce')}){/if}{array('0013')|chr()}{/if}
{if eq($order.account_information.shipping,0)}{'Billing Details'|i18n( 'extension/xrowecommerce')}{array('0013')|chr()}
----------------------------------{array('0013')|chr()}
{else}
{'Shipping & Billing Details'|i18n( 'extension/xrowecommerce')}{array('0013')|chr()}
----------------------------------{array('0013')|chr()}{/if}
{'Name'|i18n( 'extension/xrowecommerce')}: {$order.account_information.first_name} {$order.account_information.mi} {$order.account_information.last_name}{array('0013')|chr()}
{'Address1'|i18n( 'extension/xrowecommerce')}: {$order.account_information.address1}{array('0013')|chr()}
{if gt(count($order.account_information.address2),0)}{'Address2'|i18n( 'extension/xrowecommerce')}: {$order.account_information.address2}{array('0013')|chr()}{/if}
{'City'|i18n( 'extension/xrowecommerce')}: {$order.account_information.city}{array('0013')|chr()}
{if $order.account_information.state}{'State'|i18n('extension/xrowecommerce')}: {$order.account_information.state|get_state($order.account_information.country)}{array('0013')|chr()}{/if}
{'Country'|i18n( 'extension/xrowecommerce')}: {*$order.account_information.country*}{foreach fetch( 'xrowecommerce', 'get_country_list') as $tmp_country}{if $tmp_country.Alpha3|eq($order.account_information.country)}{set $country=$tmp_country}{$tmp_country.Name}{break}{/if}{/foreach}{array('0013')|chr()}
{'Zip code'|i18n( 'extension/xrowecommerce')}: {$order.account_information.zip}{array('0013')|chr()}
{'Email'|i18n( 'extension/xrowecommerce')}: {$order.account_information.email}{array('0013')|chr()}
{'Phone'|i18n( 'extension/xrowecommerce')}: {$order.account_information.phone}{array('0013')|chr()}
{array('0013')|chr()}
{*'Shipping'|i18n( 'extension/xrowecommerce')}: {foreach  $shiplist as $method}{if $method.identifier|eq($order.account_information.shippingtype)}{$method.name}{/if}{/foreach}{array('0013')|chr()*}
{if eq($order.account_information.shipping,0)}{'Shipping Details'|i18n( 'extension/xrowecommerce')}:{array('0013')|chr()}
----------------------------------{array('0013')|chr()}
{'Name'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_first_name} {$order.account_information.s_mi} {$order.account_information.s_last_name}{array('0013')|chr()}
{'Address1'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_address1}{array('0013')|chr()}
{if gt(count($order.account_information.s_address2),0)}{'Address2'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_address2}{array('0013')|chr()}{/if}
{'City'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_city}{array('0013')|chr()}
{if $order.account_information.s_state}{'State'|i18n('extension/xrowecommerce')}: {$order.account_information.s_state|get_state($order.account_information.s_country)}{array('0013')|chr()}{/if}
{'Country'|i18n( 'extension/xrowecommerce')}: {*$order.account_information.s_country*}{foreach fetch( 'xrowecommerce', 'get_country_list') as $tmp_country}{if $tmp_country.Alpha3|eq($order.account_information.s_country)}{set $country=$tmp_country}{$tmp_country.Name}{break}{/if}{/foreach}{array('0013')|chr()}
{'Zip code'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_zip}{array('0013')|chr()}
{'Phone'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_phone}{array('0013')|chr()}
{'Email'|i18n( 'extension/xrowecommerce')}: {$order.account_information.s_email}{array('0013')|chr()}
{/if}
{array('0013')|chr()}
{if ezini( 'Fields', 'NoPartialDelivery', 'xrowecommerce.ini' ).enabled|eq( 'true' )}{'Partial delivery'|i18n('extension/xrowecommerce')}: {if $order.account_information.no_partial_delivery}{'No'|i18n('extension/xrowecommerce')}{else}{'Yes'|i18n('extension/xrowecommerce')}{/if}{array('0013')|chr()}{/if}

{if and(ezini( 'Fields', 'Reference', 'xrowecommerce.ini' ).enabled|eq( 'true' ), $order.account_information.reference)}{'Reference'|i18n('extension/xrowecommerce')}: {$order.account_information.reference}{array('0013')|chr()}{/if}
{if and(ezini( 'Fields', 'Message', 'xrowecommerce.ini' ).enabled|eq( 'true' ), $order.account_information.message)}{'Order Comments'|i18n('mk/billing')}:{array('0013')|chr()}{$order.account_information.message}{array('0013')|chr()}{/if}
{array('0013')|chr()}
{if and( is_set($order.account_information.paymentmethod), $order.account_information.paymentmethod|trim|ne('Unknown') )}
{'Payment method'|i18n('extension/xrowecommerce')}: {if $gateways|count|gt(0)}{foreach $gateways as $gateway}{if $order.account_information.paymentmethod|eq($gateway.value)}{$gateway.Name|wash}{/if}{/foreach}{array('0013','0013')|chr()}{/if}
