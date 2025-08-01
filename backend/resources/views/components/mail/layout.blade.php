<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary: #141F78;
            --secondary: #0033AB;
            --accent: #F25100;
            --dark-blue: #141F77;
            --text-light: #FFFFFF;
            --text-dark: #1F2937;
            --gray-light: #F3F4F6;
            --gray-medium: #9CA3AF;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: var(--gray-light);
        }

        .header {
            background-color: var(--primary);
            padding: 20px 0;
            text-align: center;
        }

        .logo {
            max-width: 150px;
            margin: 0 auto;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
        }

        .content {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px;
            position: relative;
        }

        .content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--secondary), var(--accent));
            border-radius: 8px 8px 0 0;
        }

        .greeting {
            font-size: 22px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-light);
        }

        .message {
            color: var(--text-dark);
            font-size: 16px;
            margin-bottom: 25px;
        }

        .closing {
            margin-top: 30px;
            color: var(--text-dark);
            font-size: 16px;
            padding-top: 20px;
            border-top: 2px solid var(--gray-light);
        }

        .footer {
            background-color: var(--primary);
            color: var(--text-light);
            text-align: center;
            padding: 20px;
            font-size: 14px;
            border-radius: 0 0 8px 8px;
        }

        .social-links {
            margin-bottom: 15px;
        }

        .social-links a {
            color: var(--text-light);
            text-decoration: none;
            margin: 0 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--accent);
            color: var(--text-light);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #d94700;
        }

        .highlight {
            color: var(--accent);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <img src="{{ asset('images/logo.png') }}" alt="TekiPlanet" class="logo">
        </div>
    </div>

    <div class="container">
        <div class="content">
            <div class="greeting">
                {{ $greeting }}
            </div>

            <div class="message">
                {{ $slot }}
            </div>

            <div class="closing">
                {{ $closing }}
            </div>
        </div>

        <div class="footer">
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Twitter</a>
                <a href="#">LinkedIn</a>
                <a href="#">Instagram</a>
            </div>
            <div>
                © {{ date('Y') }} TekiPlanet. All rights reserved.
            </div>
            <div style="margin-top: 10px; font-size: 12px;">
                You received this email because you are registered on TekiPlanet.
            </div>
        </div>
    </div>
</body>
</html> 

