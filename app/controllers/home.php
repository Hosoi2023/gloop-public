<?php

function home_index(PDO $pdo): string
{
    $movie_entries = [];

    if (isset($_SESSION['user_id'])) {
        $movie_entries = get_entries_by_operator($pdo, (int) $_SESSION['user_id']);
    }

    // Fetch 4 random movies from the database that have ratings
    $stmt = $pdo->query('
        SELECT
            i.id,
            i.tmdb_id,
            i.title,
            i.main_image AS poster_url,
            ROUND(AVG(r.score), 1) AS avg_rating
        FROM item i
        JOIN rating r ON r.item_id = i.id
        GROUP BY i.id
        ORDER BY RAND()
        LIMIT 4
    ');
    $featured_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch top reaction, audience and warning for each featured movie
    foreach ($featured_movies as &$movie) {
        $stmt = $pdo->prepare('
            SELECT re.name FROM item_response ir
            JOIN response re ON re.id = ir.response_id
            WHERE ir.item_id = ?
            GROUP BY ir.response_id
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ');
        $stmt->execute([$movie['id']]);
        $movie['top_response'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare('
            SELECT a.name FROM item_audience ia
            JOIN audience a ON a.id = ia.audience_id
            WHERE ia.item_id = ?
            GROUP BY ia.audience_id
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ');
        $stmt->execute([$movie['id']]);
        $movie['top_audience'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare('
            SELECT w.name FROM item_warning iw
            JOIN warning w ON w.id = iw.warning_id
            WHERE iw.item_id = ?
            GROUP BY iw.warning_id
            ORDER BY COUNT(*) DESC
            LIMIT 1
        ');
        $stmt->execute([$movie['id']]);
        $movie['top_warning'] = $stmt->fetchColumn();
    }
    unset($movie);

    // Fetch 6 most entered movies in the past week
    $stmt = $pdo->query("
        SELECT
            i.tmdb_id,
            i.title,
            i.main_image AS poster_url,
            COUNT(r.id) AS entry_count
        FROM item i
        JOIN rating r ON r.item_id = i.id
        WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY i.id
        ORDER BY entry_count DESC
        LIMIT 6
    ");
    $trending_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $per_page    = 24;
    $page        = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $offset      = ($page - 1) * $per_page;

    if (isset($_SESSION['user_id'])) {
        $total_entries = count_entries_by_operator($pdo, (int) $_SESSION['user_id']);
        $total_pages   = (int) ceil($total_entries / $per_page);
        $page          = min($page, max(1, $total_pages));
        $movie_entries = get_entries_by_operator($pdo, (int) $_SESSION['user_id'], $per_page, ($page - 1) * $per_page);
    } else {
        $total_entries = 0;
        $total_pages   = 1;
    }

    return render('app/views/home.php', [
        'page_title'      => 'Gloop',
        'movie_entries'   => $movie_entries,
        'featured_movies' => $featured_movies,
        'trending_movies' => $trending_movies,
        'page'            => $page,
        'total_pages'     => $total_pages,
    ]);
}