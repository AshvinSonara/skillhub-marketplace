<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireLogin('freelancer');

$userId = currentUserId();
$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");

$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $editId > 0;

$gig = ['title' => '', 'description' => '', 'category_id' => '', 'image' => ''];
$pkg = [
    'basic' => ['price' => '', 'delivery_days' => '', 'description' => ''],
    'standard' => ['price' => '', 'delivery_days' => '', 'description' => ''],
    'premium' => ['price' => '', 'delivery_days' => '', 'description' => ''],
];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM gigs WHERE gig_id = ? AND freelancer_id = ?");
    $stmt->bind_param("ii", $editId, $userId);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$found) {
        redirectWithMessage('freelancer/my_gigs.php', 'Gig not found.', 'error');
    }
    $gig = $found;

    $pstmt = $conn->prepare("SELECT * FROM gig_packages WHERE gig_id = ?");
    $pstmt->bind_param("i", $editId);
    $pstmt->execute();
    $res = $pstmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $pkg[$row['package_type']] = $row;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $categoryId = (int)$_POST['category_id'];

    if ($title === '' || $description === '' || $categoryId <= 0) {
        $errors[] = "Please fill in the title, description and category.";
    }

    foreach (['basic', 'standard', 'premium'] as $type) {
        if (empty($_POST['price_' . $type]) || empty($_POST['days_' . $type])) {
            $errors[] = "Please fill in price and delivery days for the " . $type . " package.";
        }
    }

    $imageName = $gig['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newName = 'gig_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../uploads/' . $newName)) {
                $imageName = $newName;
            } else {
                $errors[] = "Could not upload the image. Please try again.";
            }
        } else {
            $errors[] = "Gig image must be a JPG, PNG or GIF file.";
        }
    }

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = $conn->prepare("UPDATE gigs SET title=?, description=?, category_id=?, image=? WHERE gig_id=? AND freelancer_id=?");
            $stmt->bind_param("ssisii", $title, $description, $categoryId, $imageName, $editId, $userId);
            $stmt->execute();
            $stmt->close();
            $gigId = $editId;
        } else {
            $stmt = $conn->prepare("INSERT INTO gigs (freelancer_id, category_id, title, description, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $userId, $categoryId, $title, $description, $imageName);
            $stmt->execute();
            $gigId = $stmt->insert_id;
            $stmt->close();
        }

        foreach (['basic', 'standard', 'premium'] as $type) {
            $price = (float)$_POST['price_' . $type];
            $days = (int)$_POST['days_' . $type];
            $pdesc = clean($_POST['desc_' . $type]);

            $check = $conn->prepare("SELECT package_id FROM gig_packages WHERE gig_id = ? AND package_type = ?");
            $check->bind_param("is", $gigId, $type);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            $check->close();

            if ($existing) {
                $up = $conn->prepare("UPDATE gig_packages SET price=?, delivery_days=?, description=? WHERE package_id=?");
                $up->bind_param("disi", $price, $days, $pdesc, $existing['package_id']);
                $up->execute();
                $up->close();
            } else {
                $ins = $conn->prepare("INSERT INTO gig_packages (gig_id, package_type, price, delivery_days, description) VALUES (?, ?, ?, ?, ?)");
                $ins->bind_param("isdis", $gigId, $type, $price, $days, $pdesc);
                $ins->execute();
                $ins->close();
            }
        }

        redirectWithMessage('freelancer/my_gigs.php', $isEdit ? 'Gig updated successfully.' : 'Gig created successfully.');
    }

    // keep entered values on validation error
    $gig['title'] = $title;
    $gig['description'] = $description;
    $gig['category_id'] = $categoryId;
    foreach (['basic', 'standard', 'premium'] as $type) {
        $pkg[$type]['price'] = $_POST['price_' . $type] ?? '';
        $pkg[$type]['delivery_days'] = $_POST['days_' . $type] ?? '';
        $pkg[$type]['description'] = $_POST['desc_' . $type] ?? '';
    }
}

$pageTitle = $isEdit ? "Edit Gig" : "Add New Gig";
include __DIR__ . '/../includes/header.php';
?>

<h1><?php echo $isEdit ? 'Edit Gig' : 'Add New Gig'; ?></h1>

<?php if (!empty($errors)): ?>
    <div class="error-message">
        <?php foreach ($errors as $e) echo '<p>' . clean($e) . '</p>'; ?>
    </div>
<?php endif; ?>

<form class="basic-form" method="POST" action="freelancer/add_gig.php<?php echo $isEdit ? '?id=' . $editId : ''; ?>" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Gig Title</label>
        <input type="text" id="title" name="title" value="<?php echo clean($gig['title']); ?>" placeholder="e.g. I will build a responsive website using HTML CSS and JS" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" required><?php echo clean($gig['description']); ?></textarea>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo $gig['category_id'] == $cat['category_id'] ? 'selected' : ''; ?>>
                        <?php echo clean($cat['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="image">Gig Image <span class="small-text">(optional)</span></label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
    </div>

    <?php if (!empty($gig['image'])): ?>
        <img src="uploads/<?php echo clean($gig['image']); ?>" alt="Current gig image" style="max-width:180px; border-radius:6px; margin-bottom:16px; border:1px solid var(--border);">
    <?php endif; ?>

    <h3 class="mt-25">Pricing Packages</h3>
    <div class="form-row">
        <?php foreach (['basic' => 'Basic', 'standard' => 'Standard', 'premium' => 'Premium'] as $key => $label): ?>
            <div class="form-group" style="border:1px solid var(--border); border-radius:6px; padding:12px; flex:1; min-width:200px;">
                <label><?php echo $label; ?> Package</label>
                <input type="number" step="0.01" name="price_<?php echo $key; ?>" placeholder="Price (INR)" value="<?php echo clean($pkg[$key]['price']); ?>" style="margin-bottom:8px;" required>
                <input type="number" name="days_<?php echo $key; ?>" placeholder="Delivery days" value="<?php echo clean($pkg[$key]['delivery_days']); ?>" style="margin-bottom:8px;" required>
                <input type="text" name="desc_<?php echo $key; ?>" placeholder="Short description" value="<?php echo clean($pkg[$key]['description']); ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <button type="submit" class="btn mt-15"><?php echo $isEdit ? 'Save Changes' : 'Create Gig'; ?></button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
