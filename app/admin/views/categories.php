<main class="admin-page">
    <div class="admin-header">
        <div>
            <a href="/admin" class="return-link">← Back to Dashboard</a>
            <h1>Categories</h1>
        </div>
    </div>

    <div class="category-grid">
        <?php
        $category_sets = [
            ['label' => 'Reaction', 'table' => 'response', 'items' => $responses],
            ['label' => 'Audience', 'table' => 'audience', 'items' => $audiences],
            ['label' => 'Contains', 'table' => 'warning',  'items' => $warnings],
        ];
        foreach ($category_sets as $cat):
            $is_editing = $edit_type === $cat['table'] && $edit_item;
            $is_adding  = $add_type === $cat['table'];
        ?>
        <div class="category-card">
            <div class="category-card-header">
                <h3><?= $cat['label'] ?></h3>
                <a href="/admin/categories?add=<?= $cat['table'] ?>" class="category-add-btn">+ Add</a>
            </div>

            <?php if ($is_adding): ?>
                <form method="POST" action="/admin/categorycreate" class="category-inline-form">
                    <input type="hidden" name="table" value="<?= $cat['table'] ?>">
                    <input type="text" name="name" class="category-inline-input" placeholder="New <?= strtolower($cat['label']) ?>..." autofocus required>
                    <div class="category-inline-actions">
                        <button type="submit" class="category-save-btn">Save</button>
                        <a href="/admin/categories" class="category-cancel-btn">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>

            <form method="GET" action="/admin/categories" class="category-select-form">
                <input type="hidden" name="edit" value="<?= $cat['table'] ?>">
                <select name="id" class="filter-select category-select" onchange="this.form.submit()">
                    <option value="">— Select to edit or delete —</option>
                    <?php foreach ($cat['items'] as $item): ?>
                        <option value="<?= $item['id'] ?>" <?= ($is_editing && $edit_id === (int)$item['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($item['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($is_editing): ?>
                <form method="POST" action="/admin/categoryupdate/<?= $edit_id ?>" class="category-inline-form">
                    <input type="hidden" name="table" value="<?= $cat['table'] ?>">
                    <input type="text" name="name" class="category-inline-input" value="<?= htmlspecialchars($edit_item['name']) ?>" required>
                    <div class="category-inline-actions">
                        <button type="submit" class="category-save-btn">Save</button>
                        <a href="/admin/categories" class="category-cancel-btn">Cancel</a>
                    </div>
                </form>
                <form method="POST" action="/admin/categorydelete/<?= $edit_id ?>"
                    class="category-delete-form"
                    onsubmit="return confirm('Delete <?= htmlspecialchars($edit_item['name']) ?>? This cannot be undone.')">
                    <input type="hidden" name="table" value="<?= $cat['table'] ?>">
                    <button type="submit" class="category-delete-btn">Delete this option</button>
                </form>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>
</main>