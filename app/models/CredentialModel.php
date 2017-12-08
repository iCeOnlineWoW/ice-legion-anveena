<?php

namespace App\Models;

/**
 * Model for managing credentials
 */
class CredentialModel extends BaseModel
{
    public $implicitTable = 'credentials';

    /**
     * Retrieves credential record by id
     * @param string $identifier
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getCredentialByIdentifier($identifier)
    {
        return $this->getTable()->where('identifier', $identifier)->fetch();
    }

    /**
     * Retrieves all credentials in system
     * @return \Nette\Database\Table\Selection
     */
    public function getAllCredentials()
    {
        return $this->getTable();
    }

    /**
     * Adds new credential to database
     * @param string $identifier
     * @param string $type
     * @param string $username
     * @param string $auth_ref
     * @return \Nette\Database\Table\ActiveRow
     */
    public function addCredential($identifier, $type, $username, $auth_ref)
    {
        return $this->getTable()->insert(array(
            'identifier' => $identifier,
            'type' => $type,
            'username' => $username,
            'auth_ref' => $auth_ref
        ));
    }

    /**
     * Edits credential in database
     * @param string $identifier
     * @param string $new_identifier
     * @param string $type
     * @param string $username
     * @param string $auth_ref
     */
    public function editCredential($identifier, $new_identifier, $type, $username, $auth_ref)
    {
        $this->getTable()->where('identifier', $identifier)->update(array(
            'identifier' => $new_identifier,
            'type' => $type,
            'username' => $username,
            'auth_ref' => $auth_ref
        ));
    }

    /**
     * Delete credential with specified identifier
     * @param string $identifier
     */
    public function deleteCredential($identifier)
    {
        $this->getTable()->where('identifier', $identifier)->delete();
    }
}
