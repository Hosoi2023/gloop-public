<main class="movie-detail-page">

    <a href="#" class="return-link" onclick="history.back(); return false;">← Return</a>

    <section class="movie-detail-hero">

        <div class="movie-poster-large">
            <?php if ($movie['poster']): ?>
                <img
                    src="<?= htmlspecialchars($movie['poster']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?> poster"
                >
            <?php else: ?>
                <div class="no-poster">No poster</div>
            <?php endif; ?>
        </div>

        <div class="movie-info">

            <div class="movie-tags">
                <?php foreach ($movie['genres'] as $genre): ?>
                    <span><?= htmlspecialchars($genre) ?></span>
                <?php endforeach; ?>
            </div>

            <h1><?= htmlspecialchars($movie['title']) ?></h1>

            <div class="create-meta">
                <?php if ($movie['year']): ?>
                    <span><?= htmlspecialchars($movie['year']) ?></span>
                <?php endif; ?>
                <?php if (!empty($movie['director'])): ?>
                    <span>Dir. <?= htmlspecialchars($movie['director']) ?></span>
                <?php endif; ?>
                <?php if (!empty($movie['runtime'])): ?>
                    <span><?= htmlspecialchars($movie['runtime']) ?>min</span>
                <?php endif; ?>
            </div>

            <p class="movie-description">
                <?= htmlspecialchars($movie['description']) ?>
            </p>

            <!-- Gloop community data -->
            <?php if (!empty($top_responses) || !empty($top_audiences) || !empty($top_warnings)): ?>
                <div class="movie-gloop-data">
                    <?php if (!empty($top_responses)): ?>
                        <div class="gloop-data-row">
                            <span class="gloop-data-label">Reaction</span>
                            <div class="gloop-data-pills">
                                <?php foreach ($top_responses as $r): ?>
                                    <span class="gloop-pill"><span class="pill-votes"><?= $r['votes'] ?></span> <?= htmlspecialchars($r['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($top_audiences)): ?>
                        <div class="gloop-data-row">
                            <span class="gloop-data-label">Audience</span>
                            <div class="gloop-data-pills">
                                <?php foreach ($top_audiences as $a): ?>
                                    <span class="gloop-pill"><span class="pill-votes"><?= $a['votes'] ?></span> <?= htmlspecialchars($a['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($top_warnings)): ?>
                        <div class="gloop-data-row">
                            <span class="gloop-data-label">Contains</span>
                            <div class="gloop-data-pills">
                                <?php foreach ($top_warnings as $w): ?>
                                    <span class="gloop-pill warning-pill"><span class="pill-votes"><?= $w['votes'] ?></span> <?= htmlspecialchars($w['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (is_admin()): ?>
                <div class="admin-tools">
                    <p class="admin-tools-label">Admin tools</p>
                    <div class="admin-tools-buttons">
                        <a href="/movie/adminedit/<?= movie_id_for_url($movie['tmdb_id']) ?>" class="admin-edit-btn">✏️ Edit movie info</a>
                        <a href="/movie/admindelete/<?= movie_id_for_url($movie['tmdb_id']) ?>"
                        class="admin-delete-btn"
                        onclick="return confirm('Permanently delete <?= htmlspecialchars($movie['title'], ENT_QUOTES) ?> and all associated data? This cannot be undone.')">
                            🗑 Delete from database
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Rating box positioned top right -->
        <div class="movie-side-panel">
            <div class="movie-score-row">
                <div class="score-block">
                    <span class="score-label">Gloop</span>
                    <?php if ($gloop_rating): ?>
                        <span class="score-value"><?= htmlspecialchars($gloop_rating) ?><span class="score-outof">/10</span></span>
                        <span class="score-count"><?= $gloop_count ?> <?= $gloop_count === 1 ? 'rating' : 'ratings' ?></span>
                    <?php else: ?>
                        <span class="score-none">No ratings yet</span>
                    <?php endif; ?>
                </div>
                <div class="score-block">
                    <span class="score-label">TMDB</span>
                    <?php if ($movie['rating'] !== null): ?>
                        <span class="score-value"><?= htmlspecialchars($movie['rating']) ?><span class="score-outof">/5</span></span>
                        <span class="score-count"><?= number_format($movie['rating_count']) ?> ratings</span>
                    <?php else: ?>
                        <span class="score-none">N/A</span>
                    <?php endif; ?>
                </div>
            </div>

        <!-- Action buttons positioned below ratings -->
            <div class="movie-actions">
                <?php if ($user_has_entry): ?>
                    <a href="/movie/edit/<?= htmlspecialchars($movie['tmdb_id']) ?>" class="watchlist-btn">
                        Edit entry
                    </a>
                    <a href="/movie/remove/<?= htmlspecialchars($movie['tmdb_id']) ?>" class="watchlist-btn danger-btn">
                        Remove from Gloop
                    </a>
                <?php elseif (is_logged()): ?>
                    <a href="/movie/create/<?= htmlspecialchars($movie['tmdb_id']) ?>" class="watchlist-btn">
                        + Add to Gloop
                    </a>
                <?php else: ?>
                    <a href="/login" class="watchlist-btn">
                        + Add to Gloop
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <section class="movie-comments">
        <div class="comments-header">
            <h2>What Gloop users said</h2>
            <?php if (!empty($comments)): ?>
                <span class="comments-count"><?= count($comments) ?> <?= count($comments) === 1 ? 'entry' : 'entries' ?></span>
            <?php endif; ?>
        </div>

        <?php if (!empty($comments)): ?>
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-top">
                            <div class="comment-author">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                                </div>
                                <div class="comment-author-info">
                                    <span class="comment-username"><?= htmlspecialchars($comment['username']) ?></span>
                                    <span class="comment-date"><?= htmlspecialchars(date('d M Y', strtotime($comment['created_at']))) ?></span>
                                </div>
                            </div>
                            <?php if (!empty($comment['rating'])): ?>
                                <span class="comment-rating">★ <?= htmlspecialchars($comment['rating']) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($comment['spoiler']): ?>
                            <div class="spoiler-wrapper">
                                <p class="spoiler-notice" onclick="this.parentElement.classList.toggle('revealed')">
                                    ⚠️ Contains spoilers. <span>View anyway?</span>
                                </p>
                                <p class="comment-body spoiler-body">
                                    <?= htmlspecialchars($comment['body']) ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="comment-body"><?= htmlspecialchars($comment['body']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-comments">No entries yet. Be the first to add this to your Gloop.</p>
        <?php endif; ?>
    </section>

</main>