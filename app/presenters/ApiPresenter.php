<?php
/**
 * Presenter provides REST API.
 */

namespace App\Presenters;

use Nette,
	App\Model\CustomerModel,
	App\Model\SupplierModel,
	App\Model\InvoiceModel,
	App\Mail\Reader,
	Nette\Mail\SendmailMailer,
	App\Mail\Sender,
	App\Invoice\EciovniFactory;
use Tracy\Debugger;


class ApiPresenter extends BasePresenter{

	/** @var CustomerModel @inject */
	public $customerModel;

	/** @var SupplierModel @inject */
	public $supplierModel;

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
		$payment = $this->invoiceModel->getInvoiceData($this->variableSymbol);
		$items = $this->invoiceModel->getItems($payment->variable_symbol_id);
		$customer = $this->customerModel->getCustomer($payment->customer_id);
		$supplier = $this->supplierModel->getUser($payment->user_id);
		return $this->eciovniFactory->create($supplier, $customer, $payment, $items);
	}

	/**
	 * Handle of /api/customers call.
	 */
	public function renderCustomers() {
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->customerModel->getCustomers();
		} elseif($request->isMethod('POST')) {
			$id = $this->customerModel->insertCustomer(json_decode($request->getRawBody(), true));
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
		$response = [];
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
				$this->mailSender->sendMail($vsId, $data['type'] == 2);
			}
			$response['invoices'] = $this->invoiceModel->getInvoices();
			$response['preinvoices'] = $this->invoiceModel->getPreInvoices();
		} elseif($request->isMethod('DELETE')) {
			$response['invoices'] = $this->invoiceModel->delete($id)->getInvoices();
			$response['preinvoices'] = $this->invoiceModel->getPreInvoices();
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	public function renderSendMail($id) {
        $this->mailSender->sendMail($id);
        $this->sendResponse(new Nette\Application\Responses\JsonResponse([]));
    }

	public function renderItems() {
        $response = [];
		$request = $this->getHttpRequest();
		if($request->isMethod('POST')) {
			$data = $request->getPost();
            $this->invoiceModel->setItems($data);
            $response['invoices'] = $this->invoiceModel->getInvoices();
            $response['preinvoices'] = $this->invoiceModel->getPreInvoices();
		}
        $this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	/**
	 * Handle of /api/invoices call.
	 *
	 * @throws \App\Model\Exception
	 */
	public function renderPreInvoices() {
		$response = [];
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->invoiceModel->getPreInvoices();
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	public function renderCyclic($id) {
		$response = [];
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->invoiceModel->getCyclicPreInvoices();
		} elseif($request->isMethod('DELETE')) {
			$response = $this->invoiceModel->deleteCyclic($id)->getCyclicPreInvoices();
		} elseif($request->isMethod('POST')) {
			$date = json_decode($request->getRawBody(), true);
			$response = $this->invoiceModel->setCyclic($id, $date['next_check'])->getCyclicPreInvoices();
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
					$this->variableSymbol = $payment['vs'];
					$this->mailSender->setInvoice($this['eciovni']);
					$this->mailSender->sendMail($payment['vs'], TRUE);
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

	public function renderCyclicPayments() {
		$response = ['datetime' => date('Y-m-d H:i:s')];
		$response['payments'] = $this->invoiceModel->getCyclicPayments();
		foreach($response['payments'] as $payment) {
			$this->mailSender->sendMail($payment['vs']);
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

}