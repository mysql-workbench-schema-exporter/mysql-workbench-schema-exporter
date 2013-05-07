<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test\Foo
 *
 * @ORM\Entity()
 * @ORM\Table(name="foo")
 */
class Foo
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="Bureau", mappedBy="foo")
	 * @ORM\JoinColumn(name="foo_id", referencedColumnName="id", nullable=false)
	 */
	protected $bureau;

	public function __construct()
	{
	}

	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return \Test\Foo
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
	 * Set Bureau entity (one to one).
	 *
	 * @param \Test\Bureau $bureau
	 * @return \Test\Foo
	 */
	public function setBureau(Bureau $bureau)
	{
		$this->bureau = $bureau;

		return $this;
	}

	/**
	 * Get Bureau entity (one to one).
	 *
	 * @return \Test\Bureau
	 */
	public function getBureau()
	{
		return $this->bureau;
	}

	public function __sleep()
	{
		return array('id');
	}
}