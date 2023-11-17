<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_healthcaare_conn = "localhost";
$database_healthcaare_conn = "nena";
$username_healthcaare_conn = "root";
$password_healthcaare_conn = "";
$healthcaare_conn = mysqli_connect($hostname_healthcaare_conn, $username_healthcaare_conn, $password_healthcaare_conn, $database_healthcaare_conn);

if (!$healthcaare_conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>