<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 24.5.15
 * Time: 15:53
 */

namespace App\Presenters;

use Nette,
	Nette\Mail,
	App\Model\CustomerModel,
	App\Model\InvoiceModel,
	App\Model\MailReader,
	Nette\Mail\SendmailMailer,
	Nette\Latte;


use OndrejBrejla\Eciovni\Eciovni;
use OndrejBrejla\Eciovni\ParticipantBuilder;
use OndrejBrejla\Eciovni\ItemImpl;
use OndrejBrejla\Eciovni\DataBuilder;
use OndrejBrejla\Eciovni\TaxImpl;



class ApiPresenter extends BasePresenter{

	/** @var CustomerModel @inject */
	public $customerModel;

	/** @var InvoiceModel @inject */
	public $invoiceModel;

	/** @var SendmailMailer @inject */
	public $mailer;

	/** @var MailReader @inject */
	public $mailReader;

	private $variableSymbol;

	protected function startup() {
		parent::startup();
	}

	protected function createComponentEciovni() {

		$supplier = (object)$this->user->identity->data;
		$supplierBuilder = new ParticipantBuilder($supplier->name, $supplier->street, $supplier->number, $supplier->city, $supplier->post_code);
		$dic = empty($supplier->dic) ? '' : $supplier->dic;
		$supplier = $supplierBuilder->setIn($supplier->ico)->setTin($dic)->setAccountNumber($supplier->account)->build();

		$payment = $this->invoiceModel->getPaymentData($this->variableSymbol);
		$customer = $this->customerModel->getCustomer($payment->customer_id);
		$customerBuilder = new ParticipantBuilder($customer->name, $customer->street, $customer->number, $customer->city, $customer->post_code);
		$customer = $customerBuilder->build();

		$items = $this->invoiceModel->getItems($payment->variable_symbol_id);
		$invoiceItems = array();
		foreach($items as $item) {
			$invoiceItems[] = new ItemImpl($item->name, $item->count, $item->price, TaxImpl::fromPercent(22));
		}

		$dataBuilder = new DataBuilder($payment->id, 'Faktura č.', $supplier, $customer, $payment->payment_date, $payment->issue_date, $invoiceItems);
		$dataBuilder->setVariableSymbol($payment->variable_symbol_id)->setDateOfVatRevenueRecognition($payment->issue_date);
		$data = $dataBuilder->build();

		return new Eciovni($data);
	}

	public function renderCustomers() {
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->customerModel->getCustomers();
		} elseif($request->isMethod('POST')) {
			$id = $this->customerModel->insertCustomer($request->getPost());
			$response = array('id' => $id);
		} elseif($request->isMethod('PUT')) {
			parse_str(file_get_contents("php://input"), $data);
			$id = $this->customerModel->updateCustomer($data);
			$response = array('id' => $id);
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	public function renderInvoices() {
		$response = array();
		$request = $this->getHttpRequest()->getPost();
		$vsId = $this->invoiceModel->insertInvoice($request);
		if($request['send']) {
			$mail = new Mail\Message();

			if($request['type'] == 2) {
				$file = $this->context->parameters['appDir'] . '/presenters/templates/Api/invoice_mail.latte';
				$payment = $this->invoiceModel->getPaymentData($vsId);
				$subject = 'Faktura č. ' . $payment->id;
				$this->variableSymbol = $vsId;
				$pdfFile = $this->context->parameters['wwwDir'] . '/invoices/' . $payment->id . '.pdf';
				$this['eciovni']->exportToPdf(new \mPDF('utf-8'), $pdfFile, 'F');
				$mail->addAttachment($pdfFile);
				$params = array('vsId' => $vsId);
			} else {
				$file = $this->context->parameters['appDir'] . '/presenters/templates/Api/payment_call.latte';
				$subject = 'Výzva k platbě';
				$params = array(
					'items' => $this->invoiceModel->getItems($vsId),
					'account' => $this->user->identity->data['account'],
					'vsId' => $vsId
				);
			}
			$latte = new Latte\Engine();
			$mail->setFrom($this->user->identity->data['email'], $this->user->identity->data['name']);
			$customer = $this->customerModel->getCustomer($request['customerId']);
			$mail->addTo($customer->email, $customer->name);
			$mail->setSubject($subject);
			$mail->setHtmlBody($latte->renderToString($file, $params));

			$this->mailer->send($mail);
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	public function renderPayment() {
		$response = array();
		$mails = $this->mailReader->read();
		foreach($mails as $mail) {
			$payment = $this->mailReader->parse($mail);
			if(!empty($payment)) {
				if($this->invoiceModel->checkPayment($payment['vs'], $payment['price'])) {

				}
			}
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	protected function sendMail() {
		$mail = new Mail\Message();
		$latte = new Latte\Engine();
		$mail->setFrom($this->user->identity->data['email'], $this->user->identity->data['name']);
		$customer = $this->customerModel->getCustomer($request['customerId']);
		$mail->addTo($customer->email, $customer->name);
		$mail->setSubject($subject);
		$mail->setHtmlBody($latte->renderToString($file, $params));

		$this->mailer->send($mail);

	}
}