<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 27.5.15
 * Time: 6:38
 */

namespace App\Model;

use OndrejBrejla\Eciovni\Eciovni;
use OndrejBrejla\Eciovni\ParticipantBuilder;
use OndrejBrejla\Eciovni\ItemImpl;
use OndrejBrejla\Eciovni\DataBuilder;
use OndrejBrejla\Eciovni\TaxImpl;

class InvoiceFactory {

	/**
	 * @param $supplier
	 * @param $customer
	 * @param $payment
	 * @param $items
	 * @return Eciovni
	 */
	protected function getEciovni($supplier, $customer, $payment, $items) {

		$supplierBuilder = new ParticipantBuilder($supplier->name, $supplier->street, $supplier->number, $supplier->city, $supplier->post_code);
		$supplier = $supplierBuilder->setIn($supplier->ico)->setTin($supplier->dic)->setAccountNumber($supplier->account)->build();
		$customerBuilder = new ParticipantBuilder($customer->name, $customer->street, $customer->number, $customer->city, $customer->post_code);
		$customer = $customerBuilder->build();

		$invoiceItems = array();
		foreach($items as $item) {
			$invoiceItems[] = new ItemImpl($item->name, $item->count, $item->price, TaxImpl::fromPercent(22));
		}

		$dataBuilder = new DataBuilder(date('YmdHis'), 'Invoice - invoice number', $supplier, $customer, $payment->payment_date, $payment->issue_date, $invoiceItems);
		$dataBuilder->setVariableSymbol($payment->variable_symbol_id)->setDateOfVatRevenueRecognition($payment->issue_date);
		$data = $dataBuilder->build();

		return new Eciovni($data);
	}

}