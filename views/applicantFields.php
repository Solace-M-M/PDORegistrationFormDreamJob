<?php
// The form fields shared by the "add" form and the "edit" form.
// Expects: $values (current/old values) and $errors (field => message)
$field = fn(string $name, $default = '') => $values[$name] ?? $default;
$error = fn(string $name) => isset($errors[$name])
    ? '<small class="error">' . e($errors[$name]) . '</small>' : '';
?>
<div class="grid">
    <label>First name *
        <input type="text" name="first_name" maxlength="50" required value="<?= e($field('first_name')) ?>">
        <?= $error('first_name') ?>
    </label>
    <label>Last name *
        <input type="text" name="last_name" maxlength="50" required value="<?= e($field('last_name')) ?>">
        <?= $error('last_name') ?>
    </label>
    <label>Email *
        <input type="email" name="email" maxlength="100" required value="<?= e($field('email')) ?>">
        <?= $error('email') ?>
    </label>
    <label>Phone number
        <input type="text" name="phone_number" maxlength="20" placeholder="09171234567" value="<?= e($field('phone_number')) ?>">
        <?= $error('phone_number') ?>
    </label>
    <label>Specialization *
        <select name="specialization" required>
            <option value="">-- choose --</option>
            <?php foreach (SPECIALIZATIONS as $option): ?>
                <option <?= $field('specialization') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $error('specialization') ?>
    </label>
    <label>Main game engine *
        <select name="game_engine" required>
            <option value="">-- choose --</option>
            <?php foreach (GAME_ENGINES as $option): ?>
                <option <?= $field('game_engine') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $error('game_engine') ?>
    </label>
    <label>Programming languages *
        <input type="text" name="programming_languages" maxlength="255" required placeholder="C#, C++" value="<?= e($field('programming_languages')) ?>">
        <?= $error('programming_languages') ?>
    </label>
    <label>Years of experience *
        <input type="number" name="years_experience" min="0" max="50" required value="<?= e($field('years_experience', 0)) ?>">
        <?= $error('years_experience') ?>
    </label>
    <label>Portfolio / GitHub link
        <input type="url" name="portfolio_url" maxlength="255" placeholder="https://github.com/username" value="<?= e($field('portfolio_url')) ?>">
        <?= $error('portfolio_url') ?>
    </label>
    <label>Expected monthly salary (₱) *
        <input type="number" name="expected_salary" min="0" step="0.01" required value="<?= e($field('expected_salary', '0')) ?>">
        <?= $error('expected_salary') ?>
    </label>
    <label>Application status
        <select name="application_status">
            <?php foreach (APPLICATION_STATUSES as $option): ?>
                <option <?= $field('application_status', 'Pending') === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $error('application_status') ?>
    </label>
</div>
