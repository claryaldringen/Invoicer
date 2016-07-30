<?php

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

	public function getInvoices() {
		$sql = "SELECT i.id,i.variable_symbol_id,vs.issue_date,vs.payment_date,c.name,c.street,c.number,c.post_code,c.city,c.ico,c.dic
			FROM invoice i
			JOIN variable_symbol vs ON vs.id = i.variable_symbol_id
			JOIN customer c ON c.id=vs.customer_id
			WHERE vs.status='active'
			ORDER BY id DESC";
		$rows = $this->db->query($sql)->fetchAll();

		foreach($rows as &$row) {
			$row['items'] = $this->db->query("SELECT * FROM item WHERE variable_symbol_id=?", $row['variable_symbol_id'])->fetchAll();
		}

		return $rows;
	}

	public function getPreInvoices() {
		$sql = "SELECT vs.id,vs.issue_date,vs.payment_date,c.name,c.street,c.number,c.post_code,c.city,c.ico,c.dic
			FROM variable_symbol vs
			JOIN customer c ON c.id=vs.customer_id
			LEFT JOIN invoice i ON vs.id = i.variable_symbol_id
			WHERE i.id IS NULL AND vs.status='active'
			ORDER BY vs.id DESC";

		$rows = $this->db->query($sql)->fetchAll();

		foreach($rows as &$row) {
			$row['items'] = $this->db->query("SELECT * FROM item WHERE variable_symbol_id=?", $row['id'])->fetchAll();
		}

		return $rows;
	}

	public function delete($variableSymbolId) {
		$this->db->beginTransaction();
		try {
			$result = $this->db->query("DELETE FROM invoice WHERE variable_symbol_id=?", $variableSymbolId);
			if ($result->getRowCount()) {
				$row = $this->db->query("SELECT COUNT(*) AS cnt FROM invoice")->fetch();
				$count = $row['cnt'];
				$this->db->query("ALTER TABLE invoice AUTO_INCREMENT=?", ++$count);
			}
			$this->db->query("UPDATE variable_symbol SET status=? WHERE id=?", 'deleted', $variableSymbolId);
		}catch(\Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}
		$this->db->commit();
		return $this;
	}

	public function getCyclicPayments() {
		$result = [];

		$sql = "SELECT * FROM cyclic_payments cp
			JOIN variable_symbol vs ON vs.id=cp.variable_symbol_id
			WHERE next_check < NOW();";

		$this->db->beginTransaction();
		try {
			$rows = $this->db->query($sql)->fetchAll();
			foreach ($rows as $row) {
				$insert = [
					'user_id' => $row['user_id'],
					'customer_id' => $row['customer_id'],
					'issue_date' => date('Y-m-d H:i:s'),
					'payment_date' => date('Y-m-d H:i:s', time() + 3600 * 24 * 14),
					'action_id' => $row['action_id'],
					'params' => $row['params'],
				];
				$this->db->query("INSERT INTO variable_symbol", $insert);
				$newVs = $this->db->getInsertId();

				$items = $this->db->query("SELECT * FROM item WHERE variable_symbol_id=?", $row['variable_symbol_id'])->fetchAll();
				foreach ($items as &$item) {
					unset($item['id']);
					$item['variable_symbol_id'] = $newVs;
				}
				$this->db->query("INSERT INTO item", $items);

				$this->db->query("UPDATE cyclic_payments SET next_check=? WHERE variable_symbol_id=?", date('Y-m-d H:i:s', strtotime($row['next_check']) + 3600 * 24 * 365), $row['variable_symbol_id']);
				$result[] = ['vs' => $newVs, 'customer_id' => $row['customer_id']];
			}
		} catch(\Exception $ex) {
			$this->db->rollBack();
			throw $ex;
		}
		$this->db->commit();
		return $result;
	}

}