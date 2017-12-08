<?php

namespace App\Models;

/**
 * Enumeration of system credential types
 */
class CredentialType extends BaseEnum
{
    const LOGIN = 'login';
    const KEY = 'key';
    const KEY_FILE = 'key-file';
}
