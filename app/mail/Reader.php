<?php

namespace App\Mail;

use Nette;

class Reader {

	/** @var Nette\Database\Context  */
	private $db;

	private $conn;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}

	/**
	 * Returns array of mails.
	 *
	 * @return array
	 */
	public function read() {
		$this->conn = imap_open('{freetech.cz/notls}', 'invoice@freetech.cz', '');
		$mails = array();
		$msgCnt = imap_num_msg($this->conn);
		for($i = 1; $i <= $msgCnt; $i++) {
			$mails[] = array(
				'index' => $i,
				'head' => imap_headerinfo($this->conn, $i),
				'body' => imap_body($this->conn, $i)
			);
		}

		imap_errors();
		imap_alerts();
		imap_close($this->conn);
		return $mails;
	}

	/**
	 * Parses mail and search for payment. Return variable symbol and price.
	 *
	 * @param $mail
	 * @return array
	 */
	public function parse($mail) {
		if(strpos($mail['head']->Subject, 'Fio banka - prijem na konte') !== false){
			$lines = explode("\r\n", $mail['body']);
			$price = 0;
			foreach($lines as $line) {
				$matches = array();
				preg_match("/VS: ([0-9\.\,]+)/", $line, $matches);
				if(isset($matches[1])) {
					$vs = $matches[1];
					continue;
				}
				preg_match("/=C8=E1stka: ([0-9\.\,\s]+)/", $line, $matches);
				if(isset($matches[1])) {
					$price = floatval(str_replace(array(',', ' '), array('.',''), $matches[1]));
					continue;
				}
			}
		}
		if(isset($vs)) return array('vs' => $vs, 'price' => $price);
	}
}
