<?php
/* Mysql */
$host = 'localhost';
$usuario = 'root';
$schemaBase = 'teste';
$password = '';

$con = mysqlI_connect($host, $usuario, $password, $schemaBase);

$sql = "SET NAMES 'utf8'";
mysqli_query($con, $sql);

$sql = 'SET character_set_connection=utf8';
mysqli_query($con, $sql);

$sql = 'SET character_set_client=utf8';
mysqli_query($con, $sql);

$sql = 'SET character_set_results=utf8';
$res = mysqli_query($con, $sql);


