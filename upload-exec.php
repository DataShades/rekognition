<html>
<head>
</head>
<body>

<?php
require 'aws-autoloader.php';
require 'include/config.php';

use Aws\S3\S3Client;
use Aws\Rekognition\RekognitionClient;

// variables specific to this script
$localUploadDir = "tmp/";
$fileName = basename($_FILES["fileToUpload"]["name"]);
$targetFile = $localUploadDir . $fileName;
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
	
	// upload success. before we do anything else, let's make the image smaller and more manageable.
	$scaledFile = "scaled_" . $fileName;
	$resizeCmd = "convert $targetFile -resize 1024x1024\> tmp/" . $scaledFile;
        exec($resizeCmd, $output, $return);

	// now let's send the original to S3 for processing
	// we send the original as Rekognition needs as much detail as possible
	$bucket = "joel-image-bucket";
	$keyname = $fileName;
	$filepath = $localUploadDir . $fileName;
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
	
	// now we output the labels that Rekognition returned in a basic HTML table
	echo '<p><table border="1"><tr><th>Label</th><th>Confidence</th></tr>';	
	$i = 0;
	foreach($labels['Labels'] as $row) {
		echo '<tr>';
		echo '<td>' . $row['Name'] . '</td>';
		echo '<td>' . round($row['Confidence'], 2) . '%</td>';
		echo '</tr>';

		// send each label over 85% to an array for later
		if(round($row['Confidence'], 2) >= '85') {
			if($i == 0) {
				// declare the array on first occurrance but not again
				$confidentLabels = array();
			}
			array_push($confidentLabels, $row['Name'] . ': ' . round($row['Confidence'], 2) . '\%');
			$i++;
		}
	}
	echo '</table></p>';

	// check to see if we didn't get any labels over 85% and display a message to that effect on the image
	if(!isset($confidentLabels)) {
		$confidentLabelsCmd = 'Rekognition did not have 85\% or higher confidence in any label for this image.';
	} else {
		// get the labels from the array and put them into a string for image processing
	        $confidentLabelsCmd = implode(" | ", $confidentLabels);
	}

	// we're currently storing the number of uploads in a local file
        // instead of DynamoDB due to the inability to easily "count rows".
        // it's time to increment that value.
        $file = 'tmp/count.txt';
        $value = file_get_contents($file);
        $value = $value + 1;
        file_put_contents($file, $value);

	// get the label string we retrieved and annotate the local scaled image
	$labelledFile = 'lbl_' . $fileName;
	$convert = "convert tmp/$scaledFile -pointsize 14 -gravity North -background Plum -splice 0x20 -annotate +0+4 '$confidentLabelsCmd' tmp/$labelledFile";
	exec($convert, $output, $return);

	// now we send the annotated image to S3
	$keyname = $labelledFile;
	$filepath = 'tmp/' . $labelledFile;
        $result = $s3->putObject(array(
                'Bucket'        => $S3Bucket,
                'Key'           => 'tagged-images/' . $keyname,
                'SourceFile'    => $filepath,
                'ContentType'   => $mime,
                'ACL'           => 'public-read',
                'StorageClass'  => $S3StorageClass,
                'Metadata'      => array(
                        'string'        => 'string'
                )
                ));

	// now we can remove the original and labelled file because they're on S3
	unlink('tmp/' . $fileName);
	unlink('tmp/' . $labelledFile);
	unlink('tmp/' . $scaledFile);

	// now we display the final tagged image from S3.
	$url = $result['ObjectURL'];
	echo '<img src="' . $url . '" />';
	
	// Generate a link to tweet the image
	$tweetURL = 'share?text=AWS Rekognition is pretty awesome, see what it found in my image:&url=' . $url;
	$tweetURL = str_replace(" ", "+", $tweetURL);


	echo '<h3>Share on Twitter?</h3>';
	echo '<p>Now that you have had your image labelled by Rekognition, why not share it with your followers? Click <a href="http://twitter.com/' . $tweetURL . '">here</a> to compose a tweet for this image.</p>';
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
<p>Click <a href="upload-form.php">here</a> to upload another image.</p>
</body>
</html>
