<?php

namespace Sunnysideup\EcommerceDeliveryCustom\Extensions;








use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceDeliveryCustom\Model\EcommerceCustomDeliveryPostalCode;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\CurrencyField;
use Sunnysideup\Ecommerce\Forms\Gridfield\Configs\GridFieldEditOriginalPageConfigWithDelete;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\DataExtension;




class EcommerceCustomDeliveryEcommerceDBConfigExtension extends DataExtension
{
    private static $db = array(
        'DeliveryChargeTitle' => 'Varchar',
        'PriceWithoutApplicableProducts' => 'Currency',
        'PriceWithApplicableProducts' => 'Currency'
    );

    private static $many_many = array(
        'DeliverySpecialChargedProducts' => Product::class,
        'SpecialPricePostalCodes' => EcommerceCustomDeliveryPostalCode::class
    );

    private static $field_labels = array(
        'PriceWithoutApplicableProducts' => 'Standard Delivery Charge (rest of NZ) without Special Products in Order',
        'PriceWithApplicableProducts' => 'Standard Delivery Charge (rest of NZ) with Special Products in Order',
        'DeliverySpecialChargedProducts' => 'List of Products with Special Delivery Charge',
        'SpecialPricePostalCodes' => 'List of Postal Codes With Special Delivery Charge'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeFieldFromTab("Root", "DeliverySpecialChargedProducts");
        $fields->removeFieldFromTab("Root", "SpecialPricePostalCodes");
        $fields->addFieldsToTab(
            "Root.Delivery",
            array(
                new TextField("DeliveryChargeTitle", "Delivery Charge Title"),
                new CurrencyField("PriceWithoutApplicableProducts", "Standard Delivery Charge without Special Products in order"),
                new CurrencyField("PriceWithApplicableProducts", "Standard Delivery Charge with Special Products in order"),
                new GridField("DeliverySpecialChargedProducts", "Products with special Delivery Charge", $this->owner->DeliverySpecialChargedProducts(), GridFieldEditOriginalPageConfigWithDelete::create()),
                new GridField("SpecialPricePostalCodes", "Special Price Postal Codes", $this->owner->SpecialPricePostalCodes(), GridFieldConfig_RelationEditor::create())
            )
        );
    }
}
