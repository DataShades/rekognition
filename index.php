<html>
<head>
</head>
<body>
<h2>AWS Rekognition Test</h2>
Click <a href="upload-form.php">here</a> to upload an image.
<?php
require 'include/config.php';

// instead of using DynamoDB to get the total items (lag by 6 hours) we'll
// use the local count.txt file
$count = file_get_contents('tmp/count.txt');
echo "<p>So far <strong>$count</strong> images have been uploaded and scanned via Rekognition.</p>";
?>

</body>
</html>
