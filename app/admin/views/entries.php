<main class="admin-page">
    <div class="admin-header">
        <div>
            <a href="/admin" class="return-link">← Back to Dashboard</a>
            <h1>Manage Entries</h1>
        </div>
    </div>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>All movies</h2>
            <span><?= count($entries) ?> total</span>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Director</th>
                    <th>Year</th>
                    <th>Entries</th>
                    <th>Avg rating</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?= htmlspecialchars($entry['title']) ?></td>
                        <td><?= htmlspecialchars($entry['director'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($entry['release_year'] ?? '—') ?></td>
                        <td><?= (int) $entry['entry_count'] ?></td>
                        <td><?= $entry['avg_rating'] ?? '—' ?></td>
                        <td><?= (int) $entry['comment_count'] ?></td>
                        <td>
                            <a href="/movie/adminedit/<?= movie_id_for_url($entry['tmdb_id']) ?>" class="action-link">Edit info</a>
                            &nbsp;·&nbsp;
                            <a href="/movie/admindelete/<?= movie_id_for_url($entry['tmdb_id']) ?>"
                               class="action-link danger"
                               onclick="return confirm('Permanently delete <?= htmlspecialchars($entry['title'], ENT_QUOTES) ?> and all associated data? This cannot be undone.')">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>