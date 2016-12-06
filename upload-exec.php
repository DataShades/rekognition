<html>
<head>
</head>
<body>

<?php
require 'aws-autoloader.php';
require 'include/config.php';

use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;
use Aws\DynamoDb\DynamoDbClient;

// variables specific to this script
$localUploadDir = "tmp/";

$targetFile = $localUploadDir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

// see if the image file actualy an image
if(isset($_POST["submit"])) {
	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if($check !== false) {
		$uploadOk = 1;
	} else {
		echo "The file is not an image.";
		$uploadOk = 0;
	}
}

// check to see if file already exists 
if (file_exists($targetFile)) {
	echo "Sorry, the file already exists.";
	$uploadOk = 0;
}

// make sure the uploaded file is within the size limit
if ($_FILES["fileToUpload"]["size"] > $uploadSizeLimit) {
	echo "Sorry, the file you uploaded exceeds the maximum allowed size of $uploadSizeLimit bytes.";
	$uploadOk = 0;
}

// only allow specific file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
	echo "We only allow JPG, JPEG, PNG & GIF files to be uploaded.";
	$uploadOk = 0;
}

// see if the $uploadOk variable has been set to 0 by an error
if ($uploadOk == 0) {
	echo "<p>Please <a href='upload-form.php'>go back</a> and retry your upload.</p>";
	exit();
// no error was encountered, try to upload file
} else {
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
	
	// upload success, time to send the file to S3 for processing by Rekognition
	$bucket = "joel-image-bucket";
	$keyname = $_FILES["fileToUpload"]["name"];
	$filepath = $localUploadDir . basename( $_FILES["fileToUpload"]["name"]);
	$mime = $check['mime'];

	// instantiate S3 class
	$s3 = S3Client::factory(array(
		'region' 	=> $S3BucketRegion,
		'version'	=> 'latest'
	));

	$result = $s3->putObject(array(
		'Bucket'	=> $S3Bucket,
		'Key'		=> $keyname,
		'SourceFile'	=> $filepath,
		'ContentType'	=> $mime,
		'ACL'		=> 'public-read',
		'StorageClass'	=> $S3StorageClass,
		'Metadata'	=> array(
			'string'	=> 'string'
		)
		));

	echo '<h3>Result</h3>';
	echo '<p><img src="' . $result['ObjectURL'] . '" width="400" /></p>';

	// delete the local copy of the upload, now that it's on S3
	unlink($filepath);

	$rekognition = RekognitionClient::factory(array(
		'region'	=> $RekognitionRegion,
		'version'	=> 'latest'
	));

	// now we need to run rekognition over this
	$labels = $rekognition->detectLabels([
		'Image'		=> [
			'S3Object'	=> [
				'Bucket'	=> $S3Bucket,
				'Name'		=> $keyname,
				],
			],
	]);
	
	// convert and store just the labels so we can store them in DynamoDB
	$json = json_encode($labels['Labels']);
	// also get the current time to allow some rudimentary sorting later
	$date = date("Ymd");
	
	// lets do some DynamoDB now...
	$dynamoDB = DynamoDBClient::factory(array(
		'region'	=> $DynamoDBRegion,
		'version'	=> 'latest'
	));

	$metadata = $dynamoDB->putItem([
		'TableName'	=> $DynamoDBTableName,
		'Item'		=> array(
			'name'		=> array('S' => $keyname),
			'json'		=> array('S' => $json),
			'date'		=> array('S' => $date)
		),
	]);

	// now we output the labels that Rekognition returned in a basic HTML table
	echo '<p><table border="1"><tr><th>Label</th><th>Confidence</th></tr>';	
	foreach($labels['Labels'] as $row) {
		echo '<tr>';
		echo '<td>' . $row['Name'] . '</td>';
		echo '<td>' . $row['Confidence'] . '%</td>';
		echo '</tr>';
	}
	echo '</table></p>';

	// we're currently storing the number of uploads in a local file
        // instead of DynamoDB due to the inability to easily "count rows".
        // it's time to increment that value.
        $file = 'tmp/count.txt';
        $value = file_get_contents($file);
        $value = $value + 1;
        file_put_contents($file, $value);

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
<p>Click <a href="upload-form.php">here</a> to upload another image.</p>
</body>
</html>
