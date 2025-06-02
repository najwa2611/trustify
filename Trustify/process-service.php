<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $service = $_POST['service'];

    // Process the form data (e.g., save to database, send email, etc.)
    // ...

    echo "Thank you, $name! Your request for the $service service has been received.";
}
?>
<?php
require 'config.php'; // Include the database configuration

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $service = $_POST['service'];

    // Process the form data (e.g., save to database, send email, etc.)
    $sql = "INSERT INTO service_requests (name, email, service) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $service);

    if ($stmt->execute()) {
        echo "Thank you, $name! Your request for the $service service has been received.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
