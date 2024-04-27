<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['doctorSession'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

if (!isset($_POST['appointment_id'], $_POST['new_status'])) {
    echo json_encode(['error' => 'Data missing.']);
    exit();
}

$appointmentId = $_POST['appointment_id'];
$newStatus = $_POST['new_status'];

if (!in_array($newStatus, ['Confirmed', 'Cancelled', 'Denied'])) {
    echo json_encode(['error' => 'Invalid status value.']);
    exit();
}

$query = $con->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
$query->bind_param("si", $newStatus, $appointmentId);

if ($query->execute()) {
    // Only send an email if the status is Confirmed, Cancelled, or Denied
    if (in_array($newStatus, ['Confirmed', 'Cancelled', 'Denied'])) {
        $query = $con->prepare("SELECT email, first_name, last_name, date, appointment_time FROM appointments WHERE appointment_id = ?");
        $query->bind_param("i", $appointmentId);
        $query->execute();
        $result = $query->get_result();
        $appointmentDetails = $result->fetch_assoc();

        if ($appointmentDetails) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tpas052202@gmail.com';
                $mail->Password = 'ailamnlsomhhtglb';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('tpas052202@gmail.com', 'TB Patient\'s Appointment System');
                $mail->addAddress($appointmentDetails['email'], $appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name']);
                $mail->isHTML(true);
                $mail->Subject = 'Appointment Status Update';

                // Customize the body based on the status
                if ($newStatus === 'Confirmed') {
                    $mail->Body = "Hello " . $appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name'] .
                        ", your appointment has been confirmed for " . date("F j, Y, g:i A", strtotime($appointmentDetails['date'] . ' ' . $appointmentDetails['appointment_time'])) .
                        ". <br>Please be there 30 minutes before your scheduled time. <br>Late arrivals of more than 30 minutes may result in cancellation.";
                } elseif ($newStatus === 'Cancelled') {
                    $mail->Body = "Hello " . $appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name'] .
                        ", we regret to inform you that your appointment scheduled for " . date("F j, Y, g:i A", strtotime($appointmentDetails['date'] . ' ' . $appointmentDetails['appointment_time'])) .
                        " has been cancelled. <br>Please contact our office for more information or to reschedule.";
                }

                $mail->send();
                echo json_encode(['success' => 'Status updated successfully and email sent.']);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
            }
        } else {
            echo json_encode(['error' => 'Failed to fetch appointment details for email.']);
        }
    } else {
        echo json_encode(['success' => 'Status updated successfully, no email needed.']);
    }
} else {
    echo json_encode(['error' => 'Failed to update status.']);
}
$query->close();
