<?php
namespace Lithos\Person;

class PersonModel
{
    private $id;
    private $name;
    private $emailAddress;
    private $role;
    private $emailAddressVerified;
    private $organizationId;
    private $created;
    private $updated;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setEmailAddressVerified($emailAddressVerified)
    {
        $this->emailAddressVerified = $emailAddressVerified;
    }

    public function getEmailAddressVerified()
    {
        return $this->emailAddressVerified;
    }

    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    public function getUpdated()
    {
        return $this->updated;
    }
}
