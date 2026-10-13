<?php
require_once __DIR__ . '/includes/functions.php';

// latest active gigs for the homepage
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

$recentGigs = $conn->query("
    SELECT g.gig_id, g.title, g.image, c.category_name, u.name AS freelancer_name,
           (SELECT MIN(price) FROM gig_packages WHERE gig_id = g.gig_id) AS starting_price,
           (SELECT ROUND(AVG(r.rating),1) FROM reviews r JOIN orders o ON r.order_id = o.order_id WHERE o.gig_id = g.gig_id) AS avg_rating,
           (SELECT COUNT(*) FROM reviews r JOIN orders o ON r.order_id = o.order_id WHERE o.gig_id = g.gig_id) AS review_count
    FROM gigs g
    JOIN users u ON g.freelancer_id = u.user_id
    JOIN categories c ON g.category_id = c.category_id
    WHERE g.status = 'active'
    ORDER BY g.created_at DESC
    LIMIT 8
");

$pageTitle = "Home";
include __DIR__ . '/includes/header.php';
?>

<div class="hero" style="border-radius:8px;">
    <h1>Find the right freelancer for your project</h1>
    <p>Web development, design, writing and marketing services — posted by real freelancers, at simple fixed prices.</p>
    <form class="hero-search" action="client/browse_gigs.php" method="GET">
        <input type="text" name="q" placeholder="Search for a service (e.g. logo design, website)">
        <button type="submit" class="btn" style="background:#17272a;">Search</button>
    </form>
</div>

<div class="category-strip">
    <a href="client/browse_gigs.php" class="category-pill">All Categories</a>
    <?php while ($cat = $categories->fetch_assoc()): ?>
        <a href="client/browse_gigs.php?category=<?php echo (int)$cat['category_id']; ?>" class="category-pill">
            <?php echo clean($cat['category_name']); ?>
        </a>
    <?php endwhile; ?>
</div>

<h2 class="mt-25">Recent Gigs</h2>

<?php if ($recentGigs->num_rows > 0): ?>
    <div class="gig-grid">
        <?php while ($gig = $recentGigs->fetch_assoc()): ?>
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
    <div class="empty-state">No gigs posted yet. Be the first freelancer to add one!</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
