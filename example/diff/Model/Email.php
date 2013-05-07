<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;

/**
 * Test\Email
 *
 * @ORM\Entity()
 * @ORM\Table(name="emails", indexes={@ORM\Index(name="fk_Emails_Users", columns={"users_id"})})
 */
class Email
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $email;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 */
	protected $users_id;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="emails")
	 * @ORM\JoinColumn(name="users_id", referencedColumnName="id", nullable=false)
	 */
	protected $user;

	public function __construct()
	{
	}

	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return \Test\Email
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
	 * Set the value of email.
	 *
	 * @param string $email
	 * @return \Test\Email
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Get the value of email.
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set the value of users_id.
	 *
	 * @param integer $users_id
	 * @return \Test\Email
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
	 * Set User entity (many to one).
	 *
	 * @param \Test\User $user
	 * @return \Test\Email
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

	public function __sleep()
	{
		return array('id', 'email', 'users_id');
	}
}