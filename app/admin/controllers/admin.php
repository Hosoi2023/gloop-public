<?php

function admin_index(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    return render(__DIR__ . '/../views/index.php', [
        'page_title' => 'Admin Dashboard',
    ]);
}

function admin_users(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $operators = operator_all_with_roles($pdo);

    return render(__DIR__ . '/../views/users.php', [
        'page_title' => 'Users',
        'operators'  => $operators,
    ]);
}

function admin_categories(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $responses = category_all($pdo, 'response');
    $audiences = category_all($pdo, 'audience');
    $warnings  = category_all($pdo, 'warning');

    $edit_type = $_GET['edit'] ?? null;
    $edit_id   = isset($_GET['id']) ? (int) $_GET['id'] : null;
    $add_type  = $_GET['add'] ?? null;

    $edit_item = null;
    if ($edit_type && $edit_id) {
        $allowed = ['response', 'audience', 'warning'];
        if (in_array($edit_type, $allowed)) {
            $edit_item = category_find($pdo, $edit_type, $edit_id);
        }
    }

    return render(__DIR__ . '/../views/categories.php', [
        'page_title' => 'Categories',
        'responses'  => $responses,
        'audiences'  => $audiences,
        'warnings'   => $warnings,
        'edit_type'  => $edit_type,
        'edit_id'    => $edit_id,
        'edit_item'  => $edit_item,
        'add_type'   => $add_type,
    ]);
}

function admin_promote(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_role($pdo, (int) $id, 'admin');
    redirect('/admin/users');
}

function admin_demote(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_role($pdo, (int) $id, 'user');
    redirect('/admin/users');
}

function admin_ban(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_active($pdo, (int) $id, 0);
    redirect('/admin/users');
}

function admin_unban(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_active($pdo, (int) $id, 1);
    redirect('/admin/users');
}

function admin_categorycreate(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }
    $table = $_POST['table'] ?? '';
    $name  = trim($_POST['name'] ?? '');
    if ($name !== '') {
        category_create($pdo, $table, $name);
    }
    redirect('/admin/categories');
}

function admin_categoryupdate(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    $table = $_POST['table'] ?? '';
    $name  = trim($_POST['name'] ?? '');
    if ($name !== '') {
        category_update($pdo, $table, (int) $id, $name);
    }
    redirect('/admin/categories');
}

function admin_categorydelete(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    $table = $_POST['table'] ?? '';
    category_delete($pdo, $table, (int) $id);
    redirect('/admin/categories');
}

function admin_mute(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_muted($pdo, (int) $id, 1);
    redirect('/admin/users');
}

function admin_unmute(PDO $pdo, string $id): string
{
    if (!is_admin()) {
        redirect('/');
    }
    if ((int) $id === (int) $_SESSION['user_id']) {
        redirect('/admin/users');
    }
    operator_set_muted($pdo, (int) $id, 0);
    redirect('/admin/users');
}

function admin_manualentry(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $responses = $pdo->query('SELECT id, name FROM response ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $audiences = $pdo->query('SELECT id, name FROM audience ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $warnings  = $pdo->query('SELECT id, name FROM warning ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

    return render(__DIR__ . '/../views/manualentry.php', [
        'page_title' => 'Manual Entry',
        'responses'  => $responses,
        'audiences'  => $audiences,
        'warnings'   => $warnings,
    ]);
}

function admin_manualsave(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $operator_id = (int) $_SESSION['user_id'];

    // Sanitise inputs
    $title       = trim($_POST['title'] ?? '');
    $year        = !empty($_POST['year'])       ? (int) $_POST['year']     : null;
    $director    = trim($_POST['director']      ?? '');
    $runtime     = !empty($_POST['runtime'])    ? (int) $_POST['runtime']  : null;
    $description = trim($_POST['description']   ?? '');
    $poster      = trim($_POST['poster']        ?? '');
    $rating      = !empty($_POST['rating'])     ? (float) $_POST['rating'] : null;
    $comment     = trim($_POST['comment']       ?? '');
    $spoiler     = isset($_POST['spoiler'])     ? 1 : 0;
    $responses   = array_slice($_POST['responses'] ?? [], 0, 3);
    $audiences   = array_slice($_POST['audiences'] ?? [], 0, 3);
    $warnings    = array_slice($_POST['warnings']  ?? [], 0, 3);

    if ($title === '') {
        redirect('/admin/manualentry');
    }

    // Generate fake negative tmdb_id
    $stmt    = $pdo->query('SELECT COUNT(*) FROM item WHERE tmdb_id < 0');
    $count   = (int) $stmt->fetchColumn();
    $tmdb_id = ($count + 1) * -1;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

    // Ensure slug is unique
    $base_slug = $slug;
    $counter   = 1;
    while (true) {
        $stmt = $pdo->prepare('SELECT id FROM item WHERE slug = ?');
        $stmt->execute([$slug]);
        if (!$stmt->fetch()) break;
        $slug = $base_slug . '-' . $counter++;
    }

    // Insert item
    $stmt = $pdo->prepare('
        INSERT INTO item (tmdb_id, title, slug, content, main_image, release_year, runtime, director, status, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $tmdb_id,
        $title,
        $slug,
        $description,
        $poster ?: null,
        $year,
        $runtime,
        $director,
        'published',
        $operator_id,
    ]);
    $item_id = (int) $pdo->lastInsertId();

    // Save rating
    if ($rating !== null) {
        $stmt = $pdo->prepare('
            INSERT INTO rating (item_id, operator_id, score)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$item_id, $operator_id, $rating]);
    }

    // Save comment
    if ($comment !== '') {
        $stmt = $pdo->prepare('
            INSERT INTO comment (item_id, operator_id, body, spoiler)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$item_id, $operator_id, $comment, $spoiler]);
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

    redirect('/movie/view/m' . abs($tmdb_id));
}

function admin_entries(PDO $pdo): string
{
    if (!is_admin()) {
        redirect('/');
    }

    $stmt = $pdo->query('
        SELECT
            i.id,
            i.tmdb_id,
            i.title,
            i.director,
            i.release_year,
            COUNT(DISTINCT r.id) AS entry_count,
            ROUND(AVG(r.score), 1) AS avg_rating,
            COUNT(DISTINCT c.id) AS comment_count
        FROM item i
        LEFT JOIN rating r ON r.item_id = i.id
        LEFT JOIN comment c ON c.item_id = i.id
        GROUP BY i.id
        ORDER BY i.title ASC
    ');
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return render(__DIR__ . '/../views/entries.php', [
        'page_title' => 'Manage Entries',
        'entries'    => $entries,
    ]);
}