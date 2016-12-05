<?php
// variables required by all scripts.

$S3Bucket = ""; 	// the bucket where images will be uploaded before processing
$S3BucketRegion = ""; 		// the region where your bucket is located
$DynamoDBRegion = ""; 	// the region where your DynamoDB is running
$RekognitionRegion = ""; 	// the region you run your Rekognition processing in (recommended to be the same as region as the S3 bucket)
$DynamoDBTableName = ""; 		// the table you've created in DynamoDB to store this information

// less global variables (but still important)

$uploadSizeLimit = "3000000"; 		// maximum allowed upload size in bytes
?>
