<?php

// ---------------------------------------------------------------
// handleForms.php - receives every form submission (add / update / delete),
// runs the matching model function, then redirects back with a message.
// ---------------------------------------------------------------
require_once __DIR__ . '/dbConfig.php';
require_once __DIR__ . '/models.php';

// Only POST requests are accepted; anything else goes back to the home page
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../index.php');
}

// Reject forms that did not come from our own pages (CSRF protection)
if (!csrfIsValid($_POST['csrf'] ?? null)) {
    setFlash('error', 'Your session expired. Please try again.');
    redirect('../index.php');
}

$action = $_POST['action'] ?? '';
$id     = (int) ($_POST['applicant_id'] ?? 0);

try {
    switch ($action) {

        // ---------- CREATE ----------
        case 'add':
            [$data, $errors] = validateApplicant($_POST);

            if (!$errors) {
                try {
                    insertApplicant($pdo, $data);
                    setFlash('success', "Application of {$data['first_name']} {$data['last_name']} was added.");
                    redirect('../index.php');
                } catch (PDOException $e) {
                    // 1062 = duplicate entry (the email column is UNIQUE)
                    if (($e->errorInfo[1] ?? 0) === 1062) {
                        $errors['email'] = 'An application with this email already exists.';
                    } else {
                        throw $e;
                    }
                }
            }
            // Validation failed: keep what the user typed and show the errors
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect('../index.php');
            break;

        // ---------- UPDATE ----------
        case 'update':
            if (!getApplicantById($pdo, $id)) {
                setFlash('error', 'That application no longer exists.');
                redirect('../index.php');
            }
            [$data, $errors] = validateApplicant($_POST);

            if (!$errors) {
                try {
                    updateApplicant($pdo, $id, $data);
                    setFlash('success', "Application #{$id} was updated.");
                    redirect('../index.php');
                } catch (PDOException $e) {
                    if (($e->errorInfo[1] ?? 0) === 1062) {
                        $errors['email'] = 'Another application already uses this email.';
                    } else {
                        throw $e;
                    }
                }
            }
            $_SESSION['old'] = $_POST;
            $_SESSION['errors'] = $errors;
            redirect("../editApplicant.php?id={$id}");
            break;

        // ---------- DELETE ----------
        case 'delete':
            if (deleteApplicant($pdo, $id)) {
                setFlash('success', "Application #{$id} was deleted.");
            } else {
                setFlash('error', 'That application was already deleted.');
            }
            redirect('../index.php');
            break;

        default:
            setFlash('error', 'Unknown action.');
            redirect('../index.php');
    }
} catch (PDOException $e) {
    // Any other database problem: show a friendly message (details stay in the server log)
    error_log($e->getMessage());
    setFlash('error', 'Something went wrong with the database. Please try again.');
    redirect('../index.php');
}
