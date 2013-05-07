<?php

namespace Test;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test\Bureau
 *
 * This is a long comment for the bureaus table. It will appear in the doctrine
 * class and long lines will be wrapped.
 * 
 * Multiple lines can be entered as well.
 *
 * @ORM\Entity()
 * @ORM\Table(name="bureaus", indexes={@ORM\Index(name="fk_bureaus_foo1", columns={"foo_id"})}, uniqueConstraints={@ORM\UniqueConstraint(name="testIndex", columns={"room"})})
 */
class Bureau
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * Comment for the room field. This comment will be used for the field in the
	 * doctrine class and long lines will wrap with the correct indentation.
	 * 
	 * New Lines are supported as well.
	 *
	 * @ORM\Column(type="string", length=45, nullable=true)
	 */
	protected $room;

	/**
	 * @ORM\OneToOne(targetEntity="Foo", inversedBy="bureau")
	 * @ORM\JoinColumn(name="foo_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	protected $foo;

	/**
	 * @ORM\ManyToMany(targetEntity="User", mappedBy="bureaus")
	 */
	protected $users;

	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return \Test\Bureau
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
	 * Set the value of room.
	 *
	 * @param string $room
	 * @return \Test\Bureau
	 */
	public function setRoom($room)
	{
		$this->room = $room;

		return $this;
	}

	/**
	 * Get the value of room.
	 *
	 * @return string
	 */
	public function getRoom()
	{
		return $this->room;
	}

	/**
	 * Set Foo entity (one to one).
	 *
	 * @param \Test\Foo $foo
	 * @return \Test\Bureau
	 */
	public function setFoo(Foo $foo = null)
	{
		$foo->setBureau($this);
		$this->foo = $foo;

		return $this;
	}

	/**
	 * Get Foo entity (one to one).
	 *
	 * @return \Test\Foo
	 */
	public function getFoo()
	{
		return $this->foo;
	}

	/**
	 * Add User entity to collection.
	 *
	 * @param \Test\User $user
	 * @return \Test\Bureau
	 */
	public function addUser(User $user)
	{
		$this->users[] = $user;

		return $this;
	}

	/**
	 * Get User entity collection.
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUsers()
	{
		return $this->users;
	}

	public function __sleep()
	{
		return array('id', 'room', 'foo_id');
	}
}