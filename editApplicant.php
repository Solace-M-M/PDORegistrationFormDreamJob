<?php
// editApplicant.php - form pre-filled with one application (UPDATE)
require_once 'core/dbConfig.php';
require_once 'core/models.php';

$id = (int) ($_GET['id'] ?? 0);
$applicant = getApplicantById($pdo, $id);   // READ the record to edit

if (!$applicant) {
    setFlash('error', 'Application not found.');
    redirect('index.php');
}

// After a failed update show what the user typed; otherwise show the saved values
$values = $_SESSION['old'] ?? $applicant;
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Edit application #' . $id;
require 'views/header.php';
?>

<section class="card">
    <h2>Edit application #<?= $id ?> &mdash; <?= e($applicant['first_name'] . ' ' . $applicant['last_name']) ?></h2>
    <p class="muted">Added <?= e(date('M j, Y g:i A', strtotime($applicant['date_added']))) ?></p>
    <?php if ($errors): ?>
        <div class="flash error">Please fix the highlighted fields.</div>
    <?php endif; ?>

    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="applicant_id" value="<?= $id ?>">
        <?php require 'views/applicantFields.php'; ?>
        <button type="submit" class="btn primary">Save changes</button>
        <a href="index.php" class="btn ghost">Cancel</a>
    </form>
</section>

<?php require 'views/footer.php'; ?>
