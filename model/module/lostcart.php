<?php
class ModelModuleLostcart extends Model {

	public function getAllRecords() {
		
		$query = $this->db->query("SELECT crt.`cart_id`, crt.`customer_id`, crt.`product_id`, crt.`date_added`, crt.`quantity`, user.`firstname`, user.`lastname`, user.`email`, user.`address_id`, adr.`zone_id` FROM `" . DB_PREFIX . "cart` crt JOIN `" . DB_PREFIX . "customer` user ON (crt.customer_id = user.customer_id) JOIN `" . DB_PREFIX . "address` adr ON (user.customer_id = adr.customer_id) WHERE crt.`customer_id` != 0");
		
		return ($query->num_rows)?$query->rows:false;
		
	}
	public function getRecordsTreeHour($start,$end) {
		if($start && $end)
		{
			$query = $this->db->query("SELECT crt.`cart_id`, crt.`customer_id`, crt.`product_id`, crt.`date_added`, crt.`quantity`, user.`firstname`, user.`lastname`, user.`email`, user.`address_id`, adr.`zone_id` FROM `" . DB_PREFIX . "cart` crt JOIN `" . DB_PREFIX . "customer` user ON (crt.customer_id = user.customer_id) JOIN `" . DB_PREFIX . "address` adr ON (user.customer_id = adr.customer_id) WHERE crt.`customer_id` != 0 AND crt.`date_added` BETWEEN '$start' AND '$end'");
			
		return ($query->num_rows)?$query->rows:false;
		}
		else
		{
			return false;
		}
		
	}
	public function getRecordsIdAndHour($customer_id,$start,$end) {
		if($customer_id && $start && $end)
		{
			$query = $this->db->query("SELECT crt.`cart_id`, crt.`customer_id`, crt.`product_id`, crt.`date_added`, crt.`quantity`, user.`firstname`, user.`lastname`, user.`email`, user.`address_id`, adr.`zone_id` FROM `" . DB_PREFIX . "cart` crt JOIN `" . DB_PREFIX . "customer` user ON (crt.customer_id = user.customer_id) JOIN `" . DB_PREFIX . "address` adr ON (user.customer_id = adr.customer_id) WHERE crt.`customer_id` = " . (int) $customer_id. " AND crt.`date_added` BETWEEN '$start' AND '$end'");
			
		return ($query->num_rows)?$query->rows:false;
		}
		else
		{
			return false;
		}
		
	}
	public function getProducts($prod_id,$lng) {
		$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store p2s LEFT JOIN " . DB_PREFIX . "product p ON (p2s.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2s.product_id = '" . (int)$prod_id . "' AND pd.language_id = '" . (int)$lng . "' AND p.date_available <= NOW() AND p.status = '1'");
		
		return ($product_query->num_rows)?$product_query->rows:false;
	}
}