<?php

use App\Models\Usuarios;

if (isset($_POST)) {
    require_once '../app/Models/Usuarios.php';
    $usuario = Usuarios::getInstancia();
    $user = $_POST['user'];
    $password = $_POST['password'];
    $userData = $usuario->login($user, $password);
    if ($usuario->mensaje == 'Usuario encontrado') {
        echo 'Usuario encontrado';
        var_dump($userData);
    } else {
        echo 'Usuario no encontrado';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <h1>Estoy en el proyecto de contactos</h1>
        <form method="post">
            <input type="text" name="user" placeholder="user">
            <input type="text" name="password" placeholder="password">
            <input type="submit" value="Enviar">
        </form>
    </body>
</html>