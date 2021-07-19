<?php
class ModelCatalogSubscription extends Model {

	/*
		Checks if the given product was already bought
		return [BOOLEAN]
	*/
    public function checkIfPurchased($product_id) {
        $implode = array();

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT op.order_product_id, op.name FROM " . DB_PREFIX . "order o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) LEFT JOIN " . DB_PREFIX . "product p ON (op.product_id = p.product_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ") AND p.product_id = $product_id");

			if($query->row) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
    }

	public function checkIfSubscribed() {
		
	}

}