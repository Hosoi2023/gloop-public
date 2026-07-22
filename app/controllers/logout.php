<?php

function logout_index(PDO $pdo): string
{
    session_destroy();
    redirect('/');
}