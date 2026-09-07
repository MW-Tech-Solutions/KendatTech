<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_login('login.php');
require_once __DIR__ . '/includes/header.php';

$user = get_current_user_data();
$pdo = get_db();

// Fetch appointments for current user
$appStmt = $pdo->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY id DESC");
$appStmt->execute([$user['id']]);
$userAppointments = $appStmt->fetchAll();

// Fetch project requests for current user
$reqStmt = $pdo->prepare("SELECT * FROM project_requests WHERE user_id = ? ORDER BY id DESC");
$reqStmt->execute([$user['id']]);
$userRequests = $reqStmt->fetchAll();

$appointmentNotice = '';
$requestNotice = '';

// Handle quick appointment submission from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book_appointment') {
    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $preferredDate = $_POST['preferred_date'] ?? '';
        $preferredTime = $_POST['preferred_time'] ?? '';
        $appointmentType = trim($_POST['appointment_type'] ?? 'Consultation');
        $messageText = trim($_POST['message'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, full_name, email, phone, preferred_date, preferred_time, appointment_type, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $fullName, $email, $phone, $preferredDate, $preferredTime, $appointmentType, $messageText]);

        $appointmentNotice = "Appointment submitted successfully!";
        // Refresh appointments
        $appStmt->execute([$user['id']]);
        $userAppointments = $appStmt->fetchAll();
    } catch (Throwable $e) {
        $appointmentNotice = "Error: " . $e->getMessage();
    }
}

// Handle quick project request submission from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_project') {
    try {
        $userName = trim($_POST['user_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $projectType = trim($_POST['project_type'] ?? 'New Project');
        $projectCategory = trim($_POST['project_category'] ?? 'Website');
        $budgetRange = trim($_POST['budget_range'] ?? '');
        $expectedDelivery = !empty($_POST['expected_delivery_date']) ? $_POST['expected_delivery_date'] : null;
        $description = trim($_POST['description'] ?? '');

        $filePath = handle_file_upload('file', 'requests');

        $stmt = $pdo->prepare("INSERT INTO project_requests (user_id, user_name, email, phone, company_name, project_type, project_category, budget_range, expected_delivery_date, description, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $userName, $email, $phone, $companyName, $projectType, $projectCategory, $budgetRange, $expectedDelivery, $description, $filePath]);

        $requestNotice = "Project request submitted successfully!";
        // Refresh project requests
        $reqStmt->execute([$user['id']]);
        $userRequests = $reqStmt->fetchAll();
    } catch (Throwable $e) {
        $requestNotice = "Error: " . $e->getMessage();
    }
}
?>

<main>
    <section class="page-hero">
        <div class="section-title">
            <span>User Dashboard</span>
            <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p>Track appointments and project requests from one place.</p>
        </div>
    </section>

    <section class="band dashboard-grid">
        <div class="glass-card table-card">
            <h3>My Appointments</h3>
            <?php if (empty($userAppointments)): ?>
                <p class="muted">No appointments booked yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Preferred Date</th>
                            <th>Appointment Type</th>
                            <th>Status</th>
                            <th>Admin Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userAppointments as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['preferred_date']); ?></td>
                                <td><?php echo htmlspecialchars($app['appointment_type']); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($app['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($app['admin_note'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="glass-card table-card">
            <h3>My Project Requests</h3>
            <?php if (empty($userRequests)): ?>
                <p class="muted">No project requests submitted yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Budget Range</th>
                            <th>Status</th>
                            <th>Admin Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userRequests as $req): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($req['project_category']); ?></td>
                                <td><?php echo htmlspecialchars($req['budget_range'] ?? '-'); ?></td>
                                <td><span class="status"><?php echo htmlspecialchars($req['status']); ?></span></td>
                                <td><?php echo htmlspecialchars($req['admin_feedback'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <section class="band split">
        <form class="glass-card form premium-form" method="post" action="">
            <input type="hidden" name="action" value="book_appointment">
            <div class="form-head">
                <span class="form-badge"><?php echo render_icon('TerminalSquare', 15); ?>Quick Action</span>
                <h2>Book an appointment</h2>
            </div>
            <?php if ($appointmentNotice): ?>
                <p class="<?php echo strpos($appointmentNotice, 'Error') === 0 ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($appointmentNotice); ?></p>
            <?php endif; ?>
            <label>Full name<input name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></label>
            <label>Email<input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label>
            <label>Phone<input name="phone" value=""></label>
            <label>Preferred date<input name="preferred_date" type="date" required></label>
            <label>Preferred time<input name="preferred_time" type="time" required></label>
            <label>Appointment type<input name="appointment_type" value="Software Consultation" required></label>
            <label>Message<textarea name="message" required></textarea></label>
            <button class="btn primary" type="submit"><?php echo render_icon('CheckCircle2'); ?>Submit</button>
        </form>

        <form class="glass-card form premium-form" method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="request_project">
            <div class="form-head">
                <span class="form-badge"><?php echo render_icon('TerminalSquare', 15); ?>Quick Action</span>
                <h2>Request a project</h2>
            </div>
            <?php if ($requestNotice): ?>
                <p class="<?php echo strpos($requestNotice, 'Error') === 0 ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($requestNotice); ?></p>
            <?php endif; ?>
            <label>Name<input name="user_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required></label>
            <label>Email<input name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></label>
            <label>Phone<input name="phone" value=""></label>
            <label>Company name<input name="company_name" value=""></label>
            <label>Project type<input name="project_type" value="New Software Project" required></label>
            <label>Category<input name="project_category" value="Website" required></label>
            <label>Budget range<input name="budget_range" value=""></label>
            <label>Expected delivery date<input name="expected_delivery_date" type="date"></label>
            <label>Detailed project description<textarea name="description" required></textarea></label>
            <button class="btn primary" type="submit"><?php echo render_icon('CheckCircle2'); ?>Submit</button>
        </form>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
