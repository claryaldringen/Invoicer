<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 3.1.17
 * Time: 22:12
 */

namespace App\Model;

use Nette;

class SupplierModel
{
	/** @var Nette\Database\Context  */
	private $db;

	public function __construct(Nette\Database\Context $database)
	{
		$this->db = $database;
	}

	public function getUser($id) {
		return $this->db->query("SELECT name,street,number,city,post_code,ico,dic,account FROM user WHERE id=?", $id)->fetch();
	}

}