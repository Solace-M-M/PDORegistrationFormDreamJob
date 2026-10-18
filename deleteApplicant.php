<?php
// deleteApplicant.php - confirmation page before a record is removed (DELETE)
require_once 'core/dbConfig.php';
require_once 'core/models.php';

$id = (int) ($_GET['id'] ?? 0);
$applicant = getApplicantById($pdo, $id);   // READ the record so we can show what will be deleted

if (!$applicant) {
    setFlash('error', 'Application not found.');
    redirect('index.php');
}

$pageTitle = 'Delete application #' . $id;
require 'views/header.php';
?>

<section class="card">
    <h2>Delete this application?</h2>
    <p>You are about to permanently delete the application of
       <strong><?= e($applicant['first_name'] . ' ' . $applicant['last_name']) ?></strong>
       (<?= e($applicant['specialization']) ?>, <?= e($applicant['email']) ?>). This cannot be undone.</p>

    <!-- Deleting uses POST (never a plain link) so it cannot be triggered by accident -->
    <form action="core/handleForms.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="applicant_id" value="<?= $id ?>">
        <button type="submit" class="btn danger">Yes, delete</button>
        <a href="index.php" class="btn ghost">Cancel</a>
    </form>
</section>

<?php require 'views/footer.php'; ?>
