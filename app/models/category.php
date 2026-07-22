<?php

function category_all(PDO $pdo, string $table): array
{
    $allowed = ['response', 'audience', 'warning'];
    if (!in_array($table, $allowed)) {
        throw new InvalidArgumentException('Invalid category table');
    }
    $stmt = $pdo->query("SELECT id, name FROM {$table} ORDER BY id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function category_find(PDO $pdo, string $table, int $id): array|false
{
    $allowed = ['response', 'audience', 'warning'];
    if (!in_array($table, $allowed)) {
        throw new InvalidArgumentException('Invalid category table');
    }
    $stmt = $pdo->prepare("SELECT id, name FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function category_create(PDO $pdo, string $table, string $name): void
{
    $allowed = ['response', 'audience', 'warning'];
    if (!in_array($table, $allowed)) {
        throw new InvalidArgumentException('Invalid category table');
    }
    $stmt = $pdo->prepare("INSERT INTO {$table} (name) VALUES (?)");
    $stmt->execute([$name]);
}

function category_update(PDO $pdo, string $table, int $id, string $name): void
{
    $allowed = ['response', 'audience', 'warning'];
    if (!in_array($table, $allowed)) {
        throw new InvalidArgumentException('Invalid category table');
    }
    $stmt = $pdo->prepare("UPDATE {$table} SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
}

function category_delete(PDO $pdo, string $table, int $id): void
{
    $allowed = ['response', 'audience', 'warning'];
    if (!in_array($table, $allowed)) {
        throw new InvalidArgumentException('Invalid category table');
    }
    $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
    $stmt->execute([$id]);
}