<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test\UsersBureaus
 *
 * @ORM\Entity()
 * @ORM\Table(name="users_bureaus", indexes={@ORM\Index(name="fk_users_bureaus_bureaus1", columns={"bureaus_id"})})
 */
class UsersBureaus
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $users_id;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $bureaus_id;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="usersBureauss")
	 * @ORM\JoinColumn(name="users_id", referencedColumnName="id", nullable=false)
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Bureau", inversedBy="usersBureauss")
	 * @ORM\JoinColumn(name="bureaus_id", referencedColumnName="id", nullable=false)
	 */
	protected $bureau;

	public function __construct()
	{
	}

	/**
	 * Set the value of users_id.
	 *
	 * @param integer $users_id
	 * @return \Test\UsersBureaus
	 */
	public function setUsersId($users_id)
	{
		$this->users_id = $users_id;

		return $this;
	}

	/**
	 * Get the value of users_id.
	 *
	 * @return integer
	 */
	public function getUsersId()
	{
		return $this->users_id;
	}

	/**
	 * Set the value of bureaus_id.
	 *
	 * @param integer $bureaus_id
	 * @return \Test\UsersBureaus
	 */
	public function setBureausId($bureaus_id)
	{
		$this->bureaus_id = $bureaus_id;

		return $this;
	}

	/**
	 * Get the value of bureaus_id.
	 *
	 * @return integer
	 */
	public function getBureausId()
	{
		return $this->bureaus_id;
	}

	/**
	 * Set User entity (many to one).
	 *
	 * @param \Test\User $user
	 * @return \Test\UsersBureaus
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get User entity (many to one).
	 *
	 * @return \Test\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Set Bureau entity (many to one).
	 *
	 * @param \Test\Bureau $bureau
	 * @return \Test\UsersBureaus
	 */
	public function setBureau(Bureau $bureau = null)
	{
		$this->bureau = $bureau;

		return $this;
	}

	/**
	 * Get Bureau entity (many to one).
	 *
	 * @return \Test\Bureau
	 */
	public function getBureau()
	{
		return $this->bureau;
	}

	public function __sleep()
	{
		return array('users_id', 'bureaus_id');
	}
}