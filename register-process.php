<?php
// Change the below variables to reflect your MySQL database details
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'phplogin';
// Try and connect using the info above
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);

// Check for connection errors
if (mysqli_connect_errno()) {
	exit('Failed to connect to MySQL: ' . mysqli_connect_error());
}

// Función auxiliar para alertas y redirecciones en JS
function alertAndRedirect($message, $url) {
    echo "<script>alert('$message'); window.location.href='$url';</script>";
    exit;
}

if (!isset($_POST['username'], $_POST['password'], $_POST['email'])) {
	alertAndRedirect('¡Por favor, completa todo el formulario!', 'register.php');
}
if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['email'])) {
	alertAndRedirect('¡Por favor, completa todo el formulario!', 'register.php');
}
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
	alertAndRedirect('¡El correo electrónico no es válido!', 'register.php');
}
if (preg_match('/^[a-zA-Z0-9]+$/', $_POST['username']) == 0) {
    alertAndRedirect('¡El usuario solo puede contener letras y números!', 'register.php');
}
if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
	alertAndRedirect('¡La contraseña debe tener entre 5 y 20 caracteres!', 'register.php');
}

if ($stmt = $con->prepare('SELECT id, password FROM accounts WHERE username = ?')) {
	$stmt->bind_param('s', $_POST['username']);
	$stmt->execute();
	$stmt->store_result();
	
	if ($stmt->num_rows > 0) {
		alertAndRedirect('¡El nombre de usuario ya existe! Elige otro.', 'register.php');
	} else {
		$registered = date('Y-m-d H:i:s');
		$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
		
        if ($stmt2 = $con->prepare('INSERT INTO accounts (username, password, email, registered) VALUES (?, ?, ?, ?)')) {
            $stmt2->bind_param('ssss', $_POST['username'], $password, $_POST['email'], $registered);
            $stmt2->execute();
			// Éxito: Se envía al login.
            alertAndRedirect('¡Te has registrado exitosamente! Ya puedes iniciar sesión.', 'index.php');
        } else {
            alertAndRedirect('¡Error al crear la cuenta!', 'register.php');
        }
	}
	$stmt->close();
} else {
	alertAndRedirect('¡Error en la base de datos!', 'register.php');
}
$con->close();
?>