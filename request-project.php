<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/csrf.php';

require_csrf_token();

$messageSent = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userName = trim($_POST['user_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $projectType = trim($_POST['project_type'] ?? 'New Software Project');
        $projectCategory = trim($_POST['project_category'] ?? 'Website');
        $budgetRange = trim($_POST['budget_range'] ?? 'Negotiable');
        $expectedDelivery = !empty($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : null;
        $description = trim($_POST['description'] ?? '');

        if (empty($userName) || empty($email) || empty($description)) {
            throw new Exception("Please provide your Name, Email, and Project Description.");
        }

        $filePath = handle_file_upload('file', 'requests');
        $userId = is_logged_in() ? $_SESSION['user_id'] : null;

        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO project_requests (user_id, user_name, email, phone, company_name, project_type, project_category, budget_range, expected_delivery_date, description, file_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $userName, $email, $phone, $companyName, $projectType, $projectCategory, $budgetRange, $expectedDelivery, $description, $filePath]);

        // Send Email Receipts to User & Admin via SMTP
        send_project_request_receipt_email($email, $userName, $projectType, $projectCategory, $budgetRange);
        send_admin_project_request_alert($userName, $email, $phone, $projectType, $budgetRange, $description);

        $messageSent = "Project request submitted successfully! A confirmation email has been sent to " . htmlspecialchars($email) . ". Our team will review your proposal and reply promptly.";
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

$user = get_current_user_data();
?>

<main>
    <section class="page-hero">
        <form class="glass-card form premium-form" method="post" action="" enctype="multipart/form-data" style="margin:0 auto;">
            <?php echo csrf_input(); ?>
            <div class="form-head">
                <span class="form-badge"><?php echo render_icon('TerminalSquare', 15); ?>Project Intake</span>
                <h2>Request a project</h2>
                <p>Provide details about your custom software, AI assistant, mobile app, or portal project. Attach documents if available.</p>
            </div>

            <?php if ($messageSent): ?>
                <p class="success"><?php echo htmlspecialchars($messageSent); ?></p>
            <?php endif; ?>

            <?php if ($errorMessage): ?>
                <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>

            <label>Name
                <input name="user_name" type="text" placeholder="e.g. Babajide Sanwo-Olu" value="<?php echo htmlspecialchars($_POST['user_name'] ?? $user['full_name'] ?? ''); ?>" required>
            </label>

            <label>Email
                <input name="email" type="email" placeholder="b.sanwoolu@company.ng" value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
            </label>

            <label>Phone
                <input name="phone" type="text" placeholder="+234 803 123 4567" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </label>

            <label>Company name
                <input name="company_name" type="text" placeholder="e.g. Lagos Tech Dynamics Ltd" value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>">
            </label>

            <label>Project type / Requirement
                <input name="project_type" type="text" value="<?php echo htmlspecialchars($_GET['project_type'] ?? $_POST['project_type'] ?? 'New Software Project'); ?>" required>
            </label>

            <label>Category
                <select name="project_category">
                    <option value="Website">Website Development</option>
                    <option value="Mobile App">Mobile App Development</option>
                    <option value="AI Solution">AI & Machine Learning</option>
                    <option value="School Portal">School Portal System</option>
                    <option value="Enterprise Dashboard">Business Management System</option>
                    <option value="API Integration">API & Microservices</option>
                </select>
            </label>

            <label>Budget range
                <input name="budget_range" type="text" value="<?php echo htmlspecialchars($_POST['budget_range'] ?? ''); ?>" placeholder="e.g. $5,000 - $15,000">
            </label>

            <label>Expected delivery date
                <input name="expected_delivery_date" type="date" value="<?php echo htmlspecialchars($_POST['expected_delivery_date'] ?? ''); ?>">
            </label>

            <label>Detailed project description
                <textarea name="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </label>

            <label class="file-field">Attach Project Specification / Brief File
                <input type="file" name="file" accept=".pdf,.doc,.docx,.png,.jpg,.zip">
                <span>Upload PDF, Word, Image, or ZIP requirements document if available.</span>
            </label>

            <button class="btn primary" type="submit"><?php echo render_icon('CheckCircle2'); ?>Submit Request</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
