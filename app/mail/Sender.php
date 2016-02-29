<?php

namespace App\Mail;


use Latte\Engine;
use Nette\Mail\Message;

class Sender
{
	public function sendMail($vsId, $customerId, $invoice = FALSE) {
		$mail = new Message();

		if($invoice) {
			$file = $this->context->parameters['appDir'] . '/presenters/templates/Api/invoice_mail.latte';
			$payment = $this->invoiceModel->getPaymentData($vsId);
			$subject = 'Faktura č. ' . $payment->id;
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

		$latte = new Engine();
		$mail->setFrom($this->user->identity->data['email'], $this->user->identity->data['name']);
		$customer = $this->customerModel->getCustomer($customerId);
		$mail->addTo($customer->email, $customer->name);
		$mail->setSubject($subject);
		$mail->setHtmlBody($latte->renderToString($file, $params));

		$this->mailer->send($mail);

	}

}