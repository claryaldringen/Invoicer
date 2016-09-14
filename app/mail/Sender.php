<?php

namespace App\Mail;


use App\File\Namer;
use App\Model\CustomerModel;
use App\Model\InvoiceModel;
use Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Security\User;
use OndrejBrejla\Eciovni\Eciovni;

class Sender
{
	/** @var InvoiceModel */
	protected $invoiceModel;

	/** @var User */
	protected $user;

	/** @var CustomerModel */
	protected $customerModel;

	/** @var IMailer */
	protected $mailer;

	/** @var Namer */
	protected $namer;

	/** @var Eciovni */
	protected $invoice;

	public function __construct(User $user, InvoiceModel $invoiceModel, CustomerModel $customerModel, SendmailMailer $mailer, Namer $namer) {
		$this->invoiceModel = $invoiceModel;
		$this->user = $user;
		$this->customerModel = $customerModel;
		$this->mailer = $mailer;
		$this->namer = $namer;
	}

	public function setInvoice(Eciovni $invoice) {
		$this->invoice = $invoice;
		return $this;
	}

	public function sendMail($vsId, $customerId, $invoice = FALSE) {
		$mail = new Message();

		if($invoice) {
			$payment = $this->invoiceModel->getPaymentData($vsId);
			$subject = 'Faktura č. ' . $payment->id;
			$pdfFile = $this->namer->getPdfFile($payment->id);
			$this->invoice->exportToPdf(new \mPDF('utf-8'), $pdfFile, 'F');
			$mail->addAttachment($pdfFile);
			$params = array('vsId' => $vsId);
			$customerId = $payment->customer_id;
		} else {
			$subject = 'Výzva k platbě';
			$params = array(
				'items' => $this->invoiceModel->getItems($vsId),
				'account' => $this->user->identity->data['account'],
				'vsId' => $vsId
			);
		}

		$latte = new Engine();
		$mail->setFrom($this->user->identity->data['email'], $this->user->identity->data['name']);
		$customer = $this->customerModel->getCustomer($customerId);
		$mail->addTo($customer->email, $customer->name);
		$mail->addBcc('info@freetech.cz');
		$mail->setSubject($subject);
		$mail->setHtmlBody($latte->renderToString($this->namer->getLatteFileName($invoice), $params));

		$this->mailer->send($mail);

	}

}