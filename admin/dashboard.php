
<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/db.php';

$msg   = '';
$error = '';

/* ── Handle Actions ── */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ADD PROJECT */
if ($action === 'add_project' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['title_en','title_fr','title_ar','title_tr',
               'desc_en','desc_fr','desc_ar','desc_tr','tech'];
    $data = [];
    $valid = true;
    foreach ($fields as $f) {
        $v = trim($_POST[$f] ?? '');
        if (empty($v)) { $valid = false; break; }
        $data[$f] = $v;
    }
    $data['github_url'] = trim($_POST['github_url'] ?? '');
    $data['live_url']   = trim($_POST['live_url']   ?? '');

    if (!$valid) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO projects
             (title_en,title_fr,title_ar,title_tr,desc_en,desc_fr,desc_ar,desc_tr,tech,github_url,live_url)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param('sssssssssss',
            $data['title_en'],$data['title_fr'],$data['title_ar'],$data['title_tr'],
            $data['desc_en'],$data['desc_fr'],$data['desc_ar'],$data['desc_tr'],
            $data['tech'],$data['github_url'],$data['live_url']
        );
        if ($stmt->execute()) {
            $msg = '✓ Project added successfully!';
        } else {
            $error = 'Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}

/* DELETE PROJECT */
if ($action === 'delete_project' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $msg = '✓ Project deleted.';
    }
    $stmt->close();
}

/* DELETE MESSAGE */
if ($action === 'delete_message' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $msg = '✓ Message deleted.';
    }
    $stmt->close();
}

/* MARK MESSAGE READ */
if ($action === 'mark_read' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("UPDATE messages SET is_read = 1 WHERE id = $id");
    header('Location: dashboard.php');
    exit;
}

/* Fetch data */
$projects = $conn->query("SELECT * FROM projects ORDER BY created_at DESC");
$messages = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
$unreadCount = $conn->query("SELECT COUNT(*) as c FROM messages WHERE is_read = 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard | Mariem Mrabet</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* ---------- RESET ---------- */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #0f0f1a;
      color: #e2e8f0;
      min-height: 100vh;
    }

    /* ---------- LAYOUT ---------- */
    .admin-layout { display: flex; min-height: 100vh; }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: #16162a;
      border-right: 1px solid #2d2d4e;
      display: flex;
      flex-direction: column;
      padding: 1.5rem 0;
      position: fixed;
      top: 0; bottom: 0; left: 0;
      z-index: 100;
    }

    .sidebar-logo {
      font-size: 1.3rem;
      font-weight: 700;
      color: #6c63ff;
      text-align: center;
      padding: 0 1rem 1.5rem;
      border-bottom: 1px solid #2d2d4e;
      margin-bottom: 1rem;
    }
    .sidebar-logo span { color: #f72585; }

    .sidebar-nav { flex: 1; }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: .75rem;
      padding: .75rem 1.5rem;
      color: #94a3b8;
      text-decoration: none;
      font-size: .9rem;
      font-weight: 500;
      transition: all .2s;
    }

    .sidebar-nav a:hover,
    .sidebar-nav a.active {
      background: rgba(108,99,255,.1);
      color: #6c63ff;
      border-right: 3px solid #6c63ff;
    }

    .sidebar-nav a i { width: 20px; text-align: center; }

    .badge-count {
      margin-left: auto;
      background: #f72585;
      color: #fff;
      font-size: .7rem;
      padding: .1rem .45rem;
      border-radius: 999px;
      font-weight: 700;
    }

    .sidebar-footer { padding: 1rem 1.5rem; }

    .sidebar-footer a {
      display: flex;
      align-items: center;
      gap: .5rem;
      color: #ef4444;
      font-size: .85rem;
      text-decoration: none;
      transition: opacity .2s;
    }
    .sidebar-footer a:hover { opacity: .8; }

    /* Main */
    .main-content {
      margin-left: 240px;
      flex: 1;
      padding: 2rem;
      max-width: calc(100vw - 240px);
    }

    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
    }

    .page-header h1 {
      font-size: 1.6rem;
      font-weight: 700;
    }

    .page-header span { color: #94a3b8; font-size: .9rem; }

    /* Alerts */
    .alert {
      padding: 1rem 1.25rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      font-size: .9rem;
      font-weight: 500;
    }
    .alert-success { background: rgba(34,197,94,.1); color: #4ade80; border: 1px solid rgba(34,197,94,.2); }
    .alert-error   { background: rgba(239,68,68,.1);  color: #f87171; border: 1px solid rgba(239,68,68,.2);  }

    /* Stats */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: #1e1e35;
      border: 1px solid #2d2d4e;
      border-radius: 16px;
      padding: 1.5rem;
      text-align: center;
    }

    .stat-card .stat-icon {
      font-size: 2rem;
      color: #6c63ff;
      margin-bottom: .5rem;
    }

    .stat-card .stat-num {
      font-size: 2rem;
      font-weight: 700;
      color: #e2e8f0;
    }

    .stat-card .stat-label {
      font-size: .85rem;
      color: #94a3b8;
    }

    /* Tabs */
    .tabs {
      display: flex;
      gap: .5rem;
      margin-bottom: 2rem;
      border-bottom: 1px solid #2d2d4e;
    }

    .tab-btn {
      background: none;
      border: none;
      padding: .75rem 1.25rem;
      color: #94a3b8;
      font-size: .9rem;
      font-weight: 500;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -1px;
      transition: all .2s;
    }

    .tab-btn.active { color: #6c63ff; border-bottom-color: #6c63ff; }
    .tab-btn:hover  { color: #6c63ff; }

    .tab-pane { display: none; }
    .tab-pane.active { display: block; }

    /* Cards */
    .card {
      background: #1e1e35;
      border: 1px solid #2d2d4e;
      border-radius: 16px;
      padding: 1.75rem;
      margin-bottom: 1.5rem;
    }

    .card h3 {
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
      color: #e2e8f0;
    }

    /* Form */
    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
    }

    .form-full { grid-column: 1 / -1; }

    .form-group { margin-bottom: 0; }

    .form-group label {
      display: block;
      font-size: .8rem;
      font-weight: 600;
      color: #94a3b8;
      margin-bottom: .35rem;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: .7rem 1rem;
      background: #16162a;
      border: 1.5px solid #2d2d4e;
      border-radius: 10px;
      color: #e2e8f0;
      font-size: .9rem;
      font-family: 'Poppins', sans-serif;
      transition: border-color .2s;
    }

    .form-group textarea { resize: vertical; min-height: 80px; }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #6c63ff;
    }

    .btn-submit {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      background: linear-gradient(135deg, #6c63ff, #f72585);
      color: #fff;
      border: none;
      padding: .75rem 2rem;
      border-radius: 10px;
      font-size: .9rem;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      margin-top: 1.25rem;
      transition: opacity .2s, transform .2s;
    }

    .btn-submit:hover { opacity: .9; transform: translateY(-2px); }

    /* Table */
    .table-responsive { overflow-x: auto; }

    table.admin-table {
      width: 100%;
      border-collapse: collapse;
    }

    .admin-table th {
      text-align: left;
      padding: .75rem 1rem;
      font-size: .8rem;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .05em;
      border-bottom: 1px solid #2d2d4e;
    }

    .admin-table td {
      padding: .9rem 1rem;
      font-size: .9rem;
      border-bottom: 1px solid #2d2d4e;
      color: #cbd5e1;
      vertical-align: top;
    }

    .admin-table tr:last-child td { border-bottom: none; }

    .admin-table tr:hover td { background: rgba(108,99,255,.05); }

    .btn-delete {
      background: rgba(239,68,68,.1);
      color: #f87171;
      border: 1px solid rgba(239,68,68,.2);
      padding: .35rem .75rem;
      border-radius: 8px;
      font-size: .8rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: .3rem;
    }
    .btn-delete:hover { background: #ef4444; color: #fff; }

    .btn-read {
      background: rgba(108,99,255,.1);
      color: #a5b4fc;
      border: 1px solid rgba(108,99,255,.2);
      padding: .35rem .75rem;
      border-radius: 8px;
      font-size: .8rem;
      cursor: pointer;
      font-family: 'Poppins', sans-serif;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: .3rem;
    }
    .btn-read:hover { background: #6c63ff; color: #fff; }

    .badge-unread {
      display: inline-block;
      background: rgba(247,37,133,.15);
      color: #f72585;
      border-radius: 999px;
      padding: .15rem .5rem;
      font-size: .75rem;
      font-weight: 700;
    }

    .truncate { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .action-btns { display: flex; gap: .4rem; flex-wrap: wrap; }

    /* Session info */
    .session-info {
      background: rgba(108,99,255,.08);
      border: 1px solid rgba(108,99,255,.2);
      border-radius: 10px;
      padding: .75rem 1rem;
      font-size: .85rem;
      color: #a5b4fc;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .main-content { margin-left: 0; max-width: 100vw; padding: 1rem; }
      .form-grid { grid-template-columns: 1fr; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>

<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo"><span>&lt;</span>MM<span>/&gt;</span> Admin</div>
    <nav class="sidebar-nav">
      <a href="#" class="active" onclick="showTab('dashboard')">
        <i class="fas fa-chart-pie"></i> Dashboard
      </a>
      <a href="#" onclick="showTab('projects')">
        <i class="fas fa-folder"></i> Projects
      </a>
      <a href="#" onclick="showTab('messages')">
        <i class="fas fa-envelope"></i> Messages
        <?php if ($unreadCount > 0): ?>
          <span class="badge-count"><?= $unreadCount ?></span>
        <?php endif; ?>
      </a>
      <a href="../index.html" target="_blank">
        <i class="fas fa-external-link-alt"></i> View Site
      </a>
    </nav>
    <div class="sidebar-footer">
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">
    <div class="page-header">
      <div>
        <h1>Admin Dashboard</h1>
        <span>Welcome back, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</span>
      </div>
    </div>

    <!-- Session Info -->
    <div class="session-info">
      <i class="fas fa-shield-alt"></i>
      Logged in as <strong>&nbsp;<?= htmlspecialchars($_SESSION['admin_username']) ?></strong>
      &nbsp;| Session ID: <?= substr(session_id(), 0, 12) ?>...
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tabs">
      <button class="tab-btn active" onclick="showTab('dashboard')">
        <i class="fas fa-chart-pie"></i> Dashboard
      </button>
      <button class="tab-btn" onclick="showTab('projects')">
        <i class="fas fa-folder"></i> Projects
      </button>
      <button class="tab-btn" onclick="showTab('messages')">
        <i class="fas fa-envelope"></i> Messages
        <?php if ($unreadCount > 0): ?>
          <span class="badge-count"><?= $unreadCount ?></span>
        <?php endif; ?>
      </button>
    </div>

    <!-- ======= DASHBOARD TAB ======= -->
    <div id="tab-dashboard" class="tab-pane active">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
          <div class="stat-num"><?= $projects->num_rows ?></div>
          <div class="stat-label">Total Projects</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-envelope"></i></div>
          <div class="stat-num"><?= $messages->num_rows ?></div>
          <div class="stat-label">Total Messages</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-bell"></i></div>
          <div class="stat-num"><?= $unreadCount ?></div>
          <div class="stat-label">Unread Messages</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
          <div class="stat-num">1</div>
          <div class="stat-label">Admin Users</div>
        </div>
      </div>

      <div class="card">
        <h3><i class="fas fa-info-circle"></i> Session Information</h3>
        <table class="admin-table">
          <tr>
            <td><strong>Session ID</strong></td>
            <td><?= session_id() ?></td>
          </tr>
          <tr>
            <td><strong>Admin Username</strong></td>
            <td><?= htmlspecialchars($_SESSION['admin_username']) ?></td>
          </tr>
          <tr>
            <td><strong>Login Time</strong></td>
            <td><?= date('Y-m-d H:i:s') ?></td>
          </tr>
          <tr>
            <td><strong>PHP Version</strong></td>
            <td><?= phpversion() ?></td>
          </tr>
        </table>
      </div>
    </div>

    <!-- ======= PROJECTS TAB ======= -->
    <div id="tab-projects" class="tab-pane">

      <!-- Add Project Form -->
      <div class="card">
        <h3><i class="fas fa-plus-circle"></i> Add New Project</h3>
        <form method="POST" action="">
          <input type="hidden" name="action" value="add_project" />
          <div class="form-grid">

            <div class="form-group">
              <label>Title (English) *</label>
              <input type="text" name="title_en" required placeholder="Project Name" />
            </div>
            <div class="form-group">
              <label>Title (French) *</label>
              <input type="text" name="title_fr" required placeholder="Nom du projet" />
            </div>
            <div class="form-group">
              <label>Title (Arabic) *</label>
              <input type="text" name="title_ar" required placeholder="اسم المشروع" dir="rtl" />
            </div>
            <div class="form-group">
              <label>Title (Turkish) *</label>
              <input type="text" name="title_tr" required placeholder="Proje Adı" />
            </div>

            <div class="form-group form-full">
              <label>Description (English) *</label>
              <textarea name="desc_en" required placeholder="Project description in English..."></textarea>
            </div>
            <div class="form-group form-full">
              <label>Description (French) *</label>
              <textarea name="desc_fr" required placeholder="Description du projet en français..."></textarea>
            </div>
            <div class="form-group form-full">
              <label>Description (Arabic) *</label>
              <textarea name="desc_ar" required placeholder="وصف المشروع بالعربية..." dir="rtl"></textarea>
            </div>
            <div class="form-group form-full">
              <label>Description (Turkish) *</label>
              <textarea name="desc_tr" required placeholder="Proje açıklaması Türkçe..."></textarea>
            </div>

            <div class="form-group">
              <label>Technologies *</label>
              <input type="text" name="tech" required placeholder="HTML, CSS, JavaScript, PHP" />
            </div>
            <div class="form-group">
              <label>GitHub URL</label>
              <input type="url" name="github_url" placeholder="https://github.com/..." />
            </div>
            <div class="form-group">
              <label>Live URL</label>
              <input type="url" name="live_url" placeholder="https://yourproject.com" />
            </div>
          </div>
          <button type="submit" class="btn-submit">
            <i class="fas fa-plus"></i> Add Project
          </button>
        </form>
      </div>

      <!-- Projects Table -->
      <div class="card">
        <h3><i class="fas fa-list"></i> Existing Projects</h3>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Title (EN)</th>
                <th>Technologies</th>
                <th>GitHub</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $projects->data_seek(0);
              while ($p = $projects->fetch_assoc()):
              ?>
              <tr>
                <td><?= $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['title_en']) ?></strong></td>
                <td><?= htmlspecialchars($p['tech']) ?></td>
                <td>
                  <?php if ($p['github_url']): ?>
                    <a href="<?= htmlspecialchars($p['github_url']) ?>"
                       target="_blank" style="color:#6c63ff">
                      <i class="fab fa-github"></i>
                    </a>
                  <?php else: ?>
                    <span style="color:#64748b">—</span>
                  <?php endif; ?>
                </td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td>
                  <div class="action-btns">
                    <a href="?action=delete_project&id=<?= $p['id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Delete this project?')">
                      <i class="fas fa-trash"></i> Delete
                    </a>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ======= MESSAGES TAB ======= -->
    <div id="tab-messages" class="tab-pane">
      <div class="card">
        <h3><i class="fas fa-inbox"></i> Contact Messages</h3>
        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $messages->data_seek(0);
              while ($m = $messages->fetch_assoc()):
              ?>
              <tr>
                <td><?= $m['id'] ?></td>
                <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                <td>
                  <a href="mailto:<?= htmlspecialchars($m['email']) ?>"
                     style="color:#6c63ff">
                    <?= htmlspecialchars($m['email']) ?>
                  </a>
                </td>
                <td class="truncate"><?= htmlspecialchars($m['subject']) ?></td>
                <td class="truncate" title="<?= htmlspecialchars($m['message']) ?>">
                  <?= htmlspecialchars(mb_substr($m['message'], 0, 60)) ?>
                  <?= mb_strlen($m['message']) > 60 ? '...' : '' ?>
                </td>
                <td><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                <td>
                  <?php if (!$m['is_read']): ?>
                    <span class="badge-unread">Unread</span>
                  <?php else: ?>
                    <span style="color:#4ade80;font-size:.8rem">✓ Read</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="action-btns">
                    <?php if (!$m['is_read']): ?>
                      <a href="?action=mark_read&id=<?= $m['id'] ?>"
                         class="btn-read">
                        <i class="fas fa-check"></i> Read
                      </a>
                    <?php endif; ?>
                    <a href="?action=delete_message&id=<?= $m['id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Delete this message?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
  function showTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));

    const pane = document.getElementById('tab-' + name);
    if (pane) pane.classList.add('active');

    document.querySelectorAll('.tab-btn').forEach(b => {
      if (b.textContent.toLowerCase().includes(name)) b.classList.add('active');
    });
  }
</script>
</body>
</html>
