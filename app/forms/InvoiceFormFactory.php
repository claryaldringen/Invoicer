<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 17.5.15
 * Time: 15:54
 */

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;

class InvoiceFormFactory extends Nette\Object{

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}
}