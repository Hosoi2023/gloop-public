<main class="movie-create-page">

    <a href="#" class="return-link" onclick="history.back(); return false;">← Return</a>

    <div class="movie-create-layout">

        <aside class="movie-create-sidebar">
            <?php if ($movie['poster']): ?>
                <img
                    src="<?= htmlspecialchars($movie['poster']) ?>"
                    alt="<?= htmlspecialchars($movie['title']) ?> poster"
                    class="create-poster"
                >
            <?php else: ?>
                <div class="create-poster-placeholder">
                    <?= htmlspecialchars(substr($movie['title'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </aside>

        <section class="movie-create-form-area">

            <p class="eyebrow">Edit entry</p>
            <h1><?= htmlspecialchars($movie['title']) ?></h1>

            <div class="create-meta">
                <?php if ($movie['year']): ?>
                    <span><?= htmlspecialchars($movie['year']) ?></span>
                <?php endif; ?>
                <?php if ($movie['director']): ?>
                    <span>Dir. <?= htmlspecialchars($movie['director']) ?></span>
                <?php endif; ?>
                <?php if ($movie['runtime']): ?>
                    <span><?= htmlspecialchars($movie['runtime']) ?>min</span>
                <?php endif; ?>
            </div>

            <form action="/movie/update/<?= htmlspecialchars($movie['tmdb_id']) ?>" method="POST" class="movie-create-form">

                <!-- Rating -->
                <div class="form-section">
                    <label class="form-label">Your rating <span class="form-hint">out of 10</span></label>
                    <input
                        type="number"
                        name="rating"
                        min="0"
                        max="10"
                        step="0.1"
                        placeholder="e.g. 8.5"
                        class="form-input"
                        value="<?= htmlspecialchars($existing_rating ?? '') ?>"
                    >
                </div>

                <!-- Comment -->
                <div class="form-section">
                    <div class="comment-label-row">
                        <label class="form-label">Your comment <span class="form-hint">optional</span></label>
                        <label class="spoiler-checkbox">
                            <input type="checkbox" name="spoiler" value="1" <?= !empty($existing_comment['spoiler']) ? 'checked' : '' ?>>
                            <span>Contains spoilers</span>
                        </label>
                    </div>
                    <textarea
                        name="comment"
                        rows="4"
                        placeholder="What did you think?"
                        class="form-textarea"
                    ><?= htmlspecialchars($existing_comment['body'] ?? '') ?></textarea>
                </div>

                <!-- Reactions -->
                <div class="form-section">
                    <label class="form-label">Reactions <span class="form-hint">pick up to 3</span></label>
                    <div class="pill-grid" id="response-grid">
                        <?php foreach ($responses as $r): ?>
                            <label class="pill">
                                <input type="checkbox" name="responses[]" value="<?= $r['id'] ?>"
                                    <?= in_array($r['id'], $selected_responses) ? 'checked' : '' ?>>
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
                                <input type="checkbox" name="audiences[]" value="<?= $a['id'] ?>"
                                    <?= in_array($a['id'], $selected_audiences) ? 'checked' : '' ?>>
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
                                <input type="checkbox" name="warnings[]" value="<?= $w['id'] ?>"
                                    <?= in_array($w['id'], $selected_warnings) ? 'checked' : '' ?>>
                                <span><?= htmlspecialchars($w['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="submit-entry-btn">Save changes</button>

            </form>

        </section>

    </div>

</main>

<script>
// Enforce max 3 selections per pill group
document.querySelectorAll('.pill-grid').forEach(grid => {
    grid.addEventListener('change', () => {
        const checkboxes = grid.querySelectorAll('input[type="checkbox"]');
        const checked = grid.querySelectorAll('input[type="checkbox"]:checked');
        if (checked.length >= 3) {
            checkboxes.forEach(cb => {
                if (!cb.checked) cb.disabled = true;
            });
        } else {
            checkboxes.forEach(cb => cb.disabled = false);
        }
    });
});

// Disable already-maxed groups on page load
document.querySelectorAll('.pill-grid').forEach(grid => {
    const checkboxes = grid.querySelectorAll('input[type="checkbox"]');
    const checked = grid.querySelectorAll('input[type="checkbox"]:checked');
    if (checked.length >= 3) {
        checkboxes.forEach(cb => {
            if (!cb.checked) cb.disabled = true;
        });
    }
});
</script>