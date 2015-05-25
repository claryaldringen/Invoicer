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
	Nette\Mail\SendmailMailer;



class ApiPresenter extends BasePresenter{

	/** @var CustomerModel @inject */
	public $customerModel;

	/** @var InvoiceModel @inject */
	public $invoiceModel;

	/** @var SendmailMailer @inject */
	public $mailer;

	protected function startup() {
		parent::startup();
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
		$this->invoiceModel->insertInvoice($request);
		if($request['send']) {
			if($request['type'] == 2) {
				$file = 'invoice_mail.latte';
				$subject = 'Faktura č.';
			} else {
				$file = 'payment_call.latte';
				$subject = 'Výzva k platbě';
			}
			$template = $this->createTemplate()->setFile($this->context->parameters['appDir'] . '/presenters/templates/Api/' . $file);
			$mail = new Mail\Message();
			$mail->setFrom($this->user->identity->data['email'], $this->user->identity->data['name']);
			$customer = $this->customerModel->getCustomer($request['customerId']);
			$mail->addTo($customer->email, $customer->name);
			$mail->setSubject($subject);
			$mail->setHtmlBody($template);

			$this->mailer->send($mail);
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($response));
	}
}