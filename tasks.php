<?php
session_start();
// Si no está logueado, redirigir al login
if (!isset($_SESSION['account_loggedin'])) {
	header('Location: index.php');
	exit;
}

$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

$account_id = $_SESSION['account_id'];

// === 1. CREATE: Agregar nueva tarea ===
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    if (!empty($title)) {
        $stmt = $con->prepare('INSERT INTO tasks (account_id, title, description) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $account_id, $title, $desc);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: tasks.php');
    exit;
}

// === 1.5 UPDATE: Guardar tarea editada ===
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $task_id = $_POST['task_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    if (!empty($title)) {
        $stmt = $con->prepare('UPDATE tasks SET title = ?, description = ? WHERE id = ? AND account_id = ?');
        $stmt->bind_param('ssii', $title, $desc, $task_id, $account_id);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: tasks.php');
    exit;
}

// === 2. DELETE: Eliminar tarea ===
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];
    $stmt = $con->prepare('DELETE FROM tasks WHERE id = ? AND account_id = ?');
    $stmt->bind_param('ii', $task_id, $account_id);
    $stmt->execute();
    $stmt->close();
    header('Location: tasks.php');
    exit;
}

// === 3. UPDATE: Cambiar estado (Pendiente / Completada) ===
if (isset($_GET['toggle'])) {
    $task_id = $_GET['toggle'];
    // Obtener estado actual
    $stmt = $con->prepare('SELECT status FROM tasks WHERE id = ? AND account_id = ?');
    $stmt->bind_param('ii', $task_id, $account_id);
    $stmt->execute();
    $stmt->bind_result($status);
    if($stmt->fetch()) {
        $new_status = ($status === 'Pendiente') ? 'Completada' : 'Pendiente';
        $stmt->close();
        // Actualizar al nuevo estado
        $update_stmt = $con->prepare('UPDATE tasks SET status = ? WHERE id = ? AND account_id = ?');
        $update_stmt->bind_param('sii', $new_status, $task_id, $account_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $stmt->close();
    }
    header('Location: tasks.php');
    exit;
}

// Verificar si se está editando una tarea específica
$edit_task = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $con->prepare('SELECT id, title, description FROM tasks WHERE id = ? AND account_id = ?');
    $stmt->bind_param('ii', $edit_id, $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_task = $row;
    }
    $stmt->close();
}

// === 4. READ: Obtener todas las tareas ===
$tasks = [];
$stmt = $con->prepare('SELECT id, title, description, status, created_at FROM tasks WHERE account_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $account_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>Mis Tareas</title>
		<link href="style.css" rel="stylesheet" type="text/css">
        <style>
            .task-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            .task-table th, .task-table td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; vertical-align: top; }
            .task-table th { background: #f8f9fa; color: #333; font-weight: bold; }
            .badge { padding: 5px 10px; border-radius: 20px; color: white; font-size: 12px; font-weight: bold; display: inline-block; }
            .bg-green { background: #28a745; }
            .bg-orange { background: #ffc107; color: #333;}
            .btn-action { text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; color: white; display: inline-block; margin-bottom: 6px; width: 80px; text-align: center; }
            .btn-blue { background: #007bff; }
            .btn-blue:hover { background: #0056b3; }
            .btn-gray { background: #6c757d; }
            .btn-gray:hover { background: #5a6268; }
            .btn-red { background: #dc3545; }
            .btn-red:hover { background: #c82333; }
            .form-control { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
            .action-column { min-width: 100px; }
        </style>
	</head>
    <body>

		<header class="header">
			<div class="wrapper">
				<h1>Gestor de Tareas Pro</h1>
				<nav class="menu">
					<a href="home.php">Inicio</a>
					<a href="tasks.php" style="border-bottom: 2px solid white;">Mis Tareas</a>
					<a href="profile.php">Mi Perfil</a>
					<a href="logout.php">Logout</a>
				</nav>
			</div>
		</header>

		<div class="content">
			<div class="page-title">
				<div class="wrap">
					<h2>Mis Tareas</h2>
					<p>Gestiona tus pendientes, <?=htmlspecialchars($_SESSION['account_name'], ENT_QUOTES)?>.</p>
				</div>
			</div>

			<div class="block">
                <?php if ($edit_task): ?>
                    <h3 style="margin-bottom: 15px; color: #333;">Editar Tarea</h3>
                    <form action="tasks.php" method="POST" style="margin-bottom: 35px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="task_id" value="<?=$edit_task['id']?>">
                        <input type="text" name="title" class="form-control" value="<?=htmlspecialchars($edit_task['title'], ENT_QUOTES)?>" required>
                        <textarea name="description" class="form-control" rows="3"><?=htmlspecialchars($edit_task['description'], ENT_QUOTES)?></textarea>
                        <button type="submit" class="btn blue" style="padding: 10px 20px; border:none; cursor:pointer;">Actualizar Tarea</button>
                        <a href="tasks.php" style="margin-left: 15px; color: #666; text-decoration: none;">Cancelar</a>
                    </form>
                <?php else: ?>
                    <h3 style="margin-bottom: 15px; color: #333;">Agregar Nueva Tarea</h3>
                    <form action="tasks.php" method="POST" style="margin-bottom: 35px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
                        <input type="hidden" name="action" value="add">
                        <input type="text" name="title" class="form-control" placeholder="Ej. Preparar reporte de métricas mensuales..." required>
                        <textarea name="description" class="form-control" placeholder="Descripción detallada (opcional)..." rows="3"></textarea>
                        <button type="submit" class="btn blue" style="padding: 10px 20px; border:none; cursor:pointer;">Guardar Tarea</button>
                    </form>
                <?php endif; ?>

                <h3 style="margin-bottom: 15px; color: #333;">Lista de Tareas</h3>
                <table class="task-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="action-column">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 30px; color:#777;">No tienes tareas registradas aún.</td></tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $t): ?>
                            <tr>
                                <td><strong><?=htmlspecialchars($t['title'], ENT_QUOTES)?></strong></td>
                                <td style="color:#555;"><?=htmlspecialchars($t['description'], ENT_QUOTES)?></td>
                                <td>
                                    <?php if ($t['status'] == 'Completada'): ?>
                                        <span class="badge bg-green">Completada</span>
                                    <?php else: ?>
                                        <span class="badge bg-orange">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?=date('d/m/Y', strtotime($t['created_at']))?></td>
                                <td>
                                    <a href="tasks.php?toggle=<?=$t['id']?>" class="btn-action btn-blue">
                                        <?=$t['status'] == 'Pendiente' ? 'Completar' : 'Reabrir'?>
                                    </a><br>
                                    <a href="tasks.php?edit=<?=$t['id']?>" class="btn-action btn-gray">Editar</a><br>
                                    <a href="tasks.php?delete=<?=$t['id']?>" class="btn-action btn-red" onclick="return confirm('¿Seguro que deseas eliminar esta tarea permanentemente?');">Borrar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
			</div>
		</div>
    </body>
</html>

