<?php

namespace App\Invoice;

use Nette\Database\Row;
use Nette\Security\User;
use OndrejBrejla\Eciovni\Eciovni;
use OndrejBrejla\Eciovni\ParticipantBuilder;
use OndrejBrejla\Eciovni\ItemImpl;
use OndrejBrejla\Eciovni\DataBuilder;
use OndrejBrejla\Eciovni\TaxImpl;

class EciovniFactory
{

	/**
	 * Creates Eciovni component.
	 *
	 * @param User $user
	 * @param Row $customer
	 * @param Row $payment
	 * @param array $items
	 * @return Eciovni
	 */
	public function create(User $user, Row $customer, Row $payment, array $items) {

		$supplier = (object)$user->identity->data;
		$supplierBuilder = new ParticipantBuilder($supplier->name, $supplier->street, $supplier->number, $supplier->city, $supplier->post_code);
		$dic = empty($supplier->dic) ? '' : $supplier->dic;
		$supplier = $supplierBuilder->setIn($supplier->ico)->setTin($dic)->setAccountNumber($supplier->account)->build();

		$this->customerBuilder = new ParticipantBuilder($customer->name, $customer->street, $customer->number, $customer->city, $customer->post_code);
		$customer = $this->customerBuilder->build();

		$invoiceItems = array();
		foreach($items as $item) {
			$invoiceItems[] = new ItemImpl($item->name, $item->count, $item->price, TaxImpl::fromPercent(0), FALSE);
		}

		$dataBuilder = new DataBuilder($payment->id, 'Faktura č.', $supplier, $customer, $payment->payment_date, $payment->issue_date, $invoiceItems);
		$dataBuilder->setVariableSymbol($payment->variable_symbol_id)->setDateOfVatRevenueRecognition($payment->issue_date);

		return new Eciovni($dataBuilder->build());
	}

}