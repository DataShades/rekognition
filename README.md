# AWS Rekognition Test
This repository provides a simple example to call the AWS Rekognition service and describe the labels within an image you upload. 

The scripts utilise AWS Rekognition and S3, most can be done within the free tier.

# What you'll need

1. You will need to create an S3 bucket
2. A server with PHP 5.6+ and ImageMagick 

# How to use

1. git clone git@github.com:DataShades/rekognition.git
2. Rename 'include/config.sample.php' to 'include/config.php'
3. Update the variables as required.
4. Ensure that the PHP processes on your server can write to the 'tmp' directory.
5. Make sure ImageMagick is installed and the 'convert' binary is accessible by PHP.
