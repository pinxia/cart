<?php

class Product {
	
	private $id;
	private $name;
	private $description;
	private $msrp;
	private $price;
	private $is_on_sale;
	private $quantity_in_stock;

	const SALE_DISCOUNT = .2;

	public function __construct($values = array()) {
		if ($values) {
			$this->setAll($values);
                }
	}

	public function __get($prop) {
		if (property_exists('Product', $prop)) {
			return $this->$prop;
                }
		else {
			return null;
                }
	}

	public function __set($prop, $value) {
		if (property_exists('Product', $prop))
			$this->$prop = $value;
	}

	public function finalPrice() {
		return $this->is_on_sale ? $this->price*(1-self::SALE_DISCOUNT) : $this->price;
	}

	public function setAll($values) {
		foreach ($values as $key => $value) {
			$this->$key = $value;
		}
	}

}

?>