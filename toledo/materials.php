<?php
require_once 'header.php';

$message = $_GET['msg'] ?? '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $name     = mysqli_real_escape_string($conn, $_POST['material_name']);
    $supplier = mysqli_real_escape_string($conn, $_POST['supplier']);
    $price    = (float)$_POST['price'];

    if ($action === 'add_material') {
        mysqli_query(
            $conn,
            "INSERT INTO materials (material_name, supplier, price) VALUES ('$name', '$supplier', $price)"
        );
        header('Location: materials.php?msg=Material+added');
        exit;
    }

    if ($action === 'edit_material') {
        $id = (int)$_POST['material_id'];
        mysqli_query(
            $conn,
            "UPDATE materials SET material_name='$name', supplier='$supplier', price=$price WHERE material_id=$id"
        );
        header('Location: materials.php?msg=Material+updated');
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Prevent deletion if material is in use
    $check = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM product_materials WHERE material_id=$id"
    );
    $row = mysqli_fetch_assoc($check);

    if ($row['total'] > 0) {
        header('Location: materials.php?msg=Material+is+in+use');
        exit;
    }

    mysqli_query($conn, "DELETE FROM materials WHERE material_id=$id");
    header('Location: materials.php?msg=Material+deleted');
    exit;
}

// Fetch materials
$materials = mysqli_query($conn, "SELECT * FROM materials ORDER BY material_name ASC");

// Check if editing
$editMaterial = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editMaterial = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT * FROM materials WHERE material_id=$editId")
    );
}
?>

<h1>Materials</h1>

<?php if ($message): ?>
    <div class="success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- SINGLE FORM FOR ADD / EDIT -->
<h3><?= $editMaterial ? 'Edit Material' : 'Add Material' ?></h3>
<form method="post">
    <input type="hidden" name="action" value="<?= $editMaterial ? 'edit_material' : 'add_material' ?>">
    <?php if ($editMaterial): ?>
        <input type="hidden" name="material_id" value="<?= $editMaterial['material_id'] ?>">
    <?php endif; ?>

    <label>Material Name</label>
    <input type="text" name="material_name" required
           value="<?= htmlspecialchars($editMaterial['material_name'] ?? '') ?>">

    <label>Supplier</label>
    <input type="text" name="supplier" required
           value="<?= htmlspecialchars($editMaterial['supplier'] ?? '') ?>">

    <label>Price</label>
    <input type="number" step="0.01" name="price" required
           value="<?= $editMaterial['price'] ?? '' ?>">

    <button type="submit"><?= $editMaterial ? 'Update Material' : 'Save Material' ?></button>
    <?php if ($editMaterial): ?>
        <a href="materials.php">Cancel</a>
    <?php endif; ?>
</form>

<hr>

<!-- MATERIAL LIST -->
<h3>Material List</h3>
<table>
    <tr>
        <th>Material</th>
        <th>Supplier</th>
        <th>Price</th>
        <th>Created</th>
        <th>Action</th>
    </tr>
    <?php while ($m = mysqli_fetch_assoc($materials)): ?>
        <tr>
            <td><?= htmlspecialchars($m['material_name']) ?></td>
            <td><?= htmlspecialchars($m['supplier']) ?></td>
            <td>₱<?= number_format($m['price'], 2) ?></td>
            <td><?= $m['created_at'] ?></td>
            <td>
                <a href="?edit=<?= $m['material_id'] ?>">Edit</a> |
                <a href="?delete=<?= $m['material_id'] ?>"
                   onclick="return confirm('Delete this material?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php require_once 'footer.php'; ?>
