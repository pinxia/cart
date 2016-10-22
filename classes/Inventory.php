<?php

class Inventory {

	private $items = array();

	public function __construct() {
		$this->loadJsonFile();
	}

	private function loadJsonFile() {
		
		// load the file
		$file = file_get_contents('inventory.json');
		$json = json_decode($file, true);

		// process the json
		foreach ($json['items'] as $key => $value) {
			$product = new Product();
			$product->setAll($value);
			$this->items[$value['id']] = $product;
		}

	}

	public function getItems() {
		return $this->items;
	}

	public function getItem($id) {
		if (isset($this->items[$id])) {
			return $this->items[$id];
                }
		else
			return null;
	}

}

?>