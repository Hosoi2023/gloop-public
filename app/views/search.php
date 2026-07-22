<main class="movies-page">

    <div class="movies-header">
        <h1>
            <?= $query !== ''
                ? 'Results for "' . htmlspecialchars($query) . '"'
                : 'Search Movies' ?>
        </h1>
    </div>

    <?php if ($query === ''): ?>
        <p style="color:#aeb4c0; font-size:20px;">Type something in the search bar to find movies.</p>

    <?php elseif (empty($movies)): ?>
        <p style="color:#aeb4c0; font-size:20px;">No results found for "<?= htmlspecialchars($query) ?>".</p>

    <?php else: ?>
        <section class="movie-grid">
            <?php foreach ($movies as $m): ?>
                <div class="movie-tile">
                    <a href="/movie/view/<?= $m['tmdb_id'] ?>">
                        <?php if ($m['poster']): ?>
                            <img src="<?= htmlspecialchars($m['poster']) ?>" alt="<?= htmlspecialchars($m['title']) ?>">
                        <?php else: ?>
                            <div class="no-poster">No poster</div>
                        <?php endif; ?>
                        <div class="movie-overlay">
                            <h2><?= htmlspecialchars($m['title']) ?></h2>
                            <p><?= htmlspecialchars($m['year']) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

</main>