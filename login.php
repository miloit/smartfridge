<?php
require_once('settings.inc');
if (isset($_SESSION['login'])) {
	header('Location: http://' . $_SERVER['HTTP_HOST'] . '/smartfridge/index.php');
} else {
	if (!empty($_POST)) {
                //var_dump($_POST);
                if ($_POST['f']['submit'] == "ID")
                {
                  $_POST['f']['userid'] = shell_exec(PATH_LOGIN_PY);
                  $message['notice'] = 'Id eingelesen.<br />' .
                        'Wenn Sie noch kein Konto haben, gehen Sie <a href="./register.php">zur Registrierung</a>.';

                }else{
		if(!( ( !empty( $_POST['f']['password'] ) && !empty( $_POST['f']['username'] ) && empty( $_POST['f']['userid'] ) ) ||  ( empty( $_POST['f']['password'] ) && empty( $_POST['f']['username'] ) && !empty( $_POST['f']['userid'] ) ) ))
                {
			$message['error'] = 'Es wurden nicht alle Felder ausgefüllt.';
		} else {
			$mysqli = @new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
			if ($mysqli->connect_error) {
				$message['error'] = 'Datenbankverbindung fehlgeschlagen: ' . $mysqli->connect_error;
			} else {
                                if(empty($_POST['f']['userid']))
                                {
				    $query = sprintf("SELECT username, password FROM users WHERE username = '%s'",$mysqli->real_escape_string($_POST['f']['username']));
				    $result = $mysqli->query($query);
				    if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
					if (crypt($_POST['f']['password'], $row['password']) == $row['password']) {
						session_start();
						
						$_SESSION = array(
							'login' => true,
							'user'  => array(
								'username'  => $row['username']
							)
						);
						$message['success'] = 'Anmeldung erfolgreich, <a href="index.php">weiter zum Inhalt.';
						header('Location: http://' . $_SERVER['HTTP_HOST'] . '/smartfridge/index.php');
					} else {
						$message['error'] = 'Das Kennwort ist nicht korrekt.';
					}
				    } else {
					$message['error'] = 'Der Benutzer wurde nicht gefunden.';
				    }
				    $mysqli->close();
                                 }
                                 else
                                 {
                                     $query = sprintf("SELECT username, password FROM users WHERE id = '%s'",$mysqli->real_escape_string($_POST['f']['userid']));
                                     $result = $mysqli->query($query);
                                     if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                                                session_start();

                                                $_SESSION = array(
                                                        'login' => true,
                                                        'user'  => array(
                                                                'username'  => $row['username']
                                                        )
                                                );
                                                $message['success'] = 'Anmeldung erfolgreich, <a href="index.php">weiter zum Inhalt.';
                                                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/smartfridge/index.php');
                                      } else {
                                        $message['error'] = 'Der Benutzer wurde nicht gefunden.';
                                    }
                                    $mysqli->close();

                                 }
			}
		}

         }
	} else {
		$message['notice'] = 'Geben Sie Ihre Zugangsdaten ein um sich anzumelden.<br />' .
			'Wenn Sie noch kein Konto haben, gehen Sie <a href="./register.php">zur Registrierung</a>.';
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>loginsystem - login.php</title>
	</head>
	<body>
		<form action="./login.php" method="post">
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
				<div><label for="username">Benutzername</label>
					<input type="text" name="f[username]" id="username"<?php 
					echo isset($_POST['f']['username']) ? ' value="' . htmlspecialchars($_POST['f']['username']) . '"' : '' ?> /></div>
				<div><label for="password">Kennnwort</label> <input type="password" name="f[password]" id="password" /></div>
			        <div><label for="userid">Userid</label><input type="text" name="f[userid]" id="userid" <?php echo isset($_POST['f']['userid']) ? ' value="' . htmlspecialchars($_POST['f']['userid']) . '"' : '' ?> readonly/></div>	
                        </fieldset>
			<fieldset>
				<div><input type="submit" name="f[submit]" value="Anmelden" /></div>
				<div><input type="submit" name="f[submit]" value="ID" /></div>
			</fieldset>
		</form>
	</body>
</html>
