<?php 

$servername = 'localhost';
$database = 'sir';
$username = 'root';
$password = '';

$conexao = mysqli_connect($servername, $username, $password, $database);
$mysqli = new mysqli($servername,$username,$password, $database);

?>