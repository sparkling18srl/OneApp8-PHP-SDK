<?php

namespace OneApp8\Model;

/**
 * Class Payment model
 * @author globrutto
 */
class Payment
{
	private $order = null;


	public function setOrder(Order $order) {
		$this->order = $order;
	}

	public function getOrder()
	{
		return $this->order;
	}
}
