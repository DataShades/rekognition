<?php
// variables required by all scripts.

$S3Bucket = "";			 	// the name of the S3 bucket where images will be uploaded before processing.
$S3StorageClass = "REDUCED_REDUNDANCY"; // Use "STANDARD" or "REDUCED_REDUNDANCY" to define the storage class for the images we upload.
$S3BucketRegion = ""; 			// the AWS region where your bucket is located (i.e. "us-west-2")
$RekognitionRegion = ""; 		// the AWS region you run your Rekognition processing in (recommended to be the same as region as the S3 bucket above)

// less global variables (but still important)

$uploadSizeLimit = "3000000"; 		// maximum allowed upload size in bytes
?>
