<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 25.5.15
 * Time: 5:56
 */

namespace App\Model;

use Nette;

class InvoiceModel {

	const INVOICE = 2;

	private $unpaired;

	/** @var Nette\Database\Context  */
	private $db;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}

	public function insertInvoice($data) {

		$insert = array(
			'user_id' => 1,
			'customer_id' => $data['customerId'],
			'issue_date' => date('Y-m-d H:i:s', strtotime($data['issueDate'])),
			'payment_date' => date('Y-m-d H:i:s', strtotime($data['paymentDate']))
		);

		$items = $data['items'];

		$this->db->beginTransaction();
		try {
			$this->db->query("INSERT INTO variable_symbol", $insert);
			$vsId = $this->db->getInsertId();
			foreach($items as $key => $item) {
				$items[$key]['variable_symbol_id'] = $vsId;
			}
			$this->db->query("INSERT INTO item ", $items);
			if($data['type'] == self::INVOICE) {
				$this->db->query("INSERT INTO invoice", array('variable_symbol_id' => $vsId));
			}
			$this->db->commit();
		} catch(Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}
		return $vsId;
	}

	public function invoiceFromVs($vsId, $send = false) {
		$this->db->beginTransaction();
		try {
			$insert = array('variable_symbol_id' => $vsId);
			if($send) $insert['send'] = date('Y-m-d H:i:s');
			$this->db->query("INSERT INTO invoice", $insert);
			$this->db->query("UPDATE variable_symbol SET payment_date=? WHERE id=?", date('Y-m-d H:i:s'), $vsId);
			$this->db->commit();
		} catch(Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}
		return $this;
	}

	public function getPaymentData($vsId) {
		$sql = "SELECT vs.customer_id,issue_date,payment_date,i.* FROM variable_symbol vs
			JOIN invoice i ON i.variable_symbol_id=vs.id
			WHERE vs.id=?";

		return $this->db->query($sql, $vsId)->fetch();
	}

	public function getItems($vsId) {
		return $this->db->query("SELECT * FROM item WHERE variable_symbol_id=?", $vsId)->fetchAll();
	}

	public function checkPayment($vsId, $price) {
		if(!isset($this->unpaired)) {
			$sql = "SELECT vs.id,SUM(i.count*i.price) AS price FROM variable_symbol vs
			JOIN item i ON i.variable_symbol_id=vs.id
			LEFT JOIN invoice inv ON inv.variable_symbol_id=vs.id
			WHERE inv.id IS NULL AND vs.status = 'active'
			GROUP BY vs.id";

			$this->unpaired = $this->db->query($sql)->fetchPairs();
		}

		if(!isset($this->unpaired[$vsId])) return "Undefined variable symbol $vsId.";
		if($this->unpaired[$vsId] != $price) return "Bad amount for variable symbol $vsId. It get $price but expected amount is {$this->unpaired[$vsId]}.";
		return true;
	}

}