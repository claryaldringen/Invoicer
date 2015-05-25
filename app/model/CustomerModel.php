<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 24.5.15
 * Time: 19:49
 */

namespace App\Model;

use Nette;

class CustomerModel {

	/** @var Nette\Database\Context  */
	private $db;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}

	public function getCustomers() {
		return $this->db->query("SELECT * FROM customer")->fetchAll();
	}

	public function getCustomer($id) {
		return $this->db->query("SELECT * FROM customer WHERE id=?", $id)->fetch();
	}

	public function insertCustomer($customer) {
		$id = 0;
		if(!empty($customer)) {
			$this->db->query("INSERT INTO customer", $customer);
			$id = $this->db->getInsertId();
		}
		return $id;
	}

	public function updateCustomer($customer) {
		$id = $customer['id'];
		unset($customer['id']);
		if(!empty($customer)) {
			$this->db->query("UPDATE customer SET ", $customer, " WHERE id=?", $id);
		}
		return $id;
	}
}