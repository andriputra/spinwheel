<?php
// Mengambil data ticket dari request POST
if(isset($_POST['ticket'])) {
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

    $sql = "SELECT ticket, prize, stopper_code FROM data_spin WHERE ticket = '$ticket'";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            // Jika ticket ditemukan, tandai sebagai pencocokan
            $match = true;
            $row = $result->fetch_assoc();
            $prize = $row['prize'];
            $stopper_code = $row['stopper_code'];
            $response = array('prize' => $prize, 'stopper_code' => $stopper_code);
            echo json_encode($response);
        } else {
            // Jika ticket tidak ditemukan, tandai sebagai tidak cocok
            $match = false;
            $error_message = "Ticket Not Valid";
            $response = array('error' => $error_message);
            echo json_encode($response);
            exit; // Keluar dari script jika tiket tidak valid
        }
    } else {
        echo "Error: " . $conn->error;
    }

    // Simpan log ke dalam tabel database
    if (!isset($match)) {
        $match = false;
    }
    $sqlLog = "INSERT INTO log_check_tiket (ticket, match_status) VALUES ('$ticket', '$match')";
    if ($conn->query($sqlLog) === FALSE) {
        echo "Error: " . $sqlLog . "<br>" . $conn->error;
    }

    $conn->close();
} else {
    echo "Ticket parameter is missing.";
}
?>
