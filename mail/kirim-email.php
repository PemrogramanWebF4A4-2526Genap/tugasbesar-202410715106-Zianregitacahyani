<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';

function kirimEmail($tujuan, $nama, $subjek, $isi)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USERNAME;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = EMAIL_PORT;

        // Pengirim
        $mail->setFrom(EMAIL_USERNAME, EMAIL_FROM_NAME);

        // Penerima
        $mail->addAddress($tujuan, $nama);

        // Isi email
        $mail->isHTML(true);
        $mail->Subject = $subjek;
        $mail->Body = $isi;

        $mail->send();

        return true;
    } catch (Exception $e) {
        die("Email gagal dikirim: " . $mail->ErrorInfo);
    }
}
