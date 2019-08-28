<?php

namespace App\Models;

use Nette,
    Nette\Security\Passwords;

/**
 * Users management.
 */
class Authenticator implements Nette\Security\IAuthenticator
{
    const
        TABLE_NAME = 'users',
        COLUMN_ID = 'id',
        COLUMN_NAME = 'username',
        COLUMN_PASSWORD_HASH = 'password',
        COLUMN_ROLE = 'admin';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;
        $row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $email)->fetch();

        if (!$row)
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH]))
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH]))
        {
            $row->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
        }

        $arr = $row->toArray();

        $arr['is_admin'] = $row[self::COLUMN_ROLE];

        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }
}

