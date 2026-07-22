<?php

function movie_search(PDO $pdo): string
{
    return render('app/views/searchbar.php', [
        'page_title' => 'Search for a movie',
    ]);
}

function movie_create(PDO $pdo, string $id): string
{
    $url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '?api_key=' . TMDB_API_KEY;
    $response = file_get_contents($url);

    if ($response === false) {
        throw new RuntimeException('Failed to reach TMDB');
    }

    $data = json_decode($response, true);

    if (isset($data['status_code'])) {
        throw new RuntimeException('Movie not found');
    }

    $movie = [
        'tmdb_id'     => $data['id'],
        'title'       => $data['title'],
        'year'        => substr($data['release_date'] ?? '', 0, 4),
        'description' => $data['overview'] ?? '',
        'runtime'     => $data['runtime'] ?? null,
        'director'    => '',
        'poster'      => $data['poster_path']
            ? 'https://image.tmdb.org/t/p/w500' . $data['poster_path']
            : null,
        'trailer_url' => '',
        'genres'      => array_map(fn($g) => $g['name'], $data['genres'] ?? []),
    ];

    // Fetch director from credits endpoint
    $credits_url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '/credits?api_key=' . TMDB_API_KEY;
    $credits_response = file_get_contents($credits_url);
    if ($credits_response !== false) {
        $credits = json_decode($credits_response, true);
        foreach ($credits['crew'] ?? [] as $member) {
            if ($member['job'] === 'Director') {
                $movie['director'] = $member['name'];
                break;
            }
        }
    }

    // Fetch responses, audiences, warnings for the form
    $responses = $pdo->query('SELECT id, name FROM response ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $audiences = $pdo->query('SELECT id, name FROM audience ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $warnings  = $pdo->query('SELECT id, name FROM warning ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    return render('app/views/create.php', [
        'page_title' => 'Add ' . $data['title'] . ' to Gloop',
        'movie'      => $movie,
        'responses'  => $responses,
        'audiences'  => $audiences,
        'warnings'   => $warnings,
    ]);
}

function movie_save(PDO $pdo): string
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/login');
    }

    $operator_id = (int) $_SESSION['user_id'];

    // Sanitise inputs
    $tmdb_id     = (int) ($_POST['tmdb_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $year        = trim($_POST['year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $runtime     = !empty($_POST['runtime']) ? (int) $_POST['runtime'] : null;
    $director    = trim($_POST['director'] ?? '');
    $poster      = trim($_POST['poster'] ?? '');
    $rating      = !empty($_POST['rating']) ? (float) $_POST['rating'] : null;
    $comment     = trim($_POST['comment'] ?? '');
    $spoiler     = isset($_POST['spoiler']) ? 1 : 0;
    $responses   = $_POST['responses'] ?? [];
    $audiences   = $_POST['audiences'] ?? [];
    $warnings    = $_POST['warnings'] ?? [];

    // Enforce max 3 on each
    $responses = array_slice($responses, 0, 3);
    $audiences = array_slice($audiences, 0, 3);
    $warnings  = array_slice($warnings, 0, 3);

    // Generate slug from title
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

    // Insert or retrieve existing item
    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $item_id = (int) $existing['id'];
    } else {
        $stmt = $pdo->prepare('
            INSERT INTO item (tmdb_id, title, slug, content, main_image, release_year, runtime, director, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $tmdb_id,
            $title,
            $slug,
            $description,
            $poster,
            $year ?: null,
            $runtime,
            $director,
            'published',
            $operator_id,
        ]);
        $item_id = (int) $pdo->lastInsertId();
    }

    // Save rating
    if ($rating !== null) {
        $stmt = $pdo->prepare('
            INSERT INTO rating (item_id, operator_id, score)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE score = VALUES(score)
        ');
        $stmt->execute([$item_id, $operator_id, $rating]);
    }

    // Save comment
    if ($comment !== '') {
        $stmt = $pdo->prepare('SELECT is_muted FROM operator WHERE id = ?');
        $stmt->execute([$operator_id]);
        $is_muted = (bool) $stmt->fetchColumn();

        if (!$is_muted) {
            $stmt = $pdo->prepare('
                INSERT INTO comment (item_id, operator_id, body, spoiler)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$item_id, $operator_id, $comment, $spoiler]);
        }
    }

    // Save responses
    if (!empty($responses)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_response (item_id, operator_id, response_id) VALUES (?, ?, ?)');
        foreach ($responses as $response_id) {
            $stmt->execute([$item_id, $operator_id, (int) $response_id]);
        }
    }

    // Save audiences
    if (!empty($audiences)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_audience (item_id, operator_id, audience_id) VALUES (?, ?, ?)');
        foreach ($audiences as $audience_id) {
            $stmt->execute([$item_id, $operator_id, (int) $audience_id]);
        }
    }

    // Save warnings
    if (!empty($warnings)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_warning (item_id, operator_id, warning_id) VALUES (?, ?, ?)');
        foreach ($warnings as $warning_id) {
            $stmt->execute([$item_id, $operator_id, (int) $warning_id]);
        }
    }

    redirect('/');
}

function movie_remove(PDO $pdo, string $id): string
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/login');
    }

    $operator_id = (int) $_SESSION['user_id'];
    $tmdb_id     = (int) $id;

    // Get the item id from the tmdb_id
    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        redirect('/');
    }

    $item_id = (int) $item['id'];

    // Delete the user's rating, comment, and selections
    $pdo->prepare('DELETE FROM rating WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    $pdo->prepare('DELETE FROM comment WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    $pdo->prepare('DELETE FROM item_response WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    $pdo->prepare('DELETE FROM item_audience WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    $pdo->prepare('DELETE FROM item_warning WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    // If no other users have entries for this film, remove the item itself
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM rating WHERE item_id = ?');
    $stmt->execute([$item_id]);
    $remaining_ratings = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM comment WHERE item_id = ?');
    $stmt->execute([$item_id]);
    $remaining_comments = (int) $stmt->fetchColumn();

    if ($remaining_ratings === 0 && $remaining_comments === 0) {
        $pdo->prepare('DELETE FROM item WHERE id = ?')->execute([$item_id]);
    }

    redirect('/');
}

function movie_view(PDO $pdo, string $id): string
{
    // Manual entry — load from database instead of TMDB
    if (str_starts_with($id, 'm')) {
        $real_id = (int) substr($id, 1) * -1;
        $stmt = $pdo->prepare('SELECT * FROM item WHERE tmdb_id = ?');
        $stmt->execute([$real_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RuntimeException('Movie not found');
        }

        $movie = [
            'tmdb_id'      => $row['tmdb_id'],
            'title'        => $row['title'],
            'year'         => $row['release_year'],
            'description'  => $row['content'] ?? '',
            'rating'       => null,
            'rating_count' => 0,
            'runtime'      => $row['runtime'],
            'director'     => $row['director'] ?? '',
            'poster'       => $row['main_image'],
            'genres'       => [],
        ];
    } else {
        // Normal TMDB flow
        $url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '?api_key=' . TMDB_API_KEY;
        $response = file_get_contents($url);

        if ($response === false) {
            throw new RuntimeException('Failed to reach TMDB');
        }

        $data = json_decode($response, true);

        if (isset($data['status_code'])) {
            throw new RuntimeException('Movie not found');
        }

        $movie = [
            'tmdb_id'      => $data['id'],
            'title'        => $data['title'],
            'year'         => substr($data['release_date'] ?? '', 0, 4),
            'description'  => $data['overview'] ?? '',
            'rating'       => round($data['vote_average'] / 2, 1),
            'rating_count' => $data['vote_count'],
            'runtime'      => $data['runtime'] ?? null,
            'director'     => '',
            'poster'       => $data['poster_path']
                ? 'https://image.tmdb.org/t/p/w500' . $data['poster_path']
                : null,
            'genres'       => array_map(fn($g) => $g['name'], $data['genres'] ?? []),
        ];

        // Fetch director from credits
        $credits_url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '/credits?api_key=' . TMDB_API_KEY;
        $credits_response = file_get_contents($credits_url);
        if ($credits_response !== false) {
            $credits = json_decode($credits_response, true);
            foreach ($credits['crew'] ?? [] as $member) {
                if ($member['job'] === 'Director') {
                    $movie['director'] = $member['name'];
                    break;
                }
            }
        }

        // Override TMDB data with any admin-edited values from the database
        $stmt = $pdo->prepare('SELECT * FROM item WHERE tmdb_id = ?');
        $stmt->execute([$movie['tmdb_id']]);
        $stored = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stored) {
            $movie['title']       = $stored['title'];
            $movie['description'] = $stored['content']      ?? $movie['description'];
            $movie['year']        = $stored['release_year'] ?? $movie['year'];
            $movie['runtime']     = $stored['runtime']      ?? $movie['runtime'];
            $movie['director']    = $stored['director']     ?? $movie['director'];
            $movie['poster']      = $stored['main_image']   ?? $movie['poster'];
        }
    }

    // Check if the current user has already added this film
    $user_has_entry = false;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare('
            SELECT 1 FROM item i
            LEFT JOIN rating r ON r.item_id = i.id AND r.operator_id = ?
            LEFT JOIN comment c ON c.item_id = i.id AND c.operator_id = ?
            WHERE i.tmdb_id = ?
            AND (r.id IS NOT NULL OR c.id IS NOT NULL)
            LIMIT 1
        ');
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $movie['tmdb_id']]);
        $user_has_entry = (bool) $stmt->fetchColumn();
    }

    // Fetch Gloop average rating
    $stmt = $pdo->prepare('
        SELECT ROUND(AVG(r.score), 1) AS avg_rating, COUNT(*) AS rating_count
        FROM rating r
        JOIN item i ON i.id = r.item_id
        WHERE i.tmdb_id = ?
    ');
    $stmt->execute([$movie['tmdb_id']]);
    $gloop_data   = $stmt->fetch(PDO::FETCH_ASSOC);
    $gloop_rating = $gloop_data['avg_rating'];
    $gloop_count  = (int) $gloop_data['rating_count'];

    // Fetch Gloop user comments for this movie
    $stmt = $pdo->prepare('
        SELECT c.body, c.created_at, c.spoiler, o.username, r.score AS rating
        FROM comment c
        JOIN operator o ON o.id = c.operator_id
        LEFT JOIN rating r ON r.operator_id = c.operator_id AND r.item_id = c.item_id
        WHERE c.item_id = (SELECT id FROM item WHERE tmdb_id = ? LIMIT 1)
        ORDER BY c.created_at DESC
    ');
    $stmt->execute([$movie['tmdb_id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch top 3 responses for this movie
    $stmt = $pdo->prepare('
        SELECT r.name, COUNT(*) AS votes
        FROM item_response ir
        JOIN response r ON r.id = ir.response_id
        JOIN item i ON i.id = ir.item_id
        WHERE i.tmdb_id = ?
        GROUP BY ir.response_id
        ORDER BY votes DESC
        LIMIT 3
    ');
    $stmt->execute([$movie['tmdb_id']]);
    $top_responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch top 3 audiences for this movie
    $stmt = $pdo->prepare('
        SELECT a.name, COUNT(*) AS votes
        FROM item_audience ia
        JOIN audience a ON a.id = ia.audience_id
        JOIN item i ON i.id = ia.item_id
        WHERE i.tmdb_id = ?
        GROUP BY ia.audience_id
        ORDER BY votes DESC
        LIMIT 3
    ');
    $stmt->execute([$movie['tmdb_id']]);
    $top_audiences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch top 3 warnings for this movie
    $stmt = $pdo->prepare('
        SELECT w.name, COUNT(*) AS votes
        FROM item_warning iw
        JOIN warning w ON w.id = iw.warning_id
        JOIN item i ON i.id = iw.item_id
        WHERE i.tmdb_id = ?
        GROUP BY iw.warning_id
        ORDER BY votes DESC
        LIMIT 3
    ');
    $stmt->execute([$movie['tmdb_id']]);
    $top_warnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return render('app/views/movie-view.php', [
        'page'           => 'movie',
        'movie'          => $movie,
        'comments'       => $comments,
        'user_has_entry' => $user_has_entry,
        'top_responses'  => $top_responses,
        'top_audiences'  => $top_audiences,
        'top_warnings'   => $top_warnings,
        'gloop_rating'   => $gloop_rating,
        'gloop_count'    => $gloop_count,
    ]);
}

function movie_edit(PDO $pdo, string $id): string
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/login');
    }

    $operator_id = (int) $_SESSION['user_id'];
    $tmdb_id     = (int) $id;

    // Fetch movie data from TMDB
    $url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '?api_key=' . TMDB_API_KEY;
    $response = file_get_contents($url);

    if ($response === false) {
        throw new RuntimeException('Failed to reach TMDB');
    }

    $data = json_decode($response, true);

    if (isset($data['status_code'])) {
        throw new RuntimeException('Movie not found');
    }

    $movie = [
        'tmdb_id'     => $data['id'],
        'title'       => $data['title'],
        'year'        => substr($data['release_date'] ?? '', 0, 4),
        'description' => $data['overview'] ?? '',
        'runtime'     => $data['runtime'] ?? null,
        'director'    => '',
        'poster'      => $data['poster_path']
            ? 'https://image.tmdb.org/t/p/w500' . $data['poster_path']
            : null,
        'genres'      => array_map(fn($g) => $g['name'], $data['genres'] ?? []),
    ];

    // Fetch director
    $credits_url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '/credits?api_key=' . TMDB_API_KEY;
    $credits_response = file_get_contents($credits_url);
    if ($credits_response !== false) {
        $credits = json_decode($credits_response, true);
        foreach ($credits['crew'] ?? [] as $member) {
            if ($member['job'] === 'Director') {
                $movie['director'] = $member['name'];
                break;
            }
        }
    }

    // Get item_id
    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        redirect('/');
    }

    $item_id = (int) $item['id'];

    // Fetch existing rating
    $stmt = $pdo->prepare('SELECT score FROM rating WHERE item_id = ? AND operator_id = ?');
    $stmt->execute([$item_id, $operator_id]);
    $existing_rating = $stmt->fetchColumn();

    // Fetch existing comment
    $stmt = $pdo->prepare('SELECT body, spoiler FROM comment WHERE item_id = ? AND operator_id = ?');
    $stmt->execute([$item_id, $operator_id]);
    $existing_comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch existing selected response ids
    $stmt = $pdo->prepare('SELECT response_id FROM item_response WHERE item_id = ? AND operator_id = ?');
    $stmt->execute([$item_id, $operator_id]);
    $selected_responses = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch existing selected audience ids
    $stmt = $pdo->prepare('SELECT audience_id FROM item_audience WHERE item_id = ? AND operator_id = ?');
    $stmt->execute([$item_id, $operator_id]);
    $selected_audiences = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch existing selected warning ids
    $stmt = $pdo->prepare('SELECT warning_id FROM item_warning WHERE item_id = ? AND operator_id = ?');
    $stmt->execute([$item_id, $operator_id]);
    $selected_warnings = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Fetch all options for the form
    $responses = $pdo->query('SELECT id, name FROM response ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $audiences = $pdo->query('SELECT id, name FROM audience ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $warnings  = $pdo->query('SELECT id, name FROM warning ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    return render('app/views/edit.php', [
        'page_title'         => 'Edit ' . $data['title'],
        'movie'              => $movie,
        'responses'          => $responses,
        'audiences'          => $audiences,
        'warnings'           => $warnings,
        'existing_rating'    => $existing_rating,
        'existing_comment'   => $existing_comment,
        'selected_responses' => $selected_responses,
        'selected_audiences' => $selected_audiences,
        'selected_warnings'  => $selected_warnings,
    ]);
}

function movie_update(PDO $pdo, string $id): string
{
    if (!isset($_SESSION['user_id'])) {
        redirect('/login');
    }

    $operator_id = (int) $_SESSION['user_id'];
    $tmdb_id     = (int) $id;

    // Get item_id
    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        redirect('/');
    }

    $item_id = (int) $item['id'];

    // Sanitise inputs
    $rating    = !empty($_POST['rating']) ? (float) $_POST['rating'] : null;
    $comment   = trim($_POST['comment'] ?? '');
    $spoiler   = isset($_POST['spoiler']) ? 1 : 0;
    $responses = array_slice($_POST['responses'] ?? [], 0, 3);
    $audiences = array_slice($_POST['audiences'] ?? [], 0, 3);
    $warnings  = array_slice($_POST['warnings'] ?? [], 0, 3);

    // Update or delete rating
    if ($rating !== null) {
        $stmt = $pdo->prepare('
            INSERT INTO rating (item_id, operator_id, score)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE score = VALUES(score)
        ');
        $stmt->execute([$item_id, $operator_id, $rating]);
    } else {
        $pdo->prepare('DELETE FROM rating WHERE item_id = ? AND operator_id = ?')
            ->execute([$item_id, $operator_id]);
    }

    // Update or delete comment
    $pdo->prepare('DELETE FROM comment WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);

    if ($comment !== '') {
        $stmt = $pdo->prepare('SELECT is_muted FROM operator WHERE id = ?');
        $stmt->execute([$operator_id]);
        $is_muted = (bool) $stmt->fetchColumn();

        if (!$is_muted) {
            $stmt = $pdo->prepare('
                INSERT INTO comment (item_id, operator_id, body, spoiler)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$item_id, $operator_id, $comment, $spoiler]);
        }
    }

    // Replace responses
    $pdo->prepare('DELETE FROM item_response WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);
    if (!empty($responses)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_response (item_id, operator_id, response_id) VALUES (?, ?, ?)');
        foreach ($responses as $response_id) {
            $stmt->execute([$item_id, $operator_id, (int) $response_id]);
        }
    }

    // Replace audiences
    $pdo->prepare('DELETE FROM item_audience WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);
    if (!empty($audiences)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_audience (item_id, operator_id, audience_id) VALUES (?, ?, ?)');
        foreach ($audiences as $audience_id) {
            $stmt->execute([$item_id, $operator_id, (int) $audience_id]);
        }
    }

    // Replace warnings
    $pdo->prepare('DELETE FROM item_warning WHERE item_id = ? AND operator_id = ?')
        ->execute([$item_id, $operator_id]);
    if (!empty($warnings)) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO item_warning (item_id, operator_id, warning_id) VALUES (?, ?, ?)');
        foreach ($warnings as $warning_id) {
            $stmt->execute([$item_id, $operator_id, (int) $warning_id]);
        }
    }

    redirect('/movie/view/' . $tmdb_id);
}

/* admin tools */

function movie_admindelete(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $tmdb_id = str_starts_with($id, 'm') ? (int) substr($id, 1) * -1 : (int) $id;

    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        redirect('/browse');
    }

    $item_id = (int) $item['id'];

    // Cascade delete all associated data
    $pdo->prepare('DELETE FROM rating WHERE item_id = ?')->execute([$item_id]);
    $pdo->prepare('DELETE FROM comment WHERE item_id = ?')->execute([$item_id]);
    $pdo->prepare('DELETE FROM item_response WHERE item_id = ?')->execute([$item_id]);
    $pdo->prepare('DELETE FROM item_audience WHERE item_id = ?')->execute([$item_id]);
    $pdo->prepare('DELETE FROM item_warning WHERE item_id = ?')->execute([$item_id]);
    $pdo->prepare('DELETE FROM item WHERE id = ?')->execute([$item_id]);

    redirect('/browse');
}

function movie_adminedit(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }

    // Fetch from DB for manual entries, TMDB for normal entries
    if (str_starts_with($id, 'm')) {
        $real_id = (int) substr($id, 1) * -1;
        $stmt = $pdo->prepare('SELECT * FROM item WHERE tmdb_id = ?');
        $stmt->execute([$real_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new RuntimeException('Movie not found');
        }

        $movie = [
            'tmdb_id'     => $row['tmdb_id'],
            'title'       => $row['title'],
            'year'        => $row['release_year'],
            'description' => $row['content'] ?? '',
            'runtime'     => $row['runtime'],
            'director'    => $row['director'] ?? '',
            'poster'      => $row['main_image'] ?? '',
        ];
    } else {
        $url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '?api_key=' . TMDB_API_KEY;
        $response = file_get_contents($url);

        if ($response === false) {
            throw new RuntimeException('Failed to reach TMDB');
        }

        $data = json_decode($response, true);

        if (isset($data['status_code'])) {
            throw new RuntimeException('Movie not found');
        }

        $movie = [
            'tmdb_id'     => $data['id'],
            'title'       => $data['title'],
            'year'        => substr($data['release_date'] ?? '', 0, 4),
            'description' => $data['overview'] ?? '',
            'runtime'     => $data['runtime'] ?? null,
            'director'    => '',
            'poster'      => $data['poster_path']
                ? 'https://image.tmdb.org/t/p/w500' . $data['poster_path']
                : '',
        ];

        // Fetch director from TMDB credits
        $credits_url = 'https://api.themoviedb.org/3/movie/' . urlencode($id) . '/credits?api_key=' . TMDB_API_KEY;
        $credits_response = file_get_contents($credits_url);
        if ($credits_response !== false) {
            $credits = json_decode($credits_response, true);
            foreach ($credits['crew'] ?? [] as $member) {
                if ($member['job'] === 'Director') {
                    $movie['director'] = $member['name'];
                    break;
                }
            }
        }

        // Override with any admin-edited values from the database
        // Skip if user clicked "Autocomplete from TMDB"
        if (empty($_GET['tmdb'])) {
            $stmt = $pdo->prepare('SELECT * FROM item WHERE tmdb_id = ?');
            $stmt->execute([$movie['tmdb_id']]);
            $stored = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stored) {
                $movie['title']       = $stored['title'];
                $movie['description'] = $stored['content']      ?? $movie['description'];
                $movie['year']        = $stored['release_year'] ?? $movie['year'];
                $movie['runtime']     = $stored['runtime']      ?? $movie['runtime'];
                $movie['director']    = $stored['director']     ?? $movie['director'];
                $movie['poster']      = $stored['main_image']   ?? $movie['poster'];
            }
        }
    }

    return render(__DIR__ . '/../admin/views/adminedit.php', [
        'page_title' => 'Edit ' . $movie['title'],
        'movie'      => $movie,
    ]);
}

function movie_adminupdate(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $tmdb_id = str_starts_with($id, 'm') ? (int) substr($id, 1) * -1 : (int) $id;

    $title       = trim($_POST['title'] ?? '');
    $year        = !empty($_POST['year'])       ? (int) $_POST['year']    : null;
    $director    = trim($_POST['director']      ?? '');
    $runtime     = !empty($_POST['runtime'])    ? (int) $_POST['runtime'] : null;
    $description = trim($_POST['description']   ?? '');
    $poster      = trim($_POST['poster']        ?? '');

    if ($title === '') {
        redirect('/movie/adminedit/' . $id);
    }

    // Regenerate slug
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

    // Ensure slug is unique (excluding current item)
    $stmt = $pdo->prepare('SELECT id FROM item WHERE tmdb_id = ?');
    $stmt->execute([$tmdb_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        redirect('/browse');
    }

    $item_id   = (int) $item['id'];
    $base_slug = $slug;
    $counter   = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM item WHERE slug = ? AND id != ?');
        $stmt->execute([$slug, $item_id]);
        if (!$stmt->fetch()) break;
        $slug = $base_slug . '-' . $counter++;
    }

    $stmt = $pdo->prepare('
        UPDATE item SET
            title        = ?,
            slug         = ?,
            content      = ?,
            main_image   = ?,
            release_year = ?,
            runtime      = ?,
            director     = ?
        WHERE id = ?
    ');
    $stmt->execute([
        $title,
        $slug,
        $description,
        $poster ?: null,
        $year,
        $runtime,
        $director,
        $item_id,
    ]);

    redirect('/movie/view/' . $id);
}