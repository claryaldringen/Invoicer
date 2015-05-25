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

}
