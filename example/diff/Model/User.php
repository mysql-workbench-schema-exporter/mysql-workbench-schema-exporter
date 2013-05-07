<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test\User
 *
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User
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
	protected $name;

	/**
	 * @ORM\OneToMany(targetEntity="Email", mappedBy="user")
	 * @ORM\JoinColumn(name="users_id", referencedColumnName="id", nullable=false)
	 */
	protected $emails;

	/**
	 * @ORM\ManyToMany(targetEntity="Bureau", inversedBy="users")
	 * @ORM\JoinTable(name="users_bureaus",
	 *     joinColumns={@ORM\JoinColumn(name="users_id", referencedColumnName="id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="bureaus_id", referencedColumnName="id")}
	 * )
	 */
	protected $bureaus;

	public function __construct()
	{
		$this->emails = new ArrayCollection();
		$this->bureaus = new ArrayCollection();
	}

	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return \Test\User
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
	 * Set the value of name.
	 *
	 * @param string $name
	 * @return \Test\User
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Get the value of name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Add Email entity to collection (one to many).
	 *
	 * @param \Test\Email $email
	 * @return \Test\User
	 */
	public function addEmail(Email $email)
	{
		$this->emails[] = $email;

		return $this;
	}

	/**
	 * Get Email entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getEmails()
	{
		return $this->emails;
	}

	/**
	 * Add Bureau entity to collection.
	 *
	 * @param \Test\Bureau $bureau
	 * @return \Test\User
	 */
	public function addBureau(Bureau $bureau)
	{
		$bureau->addUser($this);
		$this->bureaus[] = $bureau;

		return $this;
	}

	/**
	 * Get Bureau entity collection.
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getBureaus()
	{
		return $this->bureaus;
	}

	public function __toString() {
		return $this->getName();
	}

	public function __sleep()
	{
		return array('id', 'name');
	}
}