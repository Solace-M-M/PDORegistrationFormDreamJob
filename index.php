<?php
// index.php - the application form (CREATE) and the table of all applications (READ)
require_once 'core/dbConfig.php';
require_once 'core/models.php';

// Filters from the search bar (?q=unity&status=Pending)
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
if (!in_array($status, APPLICATION_STATUSES, true)) {
    $status = '';   // ignore unknown values
}

$applicants = getApplicants($pdo, $search, $status);   // READ rows for the table
$counts     = getStatusCounts($pdo);                    // READ totals for the badges

// Values/errors left by a failed submission (so the form keeps what was typed)
$values = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);

$pageTitle = 'Game Developer Applications';
require 'views/header.php';
?>

<section class="card">
    <h2>Apply as a Game Developer</h2>
    <p class="muted">Fields marked * are required.</p>
    <?php if ($errors): ?>
        <div class="flash error">Please fix the highlighted fields.</div>
    <?php endif; ?>

    <form action="core/handleForms.php" method="post" novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="add">
        <?php require 'views/applicantFields.php'; ?>
        <button type="submit" class="btn primary">Submit application</button>
    </form>
</section>

<section class="card">
    <h2>Submitted applications (<?= count($applicants) ?>)</h2>

    <div class="badges">
        <?php foreach ($counts as $label => $total): ?>
            <a class="badge <?= strtolower($label) ?>" href="?status=<?= urlencode($label) ?>"><?= e($label) ?>: <?= $total ?></a>
        <?php endforeach; ?>
    </div>

    <form method="get" class="filters">
        <input type="search" name="q" placeholder="Search name, email, language, role or engine" value="<?= e($search) ?>">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach (APPLICATION_STATUSES as $option): ?>
                <option <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Filter</button>
        <a href="index.php" class="btn ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Specialization</th>
                    <th>Engine</th><th>Languages</th><th>Exp. (yrs)</th><th>Portfolio</th>
                    <th>Expected salary</th><th>Status</th><th>Date added</th><th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$applicants): ?>
                <tr><td colspan="13" class="empty">No applications found.</td></tr>
            <?php endif; ?>

            <?php foreach ($applicants as $a): ?>
                <tr>
                    <td><?= (int) $a['applicant_id'] ?></td>
                    <td><?= e($a['first_name'] . ' ' . $a['last_name']) ?></td>
                    <td><?= e($a['email']) ?></td>
                    <td><?= e($a['phone_number'] ?? '—') ?></td>
                    <td><?= e($a['specialization']) ?></td>
                    <td><?= e($a['game_engine']) ?></td>
                    <td><?= e($a['programming_languages']) ?></td>
                    <td class="num"><?= (int) $a['years_experience'] ?></td>
                    <td>
                        <?php if ($a['portfolio_url']): ?>
                            <a href="<?= e($a['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer">View</a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td class="num">₱<?= number_format((float) $a['expected_salary'], 2) ?></td>
                    <td><span class="badge <?= strtolower(e($a['application_status'])) ?>"><?= e($a['application_status']) ?></span></td>
                    <td class="date"><?= e(date('M j, Y', strtotime($a['date_added']))) ?><small><?= e(date('g:i A', strtotime($a['date_added']))) ?></small></td>
                    <td class="actions">
                        <a class="btn small" href="editApplicant.php?id=<?= (int) $a['applicant_id'] ?>">Edit</a>
                        <a class="btn small danger" href="deleteApplicant.php?id=<?= (int) $a['applicant_id'] ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'views/footer.php'; ?>
