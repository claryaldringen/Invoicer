<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		if(!$this->user->isLoggedIn()) $this->redirect('Sign:in');
	}

	public function renderInvoice($id) {
		if($this->user->isLoggedIn()) {
			$this->variableSymbol = $id;
			$this['eciovni']->exportToPdf(new \mPDF('utf-8'));
		}
	}
}
