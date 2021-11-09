<?php

namespace FapiMember;

use WP_Term;

final class FapiTermEnvelope
{

	/** @var WP_Term */
	private $term;

	/** @var int */
	private $order;

	/**
	 * FapiTermEnvelope constructor.
	 *
	 * @param WP_Term $term
	 * @param int $order
	 */
	public function __construct($term, $order)
	{
		$this->term = $term;
		$this->order = $order;
	}

	/**
	 * @return WP_Term
	 */
	public function getTerm()
	{
		return $this->term;
	}

	/**
	 * @return int
	 */
	public function getOrder()
	{
		return $this->order;
	}

}
