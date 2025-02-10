<?php
session_start(); 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if(isset($_POST['submitContact']))
{

$fullname = $_POST['full_name'];
$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];



//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication

    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->Username   = 'magayonlibraryhub@gmail.com';                     //SMTP username
    $mail->Password   = 'ljhwawaxkchewqrb';                               //SMTP password

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //ENCRYPTION_SMTPS (port 465) - Enable implicit TLS encryption
    $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('magayonlibraryhub@gmail.com', 'Magayon Hub Admin');
    $mail->addAddress('sandagonangeline9@gmail.com', 'Angeline B. Sandagon');     //Add a recipient


/*  $mail->addAddress('ellen@example.com');               //Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');
*/
    //Attachments
 /*   $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
*/
    //Content
    $mail->isHTML(true);  // Set email format to HTML
    $mail->Subject = 'New Email - Magayon Hub';
    
    // Clean and well-structured body without redundancy
    $mail->Body = '<h3> </h3>
                   <p><strong>Full Name:</strong> ' . htmlspecialchars($fullname) . '</p>
                   <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                   <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
                   <p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>';
    

    if($mail->send())
    {
         $_SESSION['status'] = "Sent!";
        header("Location: {$_SERVER["HTTP_REFERER"]}");
        exit(0);
    }
    else
    {
        $_SESSION['status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: {$_SERVER["HTTP_REFERER"]}");
        exit(0);
    }
   // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    
    //echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
else 
{
    header('Location: index.php');
    exit(0);
}
?>