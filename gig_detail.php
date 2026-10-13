<?php
require_once __DIR__ . '/../includes/functions.php';

$gigId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("
    SELECT g.gig_id, g.title, g.description, g.image, g.freelancer_id, c.category_name,
           u.name AS freelancer_name, u.bio AS freelancer_bio, u.profile_pic
    FROM gigs g
    JOIN users u ON g.freelancer_id = u.user_id
    JOIN categories c ON g.category_id = c.category_id
    WHERE g.gig_id = ? AND g.status = 'active'
");
$stmt->bind_param("i", $gigId);
$stmt->execute();
$gig = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$gig) {
    redirectWithMessage('client/browse_gigs.php', 'That gig could not be found.', 'error');
}

$pkgStmt = $conn->prepare("SELECT * FROM gig_packages WHERE gig_id = ? ORDER BY FIELD(package_type,'basic','standard','premium')");
$pkgStmt->bind_param("i", $gigId);
$pkgStmt->execute();
$packages = $pkgStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviewStmt = $conn->prepare("
    SELECT r.rating, r.comment, r.created_at, cl.name AS client_name
    FROM reviews r
    JOIN orders o ON r.order_id = o.order_id
    JOIN users cl ON r.client_id = cl.user_id
    WHERE o.gig_id = ? ORDER BY r.created_at DESC
");
$reviewStmt->bind_param("i", $gigId);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();

$avgStmt = $conn->prepare("SELECT ROUND(AVG(r.rating),1) AS avg_r, COUNT(*) AS cnt FROM reviews r JOIN orders o ON r.order_id = o.order_id WHERE o.gig_id = ?");
$avgStmt->bind_param("i", $gigId);
$avgStmt->execute();
$avgRow = $avgStmt->get_result()->fetch_assoc();

$pageTitle = $gig['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="two-col">
    <div class="col-main">
        <?php if (!empty($gig['image'])): ?>
            <img src="uploads/<?php echo clean($gig['image']); ?>" alt="Gig image" style="border-radius:6px; border:1px solid var(--border); margin-bottom:18px;">
        <?php else: ?>
            <div class="gig-thumb" style="height:220px; border-radius:6px; font-size:20px; background:#<?php echo categoryColor($gig['category_name']); ?>; margin-bottom:18px;">
                <?php echo clean($gig['category_name']); ?>
            </div>
        <?php endif; ?>

        <span class="badge-inline"><?php echo clean($gig['category_name']); ?></span>
        <h1 class="mt-15"><?php echo clean($gig['title']); ?></h1>

        <div class="gig-rating mt-15">
            <?php echo starRating($avgRow['avg_r'] ?? 0); ?>
            <span class="count"><?php echo $avgRow['avg_r'] ? $avgRow['avg_r'] . ' (' . $avgRow['cnt'] . ' reviews)' : 'No reviews yet'; ?></span>
        </div>

        <h3 class="mt-25">About This Gig</h3>
        <p><?php echo nl2br(clean($gig['description'])); ?></p>

        <h3 class="mt-25">About the Freelancer</h3>
        <p><strong><?php echo clean($gig['freelancer_name']); ?></strong></p>
        <p class="small-text"><?php echo clean($gig['freelancer_bio'] ?: 'No bio provided.'); ?></p>

        <h3 class="mt-25">Reviews</h3>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($r = $reviews->fetch_assoc()): ?>
                <div class="review-item">
                    <div class="review-stars"><?php echo starRating($r['rating']); ?></div>
                    <p style="margin:6px 0;"><?php echo clean($r['comment']); ?></p>
                    <div class="small-text"><?php echo clean($r['client_name']); ?> &middot; <?php echo date('d M Y', strtotime($r['created_at'])); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="small-text">No reviews yet for this gig.</p>
        <?php endif; ?>
    </div>

    <div class="col-side">
        <div class="basic-form">
            <h3>Choose a Package</h3>
            <form method="POST" action="client/order.php">
                <input type="hidden" name="gig_id" value="<?php echo $gig['gig_id']; ?>">
                <input type="hidden" name="package_id" id="selected_package_id" value="">

                <?php $first = true; foreach ($packages as $pkg): ?>
                    <div class="package-tab <?php echo $first ? 'selected' : ''; ?>"
                         data-package-id="<?php echo $pkg['package_id']; ?>"
                         data-price-display="<?php echo formatMoney($pkg['price']); ?>">
                        <div class="ptype"><?php echo clean($pkg['package_type']); ?></div>
                        <div class="pprice"><?php echo formatMoney($pkg['price']); ?></div>
                        <div class="pdays"><?php echo (int)$pkg['delivery_days']; ?> day delivery</div>
                        <div class="small-text mt-15"><?php echo clean($pkg['description']); ?></div>
                    </div>
                    <?php if ($first): ?>
                        <script>document.getElementById('selected_package_id').value = "<?php echo $pkg['package_id']; ?>";</script>
                    <?php endif; ?>
                    <?php $first = false; endforeach; ?>

                <p class="mt-15">Total: <strong id="order-total-price"><?php echo formatMoney(!empty($packages) ? $packages[0]['price'] : 0); ?></strong></p>

                <?php if (!isLoggedIn()): ?>
                    <a href="auth/login.php" class="btn btn-block">Login to Order</a>
                <?php elseif (currentRole() !== 'client'): ?>
                    <p class="small-text">Only client accounts can place orders.</p>
                <?php elseif ($gig['freelancer_id'] == currentUserId()): ?>
                    <p class="small-text">You cannot order your own gig.</p>
                <?php else: ?>
                    <button type="submit" class="btn btn-block">Continue to Order</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
