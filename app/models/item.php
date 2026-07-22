<?php

function get_entries_by_operator(PDO $pdo, int $operator_id, int $per_page = 24, int $offset = 0): array
{
    $stmt = $pdo->prepare('
        SELECT
            i.id,
            i.tmdb_id,
            i.title,
            i.release_year AS year,
            i.main_image AS poster_url,
            r.score AS rating,
            c.body AS comment
        FROM item i
        INNER JOIN (
            SELECT DISTINCT item_id FROM rating WHERE operator_id = ?
            UNION
            SELECT DISTINCT item_id FROM comment WHERE operator_id = ?
        ) AS user_items ON user_items.item_id = i.id
        LEFT JOIN rating r ON r.item_id = i.id AND r.operator_id = ?
        LEFT JOIN comment c ON c.item_id = i.id AND c.operator_id = ?
        ORDER BY i.created_at DESC
        LIMIT ' . $per_page . ' OFFSET ' . $offset . '
    ');
    $stmt->execute([$operator_id, $operator_id, $operator_id, $operator_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function count_entries_by_operator(PDO $pdo, int $operator_id): int
{
    $stmt = $pdo->prepare('
        SELECT COUNT(DISTINCT item_id) FROM (
            SELECT item_id FROM rating WHERE operator_id = ?
            UNION
            SELECT item_id FROM comment WHERE operator_id = ?
        ) AS user_items
    ');
    $stmt->execute([$operator_id, $operator_id]);
    return (int) $stmt->fetchColumn();
}