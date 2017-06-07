<?php
        require_once('settings.inc');
	$message = array();
	if (!empty($_POST)) {
		if (
			empty($_POST['f']['username']) ||
			empty($_POST['f']['password']) ||
			empty($_POST['f']['password_again'])
		) {
			$message['error'] = 'Es wurden nicht alle Felder ausgefüllt.';
		} else if ($_POST['f']['password'] != $_POST['f']['password_again']) {
			$message['error'] = 'Die eingegebenen Passwörter stimmen nicht überein.';
		} else {
			unset($_POST['f']['password_again']);
			$salt = ''; 
			for ($i = 0; $i < 22; $i++) { 
				$salt .= substr('./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', mt_rand(0, 63), 1); 
			}
			$_POST['f']['password'] = crypt(
				$_POST['f']['password'],
				'$2a$10$' . $salt
			);
			
			$mysqli = @new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
			if ($mysqli->connect_error) {
				$message['error'] = 'Datenbankverbindung fehlgeschlagen: ' . $mysqli->connect_error;
			}
			$query = sprintf(
				"INSERT INTO users (username, password)
				SELECT * FROM (SELECT '%s', '%s') as new_user
				WHERE NOT EXISTS (
					SELECT username FROM users WHERE username = '%s'
				) LIMIT 1;",
				$mysqli->real_escape_string($_POST['f']['username']),
				$mysqli->real_escape_string($_POST['f']['password']),
				$mysqli->real_escape_string($_POST['f']['username'])
			);
			$mysqli->query($query);
			if ($mysqli->affected_rows == 1) {
				$message['success'] = 'Neuer Benutzer (' . htmlspecialchars($_POST['f']['username']) . ') wurde angelegt, <a href="login.php">weiter zur Anmeldung</a>.';
				header('Location: http://' . $_SERVER['HTTP_HOST'] . '/smartfridge/login.php');
			} else {
				$message['error'] = 'Der Benutzername ist bereits vergeben.';
			}
			$mysqli->close();
		}
	} else {
		$message['notice'] = 'Übermitteln Sie das ausgefüllte Formular um ein neues Benutzerkonto zu erstellen.';
	}
?><!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>loginsystem - register.php</title>
	</head>
	<body>
		<form action="./register.php" method="post">
<?php if (isset($message['error'])): ?>
			<fieldset class="error"><legend>Fehler</legend><?php echo $message['error'] ?></fieldset>
<?php endif;
	if (isset($message['success'])): ?>
			<fieldset class="success"><legend>Erfolg</legend><?php echo $message['success'] ?></fieldset>
<?php endif;
	if (isset($message['notice'])): ?>
			<fieldset class="notice"><legend>Hinweis</legend><?php echo $message['notice'] ?></fieldset>
<?php endif; ?>
			<fieldset>
				<legend>Benutzerdaten</legend>
				<div><label for="username">Benutzername</label> <input type="text" name="f[username]" id="username"<?php echo isset($_POST['f']['username']) ? ' value="' . htmlspecialchars($_POST['f']['username']) . '"' : '' ?> /></div>
				<div><label for="password">Kennwort</label> <input type="password" name="f[password]" id="password" /></div>
				<div><label for="password_again">Kennwort wiederholen</label> <input type="password" name="f[password_again]" id="password_again" /></div>
			</fieldset>
			<fieldset>
				<div><input type="submit" name="submit" value="Registrieren" /></div>
			</fieldset>
		</form>
	</body>
</html>
