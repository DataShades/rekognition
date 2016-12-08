<html>
<head>
</head>
<body>
<h2>AWS Rekognition Test</h2>
Click <a href="upload-form.php">here</a> to upload an image.
<?php
require 'include/config.php';
require 'aws-autoloader.php';

use Aws\DynamoDb\DynamoDbClient;

// instead of using DynamoDB to get the total items (lag by 6 hours) we'll
// use the local count.txt file
$count = file_get_contents('tmp/count.txt');
echo "<p>So far <strong>$count</strong> images have been uploaded and scanned via Rekognition.</p>";

/* Work in progress
echo "<h3>Recent uploads</h3>";

// display a list of recent uploads
$numDays = 1;
$currentDate = date("Ymd");
$dayRange = $currentDate - $numDays;

$dynamoDB = DynamoDBClient::factory(array(
	'region'        => $DynamoDBRegion,
	'version'       => 'latest'
));

$result = $dynamoDB->batchGetItem([
	'RequestItems' => [
		'

echo $result;
*/

?>

</body>
</html>
