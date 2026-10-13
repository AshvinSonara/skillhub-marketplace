<?php
require_once __DIR__ . '/../includes/functions.php';

$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT g.gig_id, g.title, g.image, c.category_name, u.name AS freelancer_name,
               (SELECT MIN(price) FROM gig_packages WHERE gig_id = g.gig_id) AS starting_price,
               (SELECT ROUND(AVG(r.rating),1) FROM reviews r JOIN orders o ON r.order_id = o.order_id WHERE o.gig_id = g.gig_id) AS avg_rating,
               (SELECT COUNT(*) FROM reviews r JOIN orders o ON r.order_id = o.order_id WHERE o.gig_id = g.gig_id) AS review_count
        FROM gigs g
        JOIN users u ON g.freelancer_id = u.user_id
        JOIN categories c ON g.category_id = c.category_id
        WHERE g.status = 'active'";
$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND g.title LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}
if ($categoryId > 0) {
    $sql .= " AND g.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}

if ($sort === 'price_low') {
    $sql .= " ORDER BY starting_price ASC";
} elseif ($sort === 'price_high') {
    $sql .= " ORDER BY starting_price DESC";
} else {
    $sql .= " ORDER BY g.created_at DESC";
}

$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$gigs = $stmt->get_result();

$pageTitle = "Browse Gigs";
include __DIR__ . '/../includes/header.php';
?>

<h1>Browse Gigs</h1>

<form class="basic-form" method="GET" action="client/browse_gigs.php" style="margin-bottom:22px;">
    <div class="form-row">
        <div class="form-group">
            <label for="q">Search</label>
            <input type="text" id="q" name="q" value="<?php echo clean($search); ?>" placeholder="e.g. website, logo, article">
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="0">All Categories</option>
                <?php
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $categoryId == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo clean($cat['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sort">Sort By</label>
            <select id="sort" name="sort">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn">Apply Filters</button>
</form>

<?php if ($gigs->num_rows > 0): ?>
    <div class="gig-grid">
        <?php while ($gig = $gigs->fetch_assoc()): ?>
            <div class="gig-card">
                <a href="client/gig_detail.php?id=<?php echo $gig['gig_id']; ?>">
                    <?php if (!empty($gig['image'])): ?>
                        <div class="gig-thumb" style="padding:0;">
                            <img src="uploads/<?php echo clean($gig['image']); ?>" alt="Gig image">
                        </div>
                    <?php else: ?>
                        <div class="gig-thumb" style="background:#<?php echo categoryColor($gig['category_name']); ?>;">
                            <?php echo clean($gig['category_name']); ?>
                        </div>
                    <?php endif; ?>
                </a>
                <div class="gig-card-body">
                    <h4><a href="client/gig_detail.php?id=<?php echo $gig['gig_id']; ?>"><?php echo clean($gig['title']); ?></a></h4>
                    <div class="gig-seller">by <?php echo clean($gig['freelancer_name']); ?></div>
                    <div class="gig-rating">
                        <?php echo starRating($gig['avg_rating'] ?? 0); ?>
                        <span class="count">(<?php echo (int)($gig['review_count'] ?? 0); ?>)</span>
                    </div>
                    <div class="gig-price"><span>Starting at</span><br><?php echo formatMoney($gig['starting_price']); ?></div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">No gigs matched your search. Try a different keyword or category.</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
