<?php

function get_admin_account(PDO $connection, int $adminId): array
{
    $statement = $connection->prepare(
        'SELECT id, email, password_hash FROM admins
         WHERE id = :id AND is_active = 1 LIMIT 1'
    );
    $statement->execute(['id' => $adminId]);
    $account = $statement->fetch();

    if (!$account) {
        throw new RuntimeException('The administrator account was not found.');
    }

    return $account;
}

function update_admin_account(PDO $connection, int $adminId, array $input): string
{
    $account = get_admin_account($connection, $adminId);
    $email = $input['email'] ?? null;
    $currentPassword = $input['current_password'] ?? null;
    $newPassword = $input['new_password'] ?? null;
    $confirmPassword = $input['confirm_password'] ?? null;

    if (!is_string($email) || !is_string($currentPassword)
        || !is_string($newPassword) || !is_string($confirmPassword)
    ) {
        throw new InvalidArgumentException('Check your email and password fields.');
    }

    $email = strtolower(trim($email));
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Enter a valid login email.');
    }

    if (!password_verify($currentPassword, $account['password_hash'])) {
        throw new InvalidArgumentException('Your current password is incorrect.');
    }

    if ($newPassword === '' && $confirmPassword !== '') {
        throw new InvalidArgumentException('Enter a new password or clear both new password fields.');
    }
    if ($newPassword !== '') {
        $passwordLength = preg_match_all('/./us', $newPassword);
        if ($passwordLength === false || $passwordLength < 12 || strlen($newPassword) > 72) {
            throw new InvalidArgumentException('Use a new password with at least 12 characters.');
        }
        if ($newPassword !== $confirmPassword) {
            throw new InvalidArgumentException('The new passwords do not match.');
        }
    }

    $existingEmail = $connection->prepare(
        'SELECT id FROM admins WHERE email = :email AND id <> :id LIMIT 1'
    );
    $existingEmail->execute(['email' => $email, 'id' => $adminId]);
    if ($existingEmail->fetchColumn() !== false) {
        throw new InvalidArgumentException('That login email is already in use.');
    }

    if ($newPassword === '') {
        $statement = $connection->prepare('UPDATE admins SET email = :email WHERE id = :id');
        $statement->execute(['email' => $email, 'id' => $adminId]);
    } else {
        $statement = $connection->prepare(
            'UPDATE admins SET email = :email, password_hash = :password_hash WHERE id = :id'
        );
        $statement->execute([
            'email' => $email,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $adminId,
        ]);
    }

    return $email;
}
