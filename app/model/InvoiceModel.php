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
	}

}