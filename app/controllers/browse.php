<?php

function browse_index(PDO $pdo): string
{
    // Fetch filter options
    $responses = $pdo->query('SELECT id, name FROM response ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $audiences = $pdo->query('SELECT id, name FROM audience ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $warnings  = $pdo->query('SELECT id, name FROM warning ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    // Get selected filters
    $filter_response    = !empty($_GET['response'])     ? (int) $_GET['response']       : null;
    $filter_audience    = !empty($_GET['audience'])     ? (int) $_GET['audience']       : null;
    $filter_warning     = !empty($_GET['warning'])      ? (int) $_GET['warning']        : null;
    $filter_year_from   = !empty($_GET['year_from'])    ? (int) $_GET['year_from']      : null;
    $filter_year_to     = !empty($_GET['year_to'])      ? (int) $_GET['year_to']        : null;
    $filter_rating_from = !empty($_GET['rating_from'])  ? (float) $_GET['rating_from'] : null;
    $filter_rating_to   = !empty($_GET['rating_to'])    ? (float) $_GET['rating_to']   : null;

    // Pagination
    $per_page    = 24;
    $page        = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $offset      = ($page - 1) * $per_page;

    // Build WHERE clause
    $where  = [];
    $params = [];

    if ($filter_response) {
        $where[]  = 'EXISTS (SELECT 1 FROM item_response ir WHERE ir.item_id = i.id AND ir.response_id = ?)';
        $params[] = $filter_response;
    }
    if ($filter_audience) {
        $where[]  = 'EXISTS (SELECT 1 FROM item_audience ia WHERE ia.item_id = i.id AND ia.audience_id = ?)';
        $params[] = $filter_audience;
    }
    if ($filter_warning) {
        $where[]  = 'EXISTS (SELECT 1 FROM item_warning iw WHERE iw.item_id = i.id AND iw.warning_id = ?)';
        $params[] = $filter_warning;
    }
    if ($filter_year_from) {
        $where[]  = 'i.release_year >= ?';
        $params[] = $filter_year_from;
    }
    if ($filter_year_to) {
        $where[]  = 'i.release_year <= ?';
        $params[] = $filter_year_to;
    }

    $where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    // Build HAVING clause
    $having        = [];
    $having_params = [];

    if ($filter_rating_from !== null) {
        $having[]        = 'avg_rating >= ?';
        $having_params[] = $filter_rating_from;
    }
    if ($filter_rating_to !== null) {
        $having[]        = 'avg_rating <= ?';
        $having_params[] = $filter_rating_to;
    }

    $having_sql = !empty($having) ? 'HAVING ' . implode(' AND ', $having) : '';

    // Get total count for pagination
    $count_stmt = $pdo->prepare("
        SELECT COUNT(*) FROM (
            SELECT i.id, ROUND(AVG(r.score), 1) AS avg_rating
            FROM item i
            LEFT JOIN rating r ON r.item_id = i.id
            {$where_sql}
            GROUP BY i.id
            {$having_sql}
        ) AS counted
    ");
    $count_stmt->execute(array_merge($params, $having_params));
    $total       = (int) $count_stmt->fetchColumn();
    $total_pages = (int) ceil($total / $per_page);
    $page        = min($page, max(1, $total_pages));

    // Fetch movies for current page
    $stmt = $pdo->prepare("
        SELECT
            i.id,
            i.tmdb_id,
            i.title,
            i.release_year AS year,
            i.main_image AS poster_url,
            ROUND(AVG(r.score), 1) AS avg_rating,
            COUNT(DISTINCT r.id) AS rating_count
        FROM item i
        LEFT JOIN rating r ON r.item_id = i.id
        {$where_sql}
        GROUP BY i.id
        {$having_sql}
        ORDER BY i.created_at DESC
        LIMIT {$per_page} OFFSET {$offset}
    ");
    $stmt->execute(array_merge($params, $having_params));
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build query string for pagination links (preserve filters)
    $query_params = array_filter([
        'response'     => $filter_response,
        'audience'     => $filter_audience,
        'warning'      => $filter_warning,
        'year_from'    => $filter_year_from,
        'year_to'      => $filter_year_to,
        'rating_from'  => $filter_rating_from,
        'rating_to'    => $filter_rating_to,
    ]);

    return render('app/views/browse.php', [
        'page_title'         => 'Browse Movies',
        'movies'             => $movies,
        'responses'          => $responses,
        'audiences'          => $audiences,
        'warnings'           => $warnings,
        'filter_response'    => $filter_response,
        'filter_audience'    => $filter_audience,
        'filter_warning'     => $filter_warning,
        'filter_year_from'   => $filter_year_from,
        'filter_year_to'     => $filter_year_to,
        'filter_rating_from' => $filter_rating_from,
        'filter_rating_to'   => $filter_rating_to,
        'page'               => $page,
        'total_pages'        => $total_pages,
        'total'              => $total,
        'query_params'       => $query_params,
    ]);
}