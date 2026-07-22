<?php if (is_logged()): ?>

    <main class="logged-home-page">
        <section class="logged-home-header">
            <div class="logged-home-header-row">
                <h1>Movie entries</h1>
                <p class="page-intro">Keep track of what you watched, rated, and want to remember.</p>
            </div>
            <a href="/movie/search" class="new-entry-btn">
                New movie entry
            </a>
        </section>
        <section class="movie-entry-section">
            <?php if (!empty($movie_entries)): ?>
                <div class="movie-entry-grid">
                    <?php foreach ($movie_entries as $entry): ?>
                        <article class="movie-entry-card">
                            <a href="<?= movie_url($entry['tmdb_id']) ?>" class="movie-entry-link">
                                <div class="movie-entry-poster">
                                    <?php if (!empty($entry['poster_url'])): ?>
                                        <img
                                            src="<?= htmlspecialchars($entry['poster_url']) ?>"
                                            alt="<?= htmlspecialchars($entry['title']) ?> poster"
                                        >
                                    <?php else: ?>
                                        <div class="poster-placeholder">
                                            <?= htmlspecialchars(substr($entry['title'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['rating'])): ?>
                                        <span class="movie-rating">
                                            <?= htmlspecialchars($entry['rating']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="movie-entry-info">
                                    <h3><?= htmlspecialchars($entry['title']) ?></h3>
                                    <?php if (!empty($entry['year'])): ?>
                                        <p class="movie-year"><?= htmlspecialchars($entry['year']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($entry['comment'])): ?>
                                        <p class="movie-comment"><?= htmlspecialchars($entry['comment']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="/?page=<?= $page - 1 ?>" class="pagination-btn">← Previous</a>
                        <?php else: ?>
                            <a class="pagination-btn disabled">← Previous</a>
                        <?php endif; ?>

                        <div class="pagination-pages">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if (
                                    $i === 1 ||
                                    $i === $total_pages ||
                                    abs($i - $page) <= 2
                                ): ?>
                                    <a
                                        href="/?page=<?= $i ?>"
                                        class="pagination-page <?= $i === $page ? 'active' : '' ?>"
                                    ><?= $i ?></a>
                                <?php elseif (abs($i - $page) === 3): ?>
                                    <a class="pagination-ellipsis">…</a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="/?page=<?= $page + 1 ?>" class="pagination-btn">Next →</a>
                        <?php else: ?>
                            <a class="pagination-btn disabled">Next →</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="empty-catalogue">
                    <h3>No movie entries yet</h3>
                    <p>Start building your Gloop catalogue by adding your first movie.</p>
                    <a href="/movie/search" class="empty-new-entry-link">
                        New movie entry
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php else: ?>

    <main class="hero">
        <section class="hero-text">
            <h1>
                Watch<br>
                Rate<br>
                <span>Discover</span>
            </h1>
            <p>
                Track what you've watched. Tag how it made you feel. Discover films in ways no other site lets you.
            </p>
            <div class="buttons">
                <a href="/signup" class="btn primary">Get Started</a>
                <a href="/browse" class="btn secondary">🎬 Browse movies</a>
            </div>
        </section>

        <section class="poster-hero">
            <?php if (!empty($featured_movies)): ?>
                <?php foreach ($featured_movies as $movie): ?>
                    <a href="<?= movie_url($movie['tmdb_id']) ?>" class="hero-poster-card">
                        <?php if (!empty($movie['poster_url'])): ?>
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <?php else: ?>
                            <div class="hero-poster-placeholder">
                                <?= htmlspecialchars(substr($movie['title'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="hero-poster-overlay">
                            <span class="hero-poster-rating"><?= htmlspecialchars($movie['avg_rating']) ?></span>
                            <div class="hero-poster-pills">
                                <?php if (!empty($movie['top_response'])): ?>
                                    <span class="hero-pill"><?= htmlspecialchars($movie['top_response']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($movie['top_audience'])): ?>
                                    <span class="hero-pill"><?= htmlspecialchars($movie['top_audience']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($movie['top_warning'])): ?>
                                    <span class="hero-pill hero-pill-warning"><?= htmlspecialchars($movie['top_warning']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="hero-no-movies">No movies yet. <a href="/signup">Be the first to add one.</a></p>
            <?php endif; ?>
        </section>
    </main>

    <section class="trending">
        <div class="section-header">
            <h2>Trending This Week</h2>
            <a href="/browse">View all ›</a>
        </div>

        <?php if (!empty($trending_movies)): ?>
            <div class="movie-row">
                <?php foreach ($trending_movies as $movie): ?>
                    <a href="<?= movie_url($movie['tmdb_id']) ?>" class="trending-card">
                        <?php if (!empty($movie['poster_url'])): ?>
                            <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                        <?php else: ?>
                            <div class="trending-placeholder">
                                <?= htmlspecialchars(substr($movie['title'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: #918aa4;">No trending movies this week yet.</p>
        <?php endif; ?>
    </section>

<?php endif; ?>