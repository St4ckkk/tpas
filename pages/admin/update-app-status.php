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
$currentStatus = $_POST['status'];


$currentQuery = $con->prepare("SELECT status, email, first_name, last_name, date, appointment_time FROM appointments WHERE appointment_id = ?");
$currentQuery->bind_param("i", $appointmentId);
$currentQuery->execute();
$currentResult = $currentQuery->get_result();
$currentAppointment = $currentResult->fetch_assoc();

if (!$currentAppointment) {
    echo json_encode(['error' => 'Appointment not found.']);
    exit;
}

if ($newStatus === 'Completed' && $currentAppointment['status'] !== 'Confirmed') {
    echo json_encode(['error' => 'Cannot change status to Completed unless it is Confirmed first.']);
    exit;
}


$query = $con->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
$query->bind_param("si", $newStatus, $appointmentId);

if ($query->execute()) {
    if (in_array($newStatus, ['Confirmed', 'Cancelled', 'Completed', 'Request-denied', 'Request-confirmed'])) {
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

                if ($currentStatus === 'Request-for-reschedule' || $currentStatus === 'Request-for-cancel') {
                    if ($newStatus === 'Request-confirmed') {
                        $subject = 'Request Confirmation';
                        $body = "Your request for rescheduling/cancellation of the appointment scheduled for " . date("F j, Y, g:i A", strtotime($currentAppointment['date'] . ' ' . $currentAppointment['appointment_time'])) . " has been confirmed.";
                    } else if ($newStatus === 'Request-denied') {
                        $subject = 'Request Denial';
                        $body = "We regret to inform you that your request for rescheduling/cancellation of the appointment scheduled for " . date("F j, Y, g:i A", strtotime($currentAppointment['date'] . ' ' . $currentAppointment['appointment_time'])) . " has been denied. Please contact our office for more information or to reschedule.";
                    }
                } else {
                    switch ($newStatus) {
                        case 'Confirmed':
                            $subject = 'Appointment Confirmation';
                            $body = "Your appointment has been confirmed for " . date("F j, Y, g:i A", strtotime($appointmentDetails['date'] . ' ' . $appointmentDetails['appointment_time'])) . ". Please be there 30 minutes before your scheduled time. Late arrivals of more than 30 minutes may result in cancellation.";
                            break;
                        case 'Cancelled':
                            $subject = 'Appointment Cancellation';
                            $body = "We regret to inform you that your appointment scheduled for " . date("F j, Y, g:i A", strtotime($appointmentDetails['date'] . ' ' . $appointmentDetails['appointment_time'])) . " has been cancelled. Please contact our office for more information or to reschedule.";
                            break;
                        case 'Denied':
                            $subject = 'Appointment Denial';
                            $body = "We regret to inform you that your appointment scheduled for " . date("F j, Y, g:i A", strtotime($appointmentDetails['date'] . ' ' . $appointmentDetails['appointment_time'])) . " has been denied. Please contact our office for more information or to reschedule.";
                            break;
                        case 'Completed':
                            $subject = 'Appointment Completion';
                            $body = "Thank you for using our system and visiting us. We value your feedback and invite you to share your experience to help us improve our service. Feel free to contact us with your feedback or any questions you might have.";
                            break;
                        default:
                            $subject = 'Appointment Status Update';
                            $body = "Default message for status update.";
                    }
                }

                $mail->Subject = $subject;
                $mail->Body = $body;

                $mail->send();
                echo json_encode(['success' => 'Status updated successfully and email sent.']);
            } catch (Exception $e) {
                echo json_encode(['error' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
            }
        } else {
            echo json_encode(['error' => 'Failed to fetch appointment details for email.']);
        }
    }
} else {
    echo json_encode(['error' => 'Failed to update status.']);
}
$query->close();
