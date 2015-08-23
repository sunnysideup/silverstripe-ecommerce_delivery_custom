<?php

class EcommerceCustomDeliveryPostalCode extends DataObject {

	private static $db = array(
		'PostalCodeLowestNumber' => 'Int',
		'PostalCodeHighestNumber' => 'Int',
		'PriceWithoutApplicableProducts' => 'Currency',
		'PriceWithApplicableProducts' => 'Currency'
	);


	private static $field_labels = array(
		'PostalCodeLowestNumber' => 'Lowest postal code (e.g. 2011)',
		'PostalCodeHighestNumber' => 'Highest postal code (e.g. 2015)',
		'PriceWithoutApplicableProducts' => 'Delivery charge for orders without special products',
		'PriceWithApplicableProducts' => 'Delivery charge for orders with special products'
	);
}
