<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Collect form data
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$mobile   = $_POST['mobile'] ?? '';
$hasKids  = $_POST['hasKids'] ?? 'no';
$address  = $_POST['address'] ?? '';

// Generate user ID and password
$userid   = "CK" . rand(1000, 9999);
$password = substr(str_shuffle("ABCDEFGHJKMNPQRSTUVWXYZ23456789"), 0, 6);

// Check for duplicates
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
$checkStmt->bind_param("ss", $email, $mobile);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $conn->close();
    showMessage("❌ This Email or Mobile number is already registered!", true);
    exit;
}

// Insert into users
$stmt = $conn->prepare("INSERT INTO users (name, email, mobile, has_kids, address, userid, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $email, $mobile, $hasKids, $address, $userid, $password);
$stmt->execute();
$user_id = $stmt->insert_id;

// Insert kids (if available)
if ($hasKids === "yes" && isset($_POST['kid_names'], $_POST['kid_ages'])) {
    $kid_names = $_POST['kid_names'];
    $kid_ages  = $_POST['kid_ages'];

    for ($i = 0; $i < count($kid_names); $i++) {
        $kid_stmt = $conn->prepare("INSERT INTO kids (user_id, kid_name, kid_age) VALUES (?, ?, ?)");
        $kid_stmt->bind_param("isi", $user_id, $kid_names[$i], $kid_ages[$i]);
        $kid_stmt->execute();
    }
}

// Send Email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'tnhappykids1@gmail.com';
    $mail->Password   = 'qoru vmop lczl ezsj'; // App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('tnhappykids1@gmail.com', 'TN Happy Kids');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Welcome to TN Happy Kids!";
$mail->Body = "
<div style='background:#f0faff;padding:20px;border-radius:12px;font-family:Arial,sans-serif;color:#333;'>
  <h2 style='color:#0099cc;'>Hello $name! 🐥</h2>
  <p>We are thrilled to have you and your family join <strong>TN Happy Kids School</strong> (Save the Chittu Kuruvi!).</p>

  <p style='font-size:18px;color:#008800;'><strong>🪺 You will get the Chittukuruvi Nest within 5 days!</strong></p>

  <div style='margin:15px 0;padding:10px;background:#e0ffe0;border-left:5px solid #4CAF50;line-height:1.8em;'>
    <strong>🆔 User ID:</strong> <code>$userid</code><br>
    <strong>🔐 Password:</strong> <code>$password</code>
  </div>

  <p>
    🔗 <a href='http://localhost:8080/registration-form/nest.html' style='color:#0077cc;font-weight:bold;text-decoration:none;'>
      👉 Click here to enter your Nest
    </a>
  </p>

  <hr style='margin:20px 0;'>

  <p>📬 <strong>Address:</strong> $address</p>
  <p>📞 <strong>Mobile:</strong> $mobile</p>

  <div style='margin-top:30px;padding:15px;border-radius:10px;background:#fff9e6;border:1px dashed #f4b400;'>
    <h3 style='color:#d35400;'>🎁 Special Giveaway Alert!</h3>
    <p style='font-size:16px;line-height:1.6em;'>
      📸 After receiving your <strong>Chitu Kuruvi Nest</strong>,<br>
      🏡 Place it in front of your house and start taking <strong>creative photos</strong> for 30 days!<br><br>
      👶 If you have kids, include them in fun and beautiful ways!<br><br>
      🎉 Those who upload daily for 30 days will get a chance to <strong>WIN an iPhone 16 Plus!</strong>
    </p>
    <hr style='margin:10px 0;'>
    <p style='color:#444;font-size:15px;line-height:1.5em;'>
      📢 <strong>Tamil Version:</strong><br>
      🪺 உங்கள் சிட்டு குருவி கூடு கிடைத்த 5 நாட்களுக்கு பிறகு,<br>
      🏠 அதை உங்கள் வீட்டின் முன்பாக வைத்துவிட்டு,<br>
      📸 <strong>தினமும் படைப்பாற்றலோடு</strong> புகைப்படங்களை 30 நாட்கள் எடுத்து பதிவேற்றுங்கள்!<br><br>
      👶 வீட்டில் பிள்ளைகள் இருந்தால், அவர்களையும் சேர்த்து அழகான படங்களை எடுத்தால் சிறப்பு!<br><br>
      🎁 <strong>iPhone 16 Plus</strong> வெல்ல வாய்ப்பு உங்களுக்கே!
    </p>
  </div>

  <div style='margin-top:25px;font-size:15px;color:#333;line-height:1.6em;'>
    📞 <strong>If you have any queries, contact:</strong> <a href='tel:9514900070' style='color:#0077cc;'>9514900070</a><br>
    🏫 <strong>Our TN Happy Kids Play School branches:</strong><br>
    • Pollachi<br>
    • Coimbatore<br>
    • Tambaram<br>
    • Kolathur<br>
    • Erode<br>
    • Tirupur
  </div>

  <p style='font-size:12px;color:#999;margin-top:30px;'>
    This is an auto-generated confirmation email. Please do not reply.
  </p>
</div>
";


    $mail->send();
    showMessage("✅ Registered successfully! Your User ID and Password have been sent to <b>$email</b>");
} catch (Exception $e) {
    showMessage("❌ Email could not be sent. Error: {$mail->ErrorInfo}", true);
}

$conn->close();

// Helper to show confirmation page
function showMessage($message, $isError = false) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Registration Status</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body {
      height: 100%;
      width: 100%;
      font-family: 'Segoe UI', sans-serif;
      background: url('image/maxresdefault.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }
    body::before {
      content: '';
      position: absolute;
      inset: 0;
      backdrop-filter: blur(6px);
      background-color: rgba(255, 255, 255, 0.1);
      z-index: 0;
    }
    .message-box {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.85);
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      text-align: center;
      max-width: 400px;
      animation: fadeIn 1s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .message-box h2 {
      color: <?= $isError ? '#cc0000' : '#2e7d32' ?>;
      margin-bottom: 15px;
    }
    .message-box p {
      font-size: 16px;
      color: #333;
    }
    @media (max-width: 600px) {
      .message-box { width: 90%; padding: 20px; }
    }
  </style>
</head>
<body>
  <div class="message-box">
    <h2><?= $isError ? "⚠️ Error" : "🎉 TN Happy Kids" ?></h2>
    <p><?= $message ?></p>
  </div>
</body>
</html>
<?php
  exit;
}
?>
