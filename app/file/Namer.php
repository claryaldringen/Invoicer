<?php

namespace App\File;


class Namer {

	/** @var string */
	private $appDir;

	/** @var string */
	private $wwwDir;

	/**
	 * Namer constructor.
	 *
	 * @param string $appDir
	 * @param string $wwwDir
	 */
	public function __construct($appDir, $wwwDir) {
		$this->appDir = $appDir;
		$this->wwwDir = $wwwDir;
	}

	/**
	 * @param bool $invoice
	 * @return string
	 */
	public function getLatteFileName($invoice = FALSE) {
		if($invoice) {
			return $this->appDir . '/presenters/templates/Api/invoice_mail.latte';
		} else {
			return $this->appDir . '/presenters/templates/Api/payment_call.latte';
		}
	}

	/**
	 * @param int $invoiceId
	 * @return string
	 */
	public function getPdfFile($invoiceId) {
		return $this->wwwDir . '/invoices/' . $invoiceId . '.pdf';
	}
}