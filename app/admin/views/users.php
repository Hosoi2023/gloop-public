<main class="admin-page">
    <div class="admin-header">
        <div>
            <a href="/admin" class="return-link">← Back to Dashboard</a>
            <h1>Users</h1>
        </div>
    </div>

    <div class="admin-panel">
        <div class="panel-header">
            <h2>All users</h2>
            <span><?= count($operators) ?> total</span>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operators as $op): ?>
                    <tr>
                        <td><?= htmlspecialchars($op['username']) ?></td>
                        <td><?= htmlspecialchars($op['email']) ?></td>
                        <td>
                            <span class="status <?= $op['role'] === 'admin' ? 'admin-status' : 'user-status' ?>">
                                <?= htmlspecialchars($op['role']) ?>
                            </span>
                            <?php if (!$op['is_active']): ?>
                                <span class="status banned-status">banned</span>
                            <?php endif; ?>
                            <?php if ($op['is_muted']): ?>
                                <span class="status muted-status">muted</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($op['created_at']))) ?></td>
                        <td>
                            <?php if ((int) $op['id'] === (int) $_SESSION['user_id']): ?>
                                <span style="color: #918aa4; font-size: 0.88rem;">You</span>
                            <?php else: ?>
                                <?php if ($op['role'] === 'admin'): ?>
                                    <a href="/admin/demote/<?= $op['id'] ?>" class="action-link danger">Demote to user</a>
                                <?php else: ?>
                                    <a href="/admin/promote/<?= $op['id'] ?>" class="action-link">Promote to admin</a>
                                <?php endif; ?>
                                &nbsp;·&nbsp;
                                <?php if ($op['is_active']): ?>
                                    <a href="/admin/ban/<?= $op['id'] ?>" class="action-link danger">Ban</a>
                                <?php else: ?>
                                    <a href="/admin/unban/<?= $op['id'] ?>" class="action-link">Unban</a>
                                <?php endif; ?>
                                &nbsp;·&nbsp;
                                <?php if ($op['is_muted']): ?>
                                    <a href="/admin/unmute/<?= $op['id'] ?>" class="action-link">Unmute</a>
                                <?php else: ?>
                                    <a href="/admin/mute/<?= $op['id'] ?>" class="action-link danger">Mute</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>