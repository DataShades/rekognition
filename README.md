# AWS Rekognition Test
This repository provides a simple example to call the AWS Rekognition service and describe the labels within an image you upload. 

The scripts utilise AWS Rekognition and S3, most can be done within the free tier.

# What you'll need

1. An S3 bucket
2. An instance with IAM permissions:
   - Either via an IAM role attached to the instance (the simplest option), or;
   - Access/Secret keys in environment variables or saved within an AWS credentials file in your HOME directory
   - For more information, see the [PHP SDK documentation](http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/credentials.html#credential-profiles).
3. IAM permissions should allow:
   - Put/Get/List objects within the S3 bucket.
   - Access to Rekognition functions
4. A server with PHP 5.6+ and ImageMagick 

# How to use

1. git clone git@github.com:DataShades/rekognition.git
2. Rename 'include/config.sample.php' to 'include/config.php'
3. Update the variables as required.
4. Ensure that the PHP processes on your server can write to the 'tmp' directory.
5. Make sure ImageMagick is installed and the 'convert' binary is accessible by PHP.
