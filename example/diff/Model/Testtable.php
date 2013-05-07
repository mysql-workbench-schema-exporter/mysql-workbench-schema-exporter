<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test\Testtable
 *
 * @ORM\Entity()
 * @ORM\Table(name="testtable")
 */
class Testtable
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="decimal", precision=15, scale=2, nullable=true)
	 */
	protected $decCol;

	public function __construct()
	{
	}

	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return \Test\Testtable
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Get the value of id.
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set the value of decCol.
	 *
	 * @param float $decCol
	 * @return \Test\Testtable
	 */
	public function setDecCol($decCol)
	{
		$this->decCol = $decCol;

		return $this;
	}

	/**
	 * Get the value of decCol.
	 *
	 * @return float
	 */
	public function getDecCol()
	{
		return $this->decCol;
	}

	public function __sleep()
	{
		return array('id', 'decCol');
	}
}