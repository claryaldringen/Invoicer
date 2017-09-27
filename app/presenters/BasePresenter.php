<?php

namespace App\Presenters;

use Nette,
	App\Model,
	App\Model\CustomerModel,
	App\Model\InvoiceModel;

use OndrejBrejla\Eciovni\Eciovni;
use OndrejBrejla\Eciovni\ParticipantBuilder;
use OndrejBrejla\Eciovni\ItemImpl;
use OndrejBrejla\Eciovni\DataBuilder;
use OndrejBrejla\Eciovni\TaxImpl;
use Tracy\Debugger;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var InvoiceModel @inject */
	public $invoiceModel;

	/** @var CustomerModel @inject */
	public $customerModel;

	protected  $variableSymbol;

	protected function createComponentEciovni() {

		$supplier = (object)$this->user->identity->data;
		$supplierBuilder = new ParticipantBuilder($supplier->name, $supplier->street, $supplier->number, $supplier->city, $supplier->post_code);
		$dic = empty($supplier->dic) ? '' : $supplier->dic;
		$supplier = $supplierBuilder->setIn($supplier->ico)->setTin($dic)->setAccountNumber($supplier->account)->build();

		$payment = $this->invoiceModel->getInvoiceData($this->variableSymbol);
		$customer = $this->customerModel->getCustomer($payment['customer_id']);
		$customerBuilder = new ParticipantBuilder($customer['name'], $customer['street'], $customer['number'], $customer['city'], $customer['post_code']);
		$dic = empty($customer['dic']) ? '' : $customer['dic'];
		$ico = empty($customer['ico']) ? '' : $customer['ico'];
		$customer = $customerBuilder->setIn($ico)->setTin($dic)->build();

		$items = $this->invoiceModel->getItems($payment['variable_symbol_id']);
		$invoiceItems = array();
		foreach($items as $item) {
			$invoiceItems[] = new ItemImpl($item->name, $item->count, $item->price, TaxImpl::fromPercent(0), FALSE);
		}

		//Debugger::dump($payment);

		$dataBuilder = new DataBuilder($payment['id'], 'Faktura č.', $supplier, $customer, $payment['payment_date'], $payment['issue_date'], $invoiceItems);
		$dataBuilder->setVariableSymbol($payment['variable_symbol_id'])->setDateOfVatRevenueRecognition($payment['issue_date']);
		$data = $dataBuilder->build();

		return new Eciovni($data);
	}

}
