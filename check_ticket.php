<?php
// Mengambil data ticket dari request POST
$ticket = $_POST['ticket'];

// Koneksi ke database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'spinwheel';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$sql = "SELECT prize FROM data_spin WHERE ticket = '$ticket'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Jika ticket ditemukan, tandai sebagai pencocokan
    $match = true;
    $row = $result->fetch_assoc();
    $prize = $row['prize'];
    echo $prize;
} else {
    // Jika ticket tidak ditemukan, tandai sebagai tidak cocok
    $match = false;
    // Jika ticket tidak ditemukan, kembalikan pesan error atau hadiah default
    echo "Ticket Not Valid";
}

// Simpan log ke dalam tabel database
$sqlLog = "INSERT INTO log_check_tiket (ticket, match_status) VALUES ('$ticket', '$match')";
if ($conn->query($sqlLog) === FALSE) {
    echo "Error: " . $sqlLog . "<br>" . $conn->error;
}

$conn->close();
?>
