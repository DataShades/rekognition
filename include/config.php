<?php
// variables required by all scripts.

$S3Bucket = "joels-image-bucket"; 	// the bucket where images will be uploaded before processing
$S3BucketRegion = "us-west-2"; 		// the region where your bucket is located
$DynamoDBRegion = "ap-southeast-2"; 	// the region where your DynamoDB is running
$RekognitionRegion = "us-west-2"; 	// the region you run your Rekognition processing in (recommended to be the same as region as the S3 bucket)
$DynamoDBTableName = "images"; 		// the table you've created in DynamoDB to store this information

// less global variables (but still important)

$uploadSizeLimit = "3000000"; 		// maximum allowed upload size in bytes
?>
