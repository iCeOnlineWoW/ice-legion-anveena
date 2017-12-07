<?php

namespace App\Models;

use Nette\Security\Passwords;

/**
 * Model for managing users
 */
class UserModel extends BaseModel
{
    public $implicitTable = 'users';

    /**
     * Retrieves user record by email
     * @param string $email
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getUserByEmail($email)
    {
        return $this->getTable()->where('email', $email)->fetch();
    }

    /**
     * Retrieves user record by id
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getUserById($id)
    {
        return $this->getTable()->where('id', $id)->fetch();
    }

    /**
     * Retrieves user record by username
     * @param string $username
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getUserByUsername($username)
    {
        return $this->getTable()->where('username', $username)->fetch();
    }

    /**
     * Adds new user, returns the instance
     * @param string $username
     * @param string $password
     * @param string $email
     * @return mixed | null
     * @return \Nette\Database\Table\ActiveRow
     */
    public function addUser($username, $password, $email)
    {
        return $this->getTable()->insert(array(
            'username' => $username,
            'password' => Passwords::hash($password),
            'email' => $email
        ));
    }

    /**
     * Sets admin field to given value
     * @param int $id
     * @param bool $admin
     */
    public function setAdmin($id, $admin = true)
    {
        $this->getTable()->where('id', $id)->update(array(
            'admin' => $admin ? 1 : 0
        ));
    }

    /**
     * Sets blocked field to given value
     * @param int $id
     * @param bool $blocked
     */
    public function setBlocked($id, $blocked = true)
    {
        $this->getTable()->where('id', $id)->update(array(
            'blocked' => $blocked ? 1 : 0
        ));
    }
}

