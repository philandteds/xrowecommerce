{def $default_value=cond( is_set( $variation_attribute.default_value ), $variation_attribute.default_value, '' )}
<input class="xrowproductstring" type="text" size="15" maxlength="255" id="DefaultXrowProductVariation_{$attribute.id}_xxxrownumberxxx_{$variation_attribute.attribute.identifier}" name="DefaultXrowProductVariation[{$attribute.id}][xxxrownumberxxx][{$variation_attribute.attribute.identifier}]" value="{$default_value|wash}" />