<main class="catalogue-page">

    <div class="catalogue-header">
        <div class="catalogue-header-row">
            <h1>Movie catalogue</h1>
            <p class="page-intro">Every film Gloop users have journaled.</p>
        </div>
    </div>

    <div class="catalogue-layout">

        <aside class="catalogue-filters">
            <form method="GET" action="/browse" id="filter-form">

                <div class="filter-group">
                    <label class="filter-label">Reaction</label>
                    <select name="response" class="filter-select" onchange="this.form.submit()">
                        <option value="">All reactions</option>
                        <?php foreach ($responses as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $filter_response === (int)$r['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Target audience</label>
                    <select name="audience" class="filter-select" onchange="this.form.submit()">
                        <option value="">All audiences</option>
                        <?php foreach ($audiences as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $filter_audience === (int)$a['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Content warning</label>
                    <select name="warning" class="filter-select" onchange="this.form.submit()">
                        <option value="">All warnings</option>
                        <?php foreach ($warnings as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= $filter_warning === (int)$w['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($w['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Rating</label>
                    <div class="filter-range">
                        <input
                            type="number"
                            name="rating_from"
                            class="filter-range-input"
                            placeholder="From"
                            min="0"
                            max="10"
                            step="0.5"
                            value="<?= htmlspecialchars($filter_rating_from ?? '') ?>"
                        >
                        <span class="filter-range-sep">–</span>
                        <input
                            type="number"
                            name="rating_to"
                            class="filter-range-input"
                            placeholder="To"
                            min="0"
                            max="10"
                            step="0.5"
                            value="<?= htmlspecialchars($filter_rating_to ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Year</label>
                    <div class="filter-range">
                        <input
                            type="number"
                            name="year_from"
                            class="filter-range-input"
                            placeholder="From"
                            min="1888"
                            max="2099"
                            value="<?= htmlspecialchars($filter_year_from ?? '') ?>"
                        >
                        <span class="filter-range-sep">–</span>
                        <input
                            type="number"
                            name="year_to"
                            class="filter-range-input"
                            placeholder="To"
                            min="1888"
                            max="2099"
                            value="<?= htmlspecialchars($filter_year_to ?? '') ?>"
                        >
                    </div>
                </div>

                <button type="submit" class="filter-apply-btn">Apply filters</button>

                <?php if ($filter_response || $filter_audience || $filter_warning || $filter_year_from || $filter_year_to || $filter_rating_from || $filter_rating_to): ?>
                    <a href="/browse" class="filter-clear">Clear all filters</a>
                <?php endif; ?>

            </form>
        </aside>

        <section class="catalogue-results">

            <p class="catalogue-count">
                <?= $total ?> <?= $total === 1 ? 'film' : 'films' ?>
            </p>

            <?php if (empty($movies)): ?>
                <div class="empty-catalogue">
                    <h3>No films found</h3>
                    <p>Try adjusting or clearing your filters.</p>
                </div>
            <?php else: ?>
                <div class="catalogue-grid">
                    <?php foreach ($movies as $movie): ?>
                        <article class="catalogue-card">
                            <a href="<?= movie_url($movie['tmdb_id']) ?>" class="catalogue-card-link">
                                <div class="catalogue-poster">
                                    <?php if (!empty($movie['poster_url'])): ?>
                                        <img
                                            src="<?= htmlspecialchars($movie['poster_url']) ?>"
                                            alt="<?= htmlspecialchars($movie['title']) ?> poster"
                                        >
                                    <?php else: ?>
                                        <div class="poster-placeholder">
                                            <?= htmlspecialchars(substr($movie['title'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($movie['avg_rating'])): ?>
                                        <span class="movie-rating">
                                            <?= htmlspecialchars($movie['avg_rating']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="catalogue-info">
                                    <h3><?= htmlspecialchars($movie['title']) ?></h3>
                                    <?php if (!empty($movie['year'])): ?>
                                        <p class="catalogue-year"><?= htmlspecialchars($movie['year']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <nav class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="/browse?<?= http_build_query(array_merge($query_params, ['page' => $page - 1])) ?>" class="pagination-btn">← Previous</a>
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
                                        href="/browse?<?= http_build_query(array_merge($query_params, ['page' => $i])) ?>"
                                        class="pagination-page <?= $i === $page ? 'active' : '' ?>"
                                    ><?= $i ?></a>
                                <?php elseif (abs($i - $page) === 3): ?>
                                    <a class="pagination-ellipsis">…</a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="/browse?<?= http_build_query(array_merge($query_params, ['page' => $page + 1])) ?>" class="pagination-btn">Next →</a>
                        <?php else: ?>
                            <a class="pagination-btn disabled">Next →</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>

        </section>

    </div>

</main>