<?php

class EcommerceCustomDeliveryPostalCode extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(255)',
        'PostalCodeLowestNumber' => 'Int',
        'PostalCodeHighestNumber' => 'Int',
        'PriceWithoutApplicableProducts' => 'Currency',
        'PriceWithApplicableProducts' => 'Currency'
    );

    private static $singular_name = "Postal Code Special Delivery Zone";

    private static $belongs_many_many = array(
        "EcommerceDBConfigs" => "EcommerceDBConfig"
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'PostalCodeLowestNumber' => 'Postal Code From ',
        'PostalCodeHighestNumber' => 'Postal Code To',
        'PriceWithoutApplicableProducts' => 'Without Special Products',
        'PriceWithApplicableProducts' => 'With Special Products'
    );
    private static $field_labels = array(
        'PostalCodeLowestNumber' => 'Lowest postal code (e.g. 2011)',
        'PostalCodeHighestNumber' => 'Highest postal code (e.g. 2015)',
        'PriceWithoutApplicableProducts' => 'Delivery charge for orders without special products',
        'PriceWithApplicableProducts' => 'Delivery charge for orders with special products'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeFieldFromTab("Root", "EcommerceDBConfigs");
        return $fields;
    }
}
