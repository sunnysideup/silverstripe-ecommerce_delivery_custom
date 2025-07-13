<?php

namespace Sunnysideup\EcommerceDeliveryCustom\Model\Modifiers;



use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
use Sunnysideup\Ecommerce\Model\Address\ShippingAddress;
use Sunnysideup\Ecommerce\Model\Address\BillingAddress;
use Sunnysideup\Ecommerce\Model\OrderModifier;



/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: examples
 * @description: This is an example modifier that developers can use
 * as a starting point for writing their own modifiers.
 *
 **/
class EcommerceCustomDeliveryModifier extends OrderModifier
{

// ######################################## *** model defining static variables (e.g. $db, $has_one)

    /**
     * add extra fields as you need them.
     *
     **/
    public static $db = array(
        "PostalCode" => "Varchar(10)",
        "SpecialProductCount" => "Int",
        "NonSpecialProductCount" => "Int"
    );


// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

    /**
     * standard SS method
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
    }

    public static $singular_name = "Delivery Charge";
    public function i18n_singular_name()
    {
        return self::$singular_name;
    }

    public static $plural_name = "Delivery Charges";
    public function i18n_plural_name()
    {
        return self::$plural_name;
    }

// ######################################## *** other (non) static variables (e.g. protected static $special_name_for_something, protected $order)


// ######################################## *** CRUD functions (e.g. canEdit)
// ######################################## *** init and update functions

    /**
     * For all modifers with their own database fields, we need to include this...
     * It will update each of the fields.
     * Within this method, we need to create the methods
     * Live{functionName}
     * e.g LiveMyField() and LiveMyReduction() in this case...
     * The OrderModifier already updates the basic database fields.
     * @param Bool $force - run it, even if it has run already
     */
    public function runUpdate($force = false)
    {
        $this->checkField("PostalCode");
        $this->checkField("SpecialProductCount");
        $this->checkField("NonSpecialProductCount");
        parent::runUpdate($force);
    }


// ######################################## *** form functions (e. g. Showform and getform)

    /**
     * standard OrderModifier Method
     * Should we show a form in the checkout page for this modifier?
     */
    public function ShowForm()
    {
        return false;
    }

    /**
     * Should the form be included in the editable form
     * on the checkout page?
     * @return Boolean
     */
    public function ShowFormInEditableOrderTable()
    {
        return false;
    }

    /**
     * Should the form be included in the editable form
     * on the checkout page?
     * @return Boolean
     */
    public function ShowFormOutsideEditableOrderTable()
    {
        return false;
    }

// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

    /**
     * standard OrderModifer Method
     * Tells us if the modifier should take up a row in the table on the checkout page.
     * @return Boolean
     */
    public function ShowInTable()
    {
        return true;
    }

    /**
     * standard OrderModifer Method
     * Tells us if the modifier can be removed (hidden / turned off) from the order.
     * @return Boolean
     */
    public function CanBeRemoved()
    {
        return false;
    }

// ######################################## ***  inner calculations.... USES CALCULATED VALUES



// ######################################## *** calculate database fields: protected function Live[field name]  ... USES CALCULATED VALUES

    /**
     * if we want to change the default value for the Name field
     * (defined in the OrderModifer class) then we can do this
     * as shown in the method below.
     * You may choose to return an empty string or just a standard message.
     **/
    protected function LiveName()
    {
        if ($obj = $this->MyPostalCodeObject()) {
            $title = $obj->Title;
        } else {
            $title = EcommerceDBConfig::current_ecommerce_db_config()->DeliveryChargeTitle;
        }
        if ($postalCode = $this->LivePostalCode()) {
            if ($postalCodeLabel = _t("EcommerceCustomDeliveryModifier.POSTAL_CODE", "postal code")) {
                $title .= " (".$postalCodeLabel.": ".$postalCode.")";
            }
        } else {
            //do nothing...
        }
        return $title;
    }

    protected function LiveCalculatedTotal()
    {
        $specialCount =  $this->LiveSpecialProductCount();
        $nonSpecialCount =  $this->LiveNonSpecialProductCount();
        
        $postalCodeObjectOrDefaultConfig =  $this->MyPostalCodeObject();
        if (! $postalCodeObjectOrDefaultConfig) {
            $postalCodeObjectOrDefaultConfig = EcommerceDBConfig::current_ecommerce_db_config();

        }
        $specialPrice = $postalCodeObjectOrDefaultConfig->PriceWithApplicableProducts;
        $nonSpecialPrice = $postalCodeObjectOrDefaultConfig->PriceWithoutApplicableProducts;
        if ($specialCount) {
            return $specialPrice;    
        } else {
            return $nonSpecialPrice;
        }
        return 0;
    }



// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)



// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
    }

// ######################################## *** debug functions

    public function LiveNonSpecialProductCount()
    {
        return $this->ProductCountForTotal(false);
    }

    public function LiveSpecialProductCount()
    {
        return $this->ProductCountForTotal(true);
    }

    /**
     * @return int
     */
    protected function ProductCountForTotal($special = false)
    {
        $specialCount = 0;
        $nonSpecialCount = 0;
        $applicableProducts = $this->SelectedProductsArray();
        if (count($applicableProducts)) {
            $order = $this->Order();
            if ($order) {
                foreach ($order->OrderItems() as $item) {
                    if ($special && in_array($item->Product()->ID, $applicableProducts)) {
                        $specialCount += $item->Quantity;
                    } else {
                        $nonSpecialCount += $item->Quantity;
                    }
                }
            }
        }
        if ($special) {
            return $specialCount;
        } else {
            return $nonSpecialCount;
        }
    }

    /**
     *
     * @return String
     */
    public function LivePostalCode()
    {
        $postalCode = "";
        $order = $this->Order();
        if ($order) {
            $shippingAddress = $order->CreateOrReturnExistingAddress(ShippingAddress::class);
            $postalCode = $shippingAddress->ShippingPostalCode;
            if (!$postalCode) {
                $billingAddress = $order->CreateOrReturnExistingAddress(BillingAddress::class);
                $postalCode = $billingAddress->PostalCode;
            }
        }
        if (intval($postalCode) > 0) {
            return $postalCode;
        }
        return "";
    }


    /**
     *
     * @array
     */
    private function SelectedProductsArray()
    {
        $ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
        return $ecommerceConfig->DeliverySpecialChargedProducts()->map("ID", "ID")->toArray();
    }

    /**
     *
     * @return EcommerceCustomDeliveryPostalCode | null
     */
    private function MyPostalCodeObject()
    {
        $ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
        $postalCode = intval($this->LivePostalCode());
        if ($postalCode) {
            return $ecommerceConfig->SpecialPricePostalCodes()
                ->where(
                    "$postalCode >= \"PostalCodeLowestNumber\" AND $postalCode <= \"PostalCodeHighestNumber\" "
                )->First();
        }
        return null;
    }
}

