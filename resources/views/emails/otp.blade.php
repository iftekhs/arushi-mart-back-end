<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Your login code</title>
</head>

<body>
    <p>Your login code is: <strong>{{ $otp }}</strong></p>
    <p>This code expires in {{ $ttl }} minutes and can be used only once.</p>
    <p>If you did not request this, you can ignore this email.</p>
</body>

</html>