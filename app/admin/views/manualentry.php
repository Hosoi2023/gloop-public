<main class="movie-create-page">

    <a href="/admin" class="return-link">← Back to Dashboard</a>

    <div class="movie-create-layout">

        <aside class="movie-create-sidebar">
            <div class="create-poster-placeholder">🎬</div>
        </aside>

        <section class="movie-create-form-area">

            <p class="eyebrow">Admin</p>
            <h1>Manual Entry</h1>

            <form action="/admin/manualsave" method="POST" class="movie-create-form">

                <!-- Title -->
                <div class="form-section">
                    <label class="form-label">Title <span class="form-hint">required</span></label>
                    <input
                        type="text"
                        name="title"
                        class="form-input"
                        placeholder="e.g. My Obscure Film"
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
                    >
                </div>

                <!-- Description -->
                <div class="form-section">
                    <label class="form-label">Description <span class="form-hint">optional</span></label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="A brief synopsis..."
                        class="form-textarea"
                    ></textarea>
                </div>

                <!-- Poster URL -->
                <div class="form-section">
                    <label class="form-label">Poster URL <span class="form-hint">optional</span></label>
                    <input
                        type="url"
                        name="poster"
                        class="form-input"
                        placeholder="https://..."
                    >
                </div>

                <!-- Rating -->
                <div class="form-section">
                    <label class="form-label">Your rating <span class="form-hint">optional, out of 10</span></label>
                    <input
                        type="number"
                        name="rating"
                        min="0"
                        max="10"
                        step="0.1"
                        placeholder="e.g. 8.5"
                        class="form-input"
                    >
                </div>

                <!-- Comment -->
                <div class="form-section">
                    <div class="comment-label-row">
                        <label class="form-label">Your comment <span class="form-hint">optional</span></label>
                        <label class="spoiler-checkbox">
                            <input type="checkbox" name="spoiler" value="1" checked>
                            <span>Contains spoilers</span>
                        </label>
                    </div>
                    <textarea
                        name="comment"
                        rows="4"
                        placeholder="What did you think?"
                        class="form-textarea"
                    ></textarea>
                </div>

                <!-- Reactions -->
                <div class="form-section">
                    <label class="form-label">Reactions <span class="form-hint">pick up to 3</span></label>
                    <div class="pill-grid" id="response-grid">
                        <?php foreach ($responses as $r): ?>
                            <label class="pill">
                                <input type="checkbox" name="responses[]" value="<?= $r['id'] ?>">
                                <span><?= htmlspecialchars($r['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Audience -->
                <div class="form-section">
                    <label class="form-label">Target audience <span class="form-hint">pick up to 3</span></label>
                    <div class="pill-grid" id="audience-grid">
                        <?php foreach ($audiences as $a): ?>
                            <label class="pill">
                                <input type="checkbox" name="audiences[]" value="<?= $a['id'] ?>">
                                <span><?= htmlspecialchars($a['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Warnings -->
                <div class="form-section">
                    <label class="form-label">Content warnings <span class="form-hint">pick up to 3</span></label>
                    <div class="pill-grid" id="warning-grid">
                        <?php foreach ($warnings as $w): ?>
                            <label class="pill">
                                <input type="checkbox" name="warnings[]" value="<?= $w['id'] ?>">
                                <span><?= htmlspecialchars($w['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="submit-entry-btn">Save entry</button>

            </form>

        </section>

    </div>

</main>

<script>
document.querySelectorAll('.pill-grid').forEach(grid => {
    grid.addEventListener('change', () => {
        const checkboxes = grid.querySelectorAll('input[type="checkbox"]');
        const checked = grid.querySelectorAll('input[type="checkbox"]:checked');
        if (checked.length >= 3) {
            checkboxes.forEach(cb => { if (!cb.checked) cb.disabled = true; });
        } else {
            checkboxes.forEach(cb => cb.disabled = false);
        }
    });
});
</script>