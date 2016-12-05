<html>
<head>
</head>
<body>
<h2>AWS Rekognition Test</h2>
Click <a href="upload-form.php">here</a> to upload an image.
<?php
require 'aws-autoloader.php';
require 'include/config.php';

use Aws\DynamoDb\DynamoDbClient;

$dynamoDB = DynamoDBClient::factory(array(
	'region'        => $DynamoDBRegion,
	'version'       => 'latest'
));

// run a describeTable to get the number of items
// note, this isn't live data and is a bit of a kludge at present
$totalItems = $dynamoDB->describeTable([
       	'TableName'     => $DynamoDBTableName
]);

echo "<p>So far there have been <strong>" . $totalItems['Table']['ItemCount'] . "</strong> images uploaded and scanned via Rekognition.</p>";
?>

</body>
</html>
