# AWS Rekognition Test
This repository provides a simple example to call the AWS Rekognition service and describe the labels within an image you upload. 

The scripts utilise DynamoDB, S3 and Rekognition and all can be done within the free tier.

# What you'll need
1. You should create a DynamoDB table with the primary partition key as "name" (string)
2. You will need to create an S3 bucket

# How to use
Once you've got the files locally, you'll need to rename the 'include/config.sample.php' file to config.php and update the variables as required.

# Caveats
You will need to manually create a directory called "tmp" where files will be uploaded to prior to being pushed to S3. I will add this directory to the repository in a future commit.
