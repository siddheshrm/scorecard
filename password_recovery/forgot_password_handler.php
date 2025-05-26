<?php
include '../config.php';

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

date_default_timezone_set('Asia/Kolkata');

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['name'];

        // Email found, generate 100-character reset token
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        // Update user record with reset token and expiry time
        $sql = "UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Send password recovery email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'];
        $mail->Password = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('siddheshmestri2909@gmail.com', 'scorecard.com');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password for Scorecard.com';
        $mail->Body = "<b>Dear $name,</b><br><br>We received a request to reset your password for your account on Scorecard.com. If you made this request, please click the link below to reset your password:<br><a href='http://localhost:8080/scorecard/password_recovery/reset_password.php?token=$token'>Reset Your Password</a><br><br>Please note that this link is valid for 15 minutes. After this period, you will need to request a new password reset.<br><br>Thank you for using Scorecard.com.<br><br><b>Best regards,</b><br>Scorecard.com Team";

        if ($mail->send()) {
            $_SESSION['message'] = 'Password recovery email has been sent. Please check your inbox.';
            $stmt->close();
            $conn->close();
            header('Location: ../index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error in sending email. Mailer Error: ' . $mail->ErrorInfo;
            $stmt->close();
            $conn->close();
            header('Location: ../password_recovery/forgot_password.php');
            exit();
        }
    } else {
        $_SESSION['error'] = 'No account found with that email address.';
        $stmt->close();
        $conn->close();
        header('Location: ../password_recovery/forgot_password.php');
        exit();
    }
}
