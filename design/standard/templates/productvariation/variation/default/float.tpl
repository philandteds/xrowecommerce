{def $default_value=cond( is_set( $variation_attribute.default_value ), $variation_attribute.default_value, '' )}
<input class="xrowproductnumber" type="text" size="5" maxlength="15" id="DefaultXrowProductVariation_{$attribute.id}_xxxrownumberxxx_{$variation_attribute.attribute.identifier}" name="DefaultXrowProductVariation[{$attribute.id}][xxxrownumberxxx][{$variation_attribute.attribute.identifier}]" value="{$default_value|l10n('number')|wash}" />