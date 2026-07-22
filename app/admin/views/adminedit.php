<main class="movie-create-page">

    <a href="#" class="return-link" onclick="history.back(); return false;">← Return</a>

    <div class="movie-create-layout">

        <aside class="movie-create-sidebar">
            <?php if (!empty($movie['poster'])): ?>
                <img
                    src="<?= htmlspecialchars($movie['poster']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?> poster"
                    class="create-poster"
                >
            <?php else: ?>
                <div class="create-poster-placeholder">🎬</div>
            <?php endif; ?>
        </aside>

        <section class="movie-create-form-area">

            <p class="eyebrow">Admin</p>
            <h1>Edit movie info</h1>

            <form action="/movie/adminupdate/<?= movie_id_for_url($movie['tmdb_id']) ?>" method="POST" class="movie-create-form">

                <!-- Title -->
                <div class="form-section">
                    <label class="form-label">Title <span class="form-hint">required</span></label>
                    <input
                        type="text"
                        name="title"
                        class="form-input"
                        value="<?= htmlspecialchars($movie['title']) ?>"
                        required
                    >
                </div>

                <!-- Year -->
                <div class="form-section">
                    <label class="form-label">Release year <span class="form-hint">optional</span></label>
                    <input
                        type="number"
                        name="year"
                        class="form-input"
                        placeholder="e.g. 1987"
                        min="1888"
                        max="2099"
                        value="<?= htmlspecialchars($movie['year'] ?? '') ?>"
                    >
                </div>

                <!-- Director -->
                <div class="form-section">
                    <label class="form-label">Director <span class="form-hint">optional</span></label>
                    <input
                        type="text"
                        name="director"
                        class="form-input"
                        placeholder="e.g. Stanley Kubrick"
                        value="<?= htmlspecialchars($movie['director'] ?? '') ?>"
                    >
                </div>

                <!-- Runtime -->
                <div class="form-section">
                    <label class="form-label">Runtime <span class="form-hint">optional, in minutes</span></label>
                    <input
                        type="number"
                        name="runtime"
                        class="form-input"
                        placeholder="e.g. 142"
                        min="1"
                        value="<?= htmlspecialchars($movie['runtime'] ?? '') ?>"
                    >
                </div>

                <!-- Description -->
                <div class="form-section">
                    <label class="form-label">Description <span class="form-hint">optional</span></label>
                    <textarea
                        name="description"
                        rows="4"
                        class="form-textarea"
                    ><?= htmlspecialchars($movie['description'] ?? '') ?></textarea>
                </div>

                <!-- Poster URL -->
                <div class="form-section">
                    <label class="form-label">Poster URL <span class="form-hint">optional</span></label>
                    <input
                        type="url"
                        name="poster"
                        class="form-input"
                        placeholder="https://..."
                        value="<?= htmlspecialchars($movie['poster'] ?? '') ?>"
                    >
                </div>

                <?php if ($movie['tmdb_id'] > 0): ?>
                    <div class="form-section">
                        <a href="/movie/adminedit/<?= movie_id_for_url($movie['tmdb_id']) ?>?tmdb=1" class="autocomplete-btn">
                            🔄 Autocomplete from TMDB
                        </a>
                    </div>
                <?php endif; ?>

                <button type="submit" class="submit-entry-btn">Save changes</button>

            </form>

        </section>

    </div>

</main>