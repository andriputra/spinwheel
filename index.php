<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diamond Lucky Spin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</head>
<body>
<div class="container" id="container">
    <?php
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'spinwheel';
    
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, ticket, prize, stopper_code FROM data_spin";
    $result = $conn  ->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo '<div class="field-wheel">' . $row["prize"] . '</div>';
        }
    } else {
        echo "0 results";
    }
    ?>
</div>
<div class="stoper"></div>
<span class="mid"></span>
<div class="form-container">
    <input type="text" id="keywordInput" placeholder="Enter your coupon" class="form-control">
</div>
<button id="spin" class="btn">Spin</button>

<!-- Modal Message -->
<div class="modal" id="InfoWin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="content-prize text-center">
                    <i class="fa-solid fa-trophy"></i>
                    <h2>Congratulation</h2>
                    <p id="prizeMessage"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Error -->
<div class="modal" id="errorModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="content-prize text-center">
            <i class="fa-solid fa-circle-exclamation"></i>
            <h2>Sorry</h2>
            <p id="errorMessage"></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$conn->close();
?>

<script>
$(document).ready(function(){
    function showErrorModal(message) {
        $('#errorMessage').text(message); 
        var modal = new bootstrap.Modal(document.getElementById('errorModal')); 
        modal.show(); 
    }

    $('#spin').click(function(){
        var ticket = $('#keywordInput').val();

        if(ticket.trim() === "") {
            showErrorModal("You have not entered the ticket code.");
            return; 
        }

        var lastTicketTime = localStorage.getItem('lastTicketTime');
        if (lastTicketTime) {
            var currentTime = Date.now();
            var timeDifference = currentTime - lastTicketTime;
            var minimumTimeDifference = 60000; 
            if (timeDifference < minimumTimeDifference) {
                showErrorModal("Sorry, your ticket cannot be used anymore. Please try again later.");
                return;
            }
        }

        spinWheel(function(){
            $.ajax({
                type: 'POST',
                url: 'check_ticket.php',
                data: {ticket: ticket},
                success: function(response){
                    var responseData = JSON.parse(response);
                    var prize = responseData.prize;  // Mengambil nilai dari kunci 'prize'
                    var stopper_code = responseData.stopper_code;  // Mengambil nilai dari kunci 'stopper_code'
                    var stopPosition = calculateStopPosition(ticket);
                    setTimeout(function() {
                        stopWheel(stopPosition, prize);
                    }, 3000); 
                },
                error: function() {
                    showErrorModal("Failed to check ticket. Please try again later."); // Tampilkan pesan jika terjadi kesalahan
                }
            });
            localStorage.setItem('lastTicketTime', Date.now());
        });
    })
    
    function spinWheel(callback, prize, stopper_code) {
        var totalRotationTime = 3000; 
        var startTime = Date.now();
        var rotationSpeed = 5; 
        var initialRotation = Math.random() * 360; 
        var targetRotation = 360 * 5 + initialRotation + (stopper_code * (360 / 6)); // Menggunakan stopperCode untuk menentukan targetRotation

        var currentRotation = initialRotation; 

        var wheelInterval = setInterval(function() {
            var elapsedTime = Date.now() - startTime;
            if (elapsedTime >= totalRotationTime) {
                clearInterval(wheelInterval);
                callback(); 
            } else {
                var progress = (elapsedTime / totalRotationTime); 
                currentRotation += rotationSpeed; 
                if (currentRotation >= targetRotation) {
                    currentRotation = targetRotation; 
                }
                $('#container').css('transform', 'rotate(' + currentRotation + 'deg)');
            }
        }, rotationSpeed); 
    }



    function stopWheel(stopPosition, prize){
        $('#prizeMessage').html('You won <strong>' + prize + '</strong> To claim your prize, please screen shoot this screen and send to admin');
        $('#InfoWin').modal('show');
    }
    
    function calculateStopPosition(ticket) {
        var hash = 0;
        for (var i = 0; i < ticket.length; i++) {
            hash += ticket.charCodeAt(i);
        }
        var stopPosition = hash % 6;
        return stopPosition;
    }
});
</script>

</body>
</html>
