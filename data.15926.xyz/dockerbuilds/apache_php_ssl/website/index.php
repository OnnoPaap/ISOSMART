<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #007BFF;
        }
        p {
            font-size: 1.2em;
        }
        .info {
            margin-top: 20px;
            padding: 10px;
            background-color: #e7f3fe;
            border: 1px solid #b3d4fc;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>PHP Test Page</h1>
    <p>If you see this page, your PHP server is working correctly!</p>
    <div class="info">
        <?php
            echo "Current PHP version: " . phpversion();
        ?>
    </div>
</body>
</html>
