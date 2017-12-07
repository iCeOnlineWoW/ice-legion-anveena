<?php

// force argument count
if ($argc != 4)
{
    die("Usage:\n    php AddAdmin.php <username> <password> <email>");
}

require __DIR__.'/../bootstrap.php';

$container = require __DIR__ . '/../bootstrap.php';
$users = $container->getByType('App\Model\UserModel');

$username = $argv[1];
$password = $argv[2];
$email = $argv[3];

if (!\App\Models\Validators::validateEmail($email))
    die("Invalid email: ".$email."\n");

if (!\App\Models\Validators::validatePasswordStrength($password))
    die("Weak password, use 6 characters or more\n");

if ($users->getUserByEmail($email))
    die("This email is already in the database!\n");

if ($users->getUserByUsername($username))
    die("This username is already in the database!\n");

$user = $users->addUser($username, $password, $email);
if (!$user)
    die("Could not create user.\n");

$users->setAdmin($user['id'], true);

echo "Admin '$username' successfully registered.\n";
