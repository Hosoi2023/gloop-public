<?php
require_once __DIR__ . '/../models/operator.php';

function login_index(PDO $pdo, ?string $id = null): string
{
    $error = null;

    if (is_post()) {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $operator = operator_find_by_email($pdo, $email);

        if ($operator && password_verify($password, $operator['password'])) {
            if (!$operator['is_active']) {
                $error = 'Your account has been banned.';
            } else {
                set_logged($operator);
                redirect('/');
            }
        } else {
            $error = 'Incorrect email or password.';
        }
    }

    return render('app/views/login.php', [
        'error' => $error,
    ]);
}