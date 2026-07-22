<?php
require_once __DIR__ . '/../models/operator.php';

function signup_index(PDO $pdo): string
{
    $error = null;

    if (is_post()) {
        $username         = $_POST['username'] ?? '';
        $email            = $_POST['email'] ?? '';
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (operator_find_by_email($pdo, $email)) {
            $error = 'An account with that email already exists.';
        } else {
            $success = operator_create($pdo, $username, $email, $password);
            if ($success) {
                redirect('/login');
            }
            $error = 'Something went wrong. Please try again.';
        }
    }

    return render('app/views/signup.php', [
        'error' => $error,
    ]);
}