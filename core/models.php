<?php

// ---------------------------------------------------------------
// models.php - every database function (the CRUD operations) and
//              the validation / helper functions used by the pages
// ---------------------------------------------------------------

// Allowed values for the drop-down lists (also used to validate input)
const SPECIALIZATIONS = [
    'Gameplay Programmer', 'Engine Programmer', 'Graphics Programmer', 'AI Programmer',
    'Network Programmer', 'Tools Programmer', 'UI Programmer', 'Technical Artist', 'Game Designer',
];
const GAME_ENGINES = ['Unity', 'Unreal Engine', 'Godot', 'GameMaker', 'Custom Engine', 'Other'];
const APPLICATION_STATUSES = ['Pending', 'Interview', 'Hired', 'Rejected'];

/* ------------------------- helpers ------------------------- */

// Escape text before printing it in HTML (prevents XSS)
function e($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Redirect to another page and stop the script
function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

// One-time messages shown after an action (stored in the session)
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
function pullFlash(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// CSRF token: proves a form was submitted from our own page
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrfIsValid(?string $token): bool {
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ------------------------ validation ------------------------ */

// Cleans the submitted form data and returns [cleanData, errors]
function validateApplicant(array $input): array {
    $errors = [];
    $data = [
        'first_name'            => trim($input['first_name'] ?? ''),
        'last_name'             => trim($input['last_name'] ?? ''),
        'email'                 => trim($input['email'] ?? ''),
        'phone_number'          => trim($input['phone_number'] ?? ''),
        'specialization'        => $input['specialization'] ?? '',
        'game_engine'           => $input['game_engine'] ?? '',
        'programming_languages' => trim($input['programming_languages'] ?? ''),
        'years_experience'      => trim((string) ($input['years_experience'] ?? '')),
        'portfolio_url'         => trim($input['portfolio_url'] ?? ''),
        'expected_salary'       => trim((string) ($input['expected_salary'] ?? '')),
        'application_status'    => $input['application_status'] ?? 'Pending',
    ];

    // Names: letters, spaces, dots, apostrophes and hyphens only
    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        if ($data[$field] === '' || mb_strlen($data[$field]) > 50
            || !preg_match("/^\p{L}[\p{L} .'-]*$/u", $data[$field])) {
            $errors[$field] = "{$label} is required (letters only, max 50 characters).";
        }
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 100) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if ($data['phone_number'] !== '' && !preg_match('/^[0-9+() -]{7,20}$/', $data['phone_number'])) {
        $errors['phone_number'] = 'Phone number may contain digits, spaces, + ( ) - (7-20 characters).';
    }
    if (!in_array($data['specialization'], SPECIALIZATIONS, true)) {
        $errors['specialization'] = 'Choose a specialization from the list.';
    }
    if (!in_array($data['game_engine'], GAME_ENGINES, true)) {
        $errors['game_engine'] = 'Choose a game engine from the list.';
    }
    if ($data['programming_languages'] === '' || mb_strlen($data['programming_languages']) > 255) {
        $errors['programming_languages'] = 'List at least one programming language (max 255 characters).';
    }
    $years = filter_var($data['years_experience'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 50]]);
    if ($years === false) {
        $errors['years_experience'] = 'Years of experience must be a whole number from 0 to 50.';
    } else {
        $data['years_experience'] = $years;
    }
    if ($data['portfolio_url'] !== '') {
        $scheme = strtolower((string) parse_url($data['portfolio_url'], PHP_URL_SCHEME));
        if (!filter_var($data['portfolio_url'], FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)
            || mb_strlen($data['portfolio_url']) > 255) {
            $errors['portfolio_url'] = 'Portfolio must be a full http:// or https:// link.';
        }
    }
    if (!is_numeric($data['expected_salary']) || $data['expected_salary'] < 0 || $data['expected_salary'] > 99999999.99) {
        $errors['expected_salary'] = 'Expected salary must be a number of 0 or more.';
    } else {
        $data['expected_salary'] = number_format((float) $data['expected_salary'], 2, '.', '');
    }
    if (!in_array($data['application_status'], APPLICATION_STATUSES, true)) {
        $errors['application_status'] = 'Choose a valid status.';
    }

    // Empty optional fields are stored as NULL
    $data['phone_number']  = $data['phone_number']  === '' ? null : $data['phone_number'];
    $data['portfolio_url'] = $data['portfolio_url'] === '' ? null : $data['portfolio_url'];

    return [$data, $errors];
}

/* --------------------- CRUD: the 4 operations --------------------- */

// CREATE: insert a new application and return its new id
function insertApplicant(PDO $pdo, array $d): int {
    $stmt = $pdo->prepare(
        "INSERT INTO applicants
            (first_name, last_name, email, phone_number, specialization, game_engine,
             programming_languages, years_experience, portfolio_url, expected_salary, application_status)
         VALUES
            (:first_name, :last_name, :email, :phone_number, :specialization, :game_engine,
             :programming_languages, :years_experience, :portfolio_url, :expected_salary, :application_status)"
    );
    $stmt->execute($d);
    return (int) $pdo->lastInsertId();   // id generated by AUTO_INCREMENT
}

// READ (many): all applications, optionally filtered by search text and status
function getApplicants(PDO $pdo, string $search = '', string $status = ''): array {
    // escape % and _ so users cannot use them as wildcards
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $stmt = $pdo->prepare(
        "SELECT * FROM applicants
         WHERE (CONCAT(first_name, ' ', last_name) LIKE :s_name
                OR email LIKE :s_email
                OR programming_languages LIKE :s_lang
                OR specialization LIKE :s_spec
                OR game_engine LIKE :s_engine)
           AND (:status_all = '' OR application_status = :status)
         ORDER BY date_added DESC, applicant_id DESC"
    );
    $stmt->execute([
        ':s_name' => $like, ':s_email' => $like, ':s_lang' => $like, ':s_spec' => $like, ':s_engine' => $like,
        ':status_all' => $status, ':status' => $status,
    ]);
    return $stmt->fetchAll();
}

// READ (one): a single application by id, or null when it does not exist
function getApplicantById(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM applicants WHERE applicant_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// READ (summary): how many applications per status, for the summary badges
function getStatusCounts(PDO $pdo): array {
    $rows = $pdo->query(
        "SELECT application_status, COUNT(*) AS total FROM applicants GROUP BY application_status"
    )->fetchAll();
    $counts = array_fill_keys(APPLICATION_STATUSES, 0);
    foreach ($rows as $row) {
        $counts[$row['application_status']] = (int) $row['total'];
    }
    return $counts;
}

// UPDATE: change every editable field of one application
function updateApplicant(PDO $pdo, int $id, array $d): bool {
    $stmt = $pdo->prepare(
        "UPDATE applicants SET
            first_name = :first_name, last_name = :last_name, email = :email,
            phone_number = :phone_number, specialization = :specialization, game_engine = :game_engine,
            programming_languages = :programming_languages, years_experience = :years_experience,
            portfolio_url = :portfolio_url, expected_salary = :expected_salary,
            application_status = :application_status
         WHERE applicant_id = :applicant_id"
    );
    return $stmt->execute(array_merge($d, ['applicant_id' => $id]));
}

// DELETE: remove one application; returns true when a row was really deleted
function deleteApplicant(PDO $pdo, int $id): bool {
    $stmt = $pdo->prepare("DELETE FROM applicants WHERE applicant_id = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

?>
