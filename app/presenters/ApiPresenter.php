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
	App\Model\MailReader,
	Nette\Mail\SendmailMailer,
	Nette\Latte;


class ApiPresenter extends BasePresenter{

	/** @var SendmailMailer @inject */
	public $mailer;

	/** @var MailReader @inject */
	public $mailReader;

	protected function startup() {
		parent::startup();
		if(!$this->user->isLoggedIn()) die('User is not logged in.');
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

	public function renderInvoices($id) {
		$response = array();
		$request = $this->getHttpRequest();
		if($request->isMethod('GET')) {
			$response = $this->invoiceModel->getInvoices();
		} elseif($request->isMethod('POST')) {
			$data = $request->getPost();
			$vsId = $this->invoiceModel->insertInvoice($data);
			if ($data['send'] == 'true') {
				$this->sendMail($vsId, $data['customerId'], $data['type'] == 2);
			}
			$response = $this->invoiceModel->getInvoices();
		} elseif($request->isMethod('DELETE')) {
			$response = $this->invoiceModel->delete($id)->getInvoices();
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}

	public function renderPayment() {
		$response = date('Y-m-d H:i:s') . "\n";
		$mails = $this->mailReader->read();
		foreach($mails as $mail) {
			$payment = $this->mailReader->parse($mail);
			if(!empty($payment)) {
				$status = $this->invoiceModel->checkPayment($payment['vs'], $payment['price']);
				if($status === true) {
					$this->invoiceModel->invoiceFromVs($payment['vs'], true);
					$this->sendMail($payment['vs'], null, true);
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

	protected function sendMail($vsId, $customerId, $invoice = false) {
		$mail = new Mail\Message();

		if($invoice) {
			$file = $this->context->parameters['appDir'] . '/presenters/templates/Api/invoice_mail.latte';
			$payment = $this->invoiceModel->getPaymentData($vsId);
			$subject = 'Faktura č. ' . $payment->id;
			$this->variableSymbol = $vsId;
			$pdfFile = $this->context->parameters['wwwDir'] . '/invoices/' . $payment->id . '.pdf';
			$this['eciovni']->exportToPdf(new \mPDF('utf-8'), $pdfFile, 'F');
			$mail->addAttachment($pdfFile);
			$params = array('vsId' => $vsId);
			$customerId = $payment->customer_id;
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
		$customer = $this->customerModel->getCustomer($customerId);
		$mail->addTo($customer->email, $customer->name);
		$mail->setSubject($subject);
		$mail->setHtmlBody($latte->renderToString($file, $params));

		$this->mailer->send($mail);

	}
}