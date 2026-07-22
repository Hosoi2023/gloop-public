<?php

function is_logged(): bool
{
    return isset($_SESSION['user_id']);
}

function is_admin(): bool
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function set_logged($operator){
    $_SESSION['user_id'] = $operator['id'];
    $_SESSION['user_email'] = $operator['email'];
    $_SESSION['user_username'] = $operator['username'];
    $_SESSION['user_role'] = $operator['role'];
}

function operator_find($pdo, $id)
{
    $sql = 'SELECT * FROM operator WHERE id = :id';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id' => $id,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function operator_find_by_email(PDO $pdo, string $email): array|false
{
    $sql = '
        SELECT *
        FROM operator
        WHERE email = :email
        LIMIT 1
    ';

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'email' => $email,
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function operator_all($pdo)
{
    $sql = 'SELECT id, email FROM operator';

    $stmt = $pdo->query($sql);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function operator_update_password($pdo, $id, $hashed_password)
{
    $sql = '
        UPDATE operator
        SET password = :password
        WHERE id = :id
    ';

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'id' => $id,
        'password' => $hashed_password,
    ]);
}

function operator_create(PDO $pdo, string $username, string $email, string $password): bool
{
    $sql = '
        INSERT INTO operator (username, email, password)
        VALUES (:username, :email, :password)
    ';
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'username' => $username,
        'email'    => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);
}

function operator_all_with_roles(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, username, email, role, is_active, is_muted, created_at FROM operator ORDER BY created_at DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function operator_set_role(PDO $pdo, int $id, string $role): void
{
    $stmt = $pdo->prepare('UPDATE operator SET role = ? WHERE id = ?');
    $stmt->execute([$role, $id]);
}

function operator_set_active(PDO $pdo, int $id, int $is_active): void
{
    $stmt = $pdo->prepare('UPDATE operator SET is_active = ? WHERE id = ?');
    $stmt->execute([$is_active, $id]);
}

function operator_set_muted(PDO $pdo, int $id, int $is_muted): void
{
    $stmt = $pdo->prepare('UPDATE operator SET is_muted = ? WHERE id = ?');
    $stmt->execute([$is_muted, $id]);
}