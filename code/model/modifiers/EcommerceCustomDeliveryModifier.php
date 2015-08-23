<?php

/**
 * @author Nicolaas [at] sunnysideup.co.nz
 * @package: ecommerce
 * @sub-package: examples
 * @description: This is an example modifier that developers can use
 * as a starting point for writing their own modifiers.
 *
 **/
class EcommerceCustomDeliveryModifier extends OrderModifier {

// ######################################## *** model defining static variables (e.g. $db, $has_one)

	/**
	 * add extra fields as you need them.
	 *
	 **/
	public static $db = array(
		"PostalCode" => "Varchar(10)",
		"HasSpecialProducts" => "Boolean(1)"
	);


// ######################################## *** cms variables + functions (e.g. getCMSFields, $searchableFields)

	/**
	 * standard SS method
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}

	public static $singular_name = "Delivery Charge";
		function i18n_singular_name() { return self::$singular_name;}

	public static $plural_name = "Delivery Charges";
		function i18n_plural_name() { return self::$plural_name;}

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
	public function runUpdate($force = false) {
		$this->checkField("PostalCode");
		$this->checkField("IsLocal");
		parent::runUpdate($force);
	}


// ######################################## *** form functions (e. g. Showform and getform)

	/**
	 * standard OrderModifier Method
	 * Should we show a form in the checkout page for this modifier?
	 */
	public function ShowForm() {
		return false;
	}

	/**
	 * Should the form be included in the editable form
	 * on the checkout page?
	 * @return Boolean
	 */
	public function ShowFormInEditableOrderTable() {
		return false;
	}

	/**
	 * Should the form be included in the editable form
	 * on the checkout page?
	 * @return Boolean
	 */
	public function ShowFormOutsideEditableOrderTable() {
		return false;
	}

// ######################################## *** template functions (e.g. ShowInTable, TableTitle, etc...) ... USES DB VALUES

	/**
	 * standard OrderModifer Method
	 * Tells us if the modifier should take up a row in the table on the checkout page.
	 * @return Boolean
	 */
	public function ShowInTable() {
		return true;
	}

	/**
	 * standard OrderModifer Method
	 * Tells us if the modifier can be removed (hidden / turned off) from the order.
	 * @return Boolean
	 */
	public function CanBeRemoved() {
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
	protected function LiveName() {
		return EcommerceDBConfig::current_ecommerce_db_config()->DeliveryChargeTitle." (postal code: ".$this->LivePostalCode().")";
	}

	protected function LiveCalculatedTotal() {
		$hasSpecialProducts =  $this->LiveHasSpecialProducts();
		$postalCodeObject =  $this->MyPricePostalCode();
		if(!$postalCodeObject) {
			$postalCodeObject = EcommerceDBConfig::current_ecommerce_db_config();
		}
		if($hasSpecialProducts) {
			return $postalCodeObject->PriceWithApplicableProducts;
		}
		else {
			return $postalCodeObject->PriceWithoutApplicableProducts;
		}
	}



// ######################################## *** Type Functions (IsChargeable, IsDeductable, IsNoChange, IsRemoved)



// ######################################## *** standard database related functions (e.g. onBeforeWrite, onAfterWrite, etc...)

	function onBeforeWrite() {
		parent::onBeforeWrite();
	}

// ######################################## *** debug functions

	/**
	 * @return boolean
	 */
	function LiveHasSpecialProducts(){
		$applicableProducts = $this->SelectedProductsArray();
		if(count($applicableProducts)) {
			$order = $this->Order();
			if($order) {
				forech($item as $order->OrderItems()) {
					if(in_array($item->Product()->ID, $applicableProducts)) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 *
	 * @return String
	 */
	function LivePostalCode(){
		$postalCode = "unknown";
		$order = $this->Order();
		if($order) {
			$shippingAddress = $order->CreateOrReturnExistingAddress("ShippingAddress");
			$postalCode = $shippingAddress->PostalCode
			if(!$postalCode) {
				$billingAddress = $order->CreateOrReturnExistingAddress("BillingAddress");
				$postalCode = $billingAddress->ShippingPostalCode;
			}
		}
		return $postalCode;
	}


	/**
	 *
	 * @array
	 */
	private function SelectedProductsArray(){
		$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
		return $ecommerceConfig->DeliveryChargedProducts()->map("ID", "ID")->toArray();
	}

	/**
	 *
	 * @return EcommerceCustomDeliveryPostalCode | null
	 */
	private function MyPricePostalCode(){
		$ecommerceConfig = EcommerceDBConfig::current_ecommerce_db_config();
		$postalCode = intval($this->LivePostalCode());
		if($postalCode) {
			return $ecommerceConfig->SpecialPricePostalCodes()
				->where(
					"$postalCode >= \"PostalCodeLowestNumber\" AND $postalCode <= \"PostalCodeHighestNumber\" "
				)->First();
		}
		return null;
	}



}
