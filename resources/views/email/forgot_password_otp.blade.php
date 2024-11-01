<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 15px;
            border: 2px dashed #7367f0;
            overflow: hidden;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: #7367f0;
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .header img {
            width: 50px;
            border-radius: 50%;
        }
        .content {
            padding: 30px;
            text-align: center;
        }
        .content h1 {
            color: #333333;
            margin-bottom: 20px;
        }
        .content p {
            color: #666666;
            margin-bottom: 30px;
        }
        .otp-code {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            margin-bottom: 30px;
            color: #7367f0;
        }
        .footer {
            padding: 20px;
            text-align: center;
            background-color: #f2f2f2;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        .footer p {
            color: #999999;
        }
        .social-icons i {
            background: #7367f0;
            height: 35px;
            width: 35px;
            border-radius: 50%;
            line-height: 35px;
            color: white;
            display: inline-block;
            margin: 0 10px;
            transition: transform 0.3s ease;
        }
        .social-icons i:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to SyncXss</h1>
            <h2>Reset Your Password</h2>
        </div>
        <div class="content">
            <h1>Reset your password</h1>
            <p>We received a request to reset your SyncXss account password.</p>
            <p>Please use the OTP below to reset your password:</p>
            <div class="otp-code">{{$otp}}</div>
        </div>
        <div class="footer">
            <p>&copy; 2024 SyncXss. All rights reserved.</p>
            <p>If you did not request a password reset, please ignore this email.</p>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#"><i class="fa-brands fa-linkedin"></i></a>
            </div>
        </div>
    </div>
</body>
</html>
