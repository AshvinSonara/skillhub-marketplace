<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('admin');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $name = clean($_POST['category_name']);
    if ($name === '') {
        $errors[] = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        redirectWithMessage('admin/manage_categories.php', 'Category added.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int)$_POST['category_id'];
    $name = clean($_POST['category_name']);
    if ($name !== '') {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->close();
        redirectWithMessage('admin/manage_categories.php', 'Category updated.');
    }
}

$categories = $conn->query("
    SELECT category_id, category_name,
           (SELECT COUNT(*) FROM gigs WHERE gigs.category_id = categories.category_id) AS gig_count
    FROM categories ORDER BY category_name
");

$pageTitle = "Manage Categories";
include __DIR__ . '/../includes/header.php';
?>

<h1>Manage Categories</h1>

<?php if (!empty($errors)): ?>
    <div class="error-message">
        <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
    </div>
<?php endif; ?>

<form class="basic-form" method="POST" action="admin/manage_categories.php" style="max-width:420px;">
    <input type="hidden" name="action" value="add">
    <div class="form-group">
        <label for="category_name">New Category Name</label>
        <input type="text" id="category_name" name="category_name" required>
    </div>
    <button type="submit" class="btn">Add Category</button>
</form>

<div class="table-wrap">
<table class="basic-table mt-25">
    <tr><th>Category</th><th>Gigs</th><th>Actions</th></tr>
    <?php while ($cat = $categories->fetch_assoc()): ?>
        <tr>
            <td>
                <form method="POST" action="admin/manage_categories.php" style="display:flex; gap:6px;">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                    <input type="text" name="category_name" value="<?php echo clean($cat['category_name']); ?>" style="padding:6px;">
                    <button type="submit" class="btn btn-small">Save</button>
                </form>
            </td>
            <td><?php echo $cat['gig_count']; ?></td>
            <td>
                <?php if ($cat['gig_count'] == 0): ?>
                    <a href="admin/delete_category.php?id=<?php echo $cat['category_id']; ?>" class="btn btn-small btn-danger confirm-action" data-confirm="Delete this category?">Delete</a>
                <?php else: ?>
                    <span class="small-text">In use</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
