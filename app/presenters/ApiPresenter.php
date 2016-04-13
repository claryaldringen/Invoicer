<?php
/**
 * Presenter provides REST API.
 */

namespace App\Presenters;

use Nette,
	Nette\Mail,
	App\Model\CustomerModel,
	App\Model\InvoiceModel,
	App\Mail\Reader,
	Nette\Mail\SendmailMailer,
	Nette\Latte,
	App\Mail\Sender,
	App\Invoice\EciovniFactory;


class ApiPresenter extends BasePresenter{

	/** @var CustomerModel @inject */
	public $customerModel;

	/** @var InvoiceModel @inject */
	public $invoiceModel;

	/** @var SendmailMailer @inject */
	public $mailer;

	/** @var Reader @inject */
	public $mailReader;

	/** @var EciovniFactory @inject */
	public $eciovniFactory;

	/** @var Sender @inject */
	public $mailSender;

	protected $variableSymbol;

	/**
	 * Returns new instance of Eciovni.
	 *
	 * @return \OndrejBrejla\Eciovni\Eciovni
	 */
	protected function createComponentEciovni() {
		$payment = $this->invoiceModel->getPaymentData($this->variableSymbol);
		$items = $this->invoiceModel->getItems($payment->variable_symbol_id);
		$customer = $this->customerModel->getCustomer($payment->customer_id);
		return $this->eciovniFactory->create($this->getUser(), $customer, $payment, $items);
	}

	/**
	 * Handle of /api/customers call.
	 */
	public function renderCustomers() {
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->customerModel->getCustomers();
		} elseif($request->isMethod('POST')) {
			$id = $this->customerModel->insertCustomer($request->getPost());
			$response = array('id' => $id);
		} elseif($request->isMethod('PUT')) {
			$id = $this->customerModel->updateCustomer($request->getPut());
			$response = array('id' => $id);
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	/**
	 * Handle of /api/invoices call.
	 *
	 * @throws \App\Model\Exception
	 */
	public function renderInvoices($id) {
		$response = array();
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->invoiceModel->getInvoices();
		} elseif($request->isMethod('POST')) {
			$data = $request->getPost();
			$vsId = $this->invoiceModel->insertInvoice($data);
			if ($data['send'] == 'true') {
				if($data['type'] == 2) {
					$this->variableSymbol = $vsId;
					$this->mailSender->setInvoice($this['eciovni']);
				}
				$this->mailSender->sendMail($vsId, $data['customerId'], $data['type'] == 2);
			}
			$response = $this->invoiceModel->getInvoices();
		} elseif($request->isMethod('DELETE')) {
			$response = $this->invoiceModel->delete($id)->getInvoices();
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	/**
	 * Handle of /api/payment call.
	 *
	 * @throws \App\Model\Exception
	 */
	public function renderPayment() {
		$response = date('Y-m-d H:i:s') . "\n";
		$mails = $this->mailReader->read();
		foreach($mails as $mail) {
			$payment = $this->mailReader->parse($mail);
			if(!empty($payment)) {
				$status = $this->invoiceModel->checkPayment($payment['vs'], $payment['price']);
				if($status === TRUE) {
					$this->invoiceModel->invoiceFromVs($payment['vs'], TRUE);
					$this->mailSender->sendMail($payment['vs'], NULL, TRUE);
					$response .= "Variable symbol {$payment['vs']} with amount {$payment['price']} is OK.\n";
				} else {
					$response .= $status . "\n";
				}
			} else {
				$response .= "Not payment in mail.\n";
			}
		}
		$this->sendResponse(new Nette\Application\Responses\TextResponse($response));
	}

}