# Deploying a PHP Application on AWS EC2 with RDS MySQL Database

This tutorial will guide you through the process of deploying a PHP application on an Ubuntu EC2 instance that connects to an RDS MySQL database. You will learn how to set up the environment, configure dependencies, and establish a connection between the EC2 instance and the RDS MySQL database.

## Requirements

Before you begin, ensure you have the following requirements:

- An AWS account with access to launch EC2 instances and configure security groups.
- Basic knowledge of AWS services and EC2 instances.
- SSH access to your EC2 instance using a key pair.
- Basic knowledge of Git and GitHub.
- A RDS MySQL database - see sample SQL queries at `sampleDB.sql`

## Setup

Step 1: Launch an Ubuntu EC2 instance in a public subnet.

Make sure that the security group attached to your instance allows public access (i.e., 0.0.0.0/0) to ports 443 and 80. Additionally, allow inbound access to port 22 from your IP address.

Use the following User Data while launching the instance.

```bash
#!/bin/bash
sudo apt update -y
sudo apt upgrade -y
sudo apt install -y awscli git apache2 php php-mysql php-zip unzip php-simplexml
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

Step 2: Attach a role that allows your instance to consume Secret Manager resources.

Navigate to the AWS EC2 Console (https://us-east-1.console.aws.amazon.com/ec2/home), selecting "Instances" from the left-hand menu, choosing your instance, clicking "Actions", selecting "Security", clicking "Modify IAM role", and selecting a role that provide access to AWS Secret Manager. Alternatively, you can apply the role by executing the following command in the AWS CLI.

```bash
aws ec2 associate-iam-instance-profile --instance-id <instance_id> --iam-instance-profile '{"Name"="<iam_role_name>}'
```

Step 3: SSH to your Instance.

```bash
ssh ec2-user@INSTANCE-IP -i YourKey.pem
```

Step 4: Check if your dependencies are correctelly installed.

```bash
aws --version
git --version
apache2 -v
php --version
php -m # php extension
composer --version
```

Step 5: Set a basic AWS config to inform your default region.

```bash
aws configure
# AWS Access Key ID [None]:
# AWS Secret Access Key [None]:
# Default region name [None]: YOUR-DEFAULT-REGION (e.g. us-east-1)
# Default output format [None]: json
```

Test networking communication from EC2 to RDS.

```bash
telnet YOUR-RDS-ENDPOINT 3306
```

Step 6: Create a Folder.

Set up a folder to store your project by running the following commands.

```bash
mkdir -p ~/myApps
cd ~/myApps
```

Step 7: Clone the Git Repository and Configure Git Credentials.

Before cloning the repository, you may want to configure your Git credentials.

```bash
git config --global user.email "your_email@example.com"
git config --global user.name "Your Name"
```

Then, clone the repository by running the following command.

```bash
cd ~/myApps
git clone https://github.com/biagolini/PhpAWSRdsConnection.git

```

Change the permissions of the PhpAWSRdsConnection directory and its contents.

```bash
# Read, write, and execute permissions for all files and directories in your project.  Makes the project folder accessible to everyone (insecure, but useful for debugging purposes)
# sudo chmod -R 777 PhpAWSRdsConnection
# Read, write, and execute permissions for the owner, read and execute permissions for the group and others
sudo chmod -R 755 PhpAWSRdsConnection
```

Create a symbolic link to be used as a reference to the site that will be opened.

```bash
cd ~/myApps
ln -s PhpAWSRdsConnection php-rds-test
```

Step 8: Setup your php dependencies.

Before starting to add our dependencies, keep in mind how dependencies will be added to our project. The path/files used in this tutorial will be:

```
/home/ubuntu/myApps/php-rds-test/composer.json
/home/ubuntu/myApps/php-rds-test/public/index.php
/home/ubuntu/myApps/php-rds-test/public/style.css
/home/ubuntu/myApps/php-rds-test/vendor
```

- composer.json: This file contains the project's metadata, such as its name, description, and dependencies. It is used by Composer to manage and install the required PHP packages for your project.

- index.php: This file serves as the main entry point for your PHP application. When a user accesses the application, this file is executed, and it is responsible for rendering the HTML content and handling any server-side logic.

- style.css: This file contains the CSS styles for your PHP application. It is used to style the HTML content generated by the index.php file and provides a consistent look and feel for your application.

- vendor: This directory is generated by Composer and contains the installed PHP packages defined in the composer.json file. These packages are the dependencies required for your project to function correctly. The vendor directory should not be modified directly, as Composer manages its contents.

Make sure that you are in the project folder, and Initialize the Composer configuration file:

```bash
cd ~/myApps/php-rds-test
composer init
```

Provide the necessary information and choose the appropriate package when prompted:

```
Package name (<vendor>/<name>): "company/tutorial-project"
Description: "A simple tutorial project to learn AWS SDK for PHP."
Author:  Name Developer <developer@company.com>
Minimum Stability []:
Package Type (e.g. library, project, metapackage, composer-plugin) []: project
License []:

Would you like to define your dependencies (require) interactively [yes]? Yes
aws-sdk-php

Found 15 packages matching aws-sdk-php

   [0] aws/aws-sdk-php
   [1] aws/aws-sdk-php-laravel
   [2] aws/aws-sdk-php-symfony
   [3] aws/aws-sdk-php-zf2
   [4] aws/aws-sdk-php-resources
   [5] aws/aws-sdk-php-silex
   [6] thephalcons/amazon-webservices-bundle
   [7] aws/aws-sdk-php-v3-bridge
   [8] cybernox/amazon-webservices-bundle
   [9] klinson/aws-s3-minio
  [10] sunaoka/laravel-aws-sdk-php
  [11] lalcebo/aws-sdk-php-params
  [12] misantron/dynamite
  [13] alexeitaber/aws-s3-minio
  [14] tanghengzhi/aws-sdk-php-laravel

Choose option [0]
Enter package # to add, or the complete package name if it is not listed: 0
Enter the version constraint to require (or leave blank to use the latest version):
Using version ^3.269 for aws/aws-sdk-php

Would you like to define your dev dependencies (require-dev) interactively [yes]? Follow the same procedure as before
```

If you forget to add the dependencies during the initialization process, you can install them using the following commands.

```bash
composer require aws/aws-sdk-php
composer install
```

Step 9: Configure the Web Server.

Navigate to the project directory and obtain the path to your PHP project:

```bash
cd ~/myApps/php-rds-test/
pwd
# This will display the path to your PHP project, for example: /home/ubuntu/myApps/php-rds-test
```

Edit the Apache configuration file by running the following command:

```bash

sudo nano /etc/apache2/sites-available/myapp.conf
```

Paste the following content into the configuration file, replacing /home/your-username/myApps/php-rds-test with the actual path to your PHP project:

```
<VirtualHost *:80>
    ServerName php-rds-test.local
    ServerAdmin webmaster@localhost
    DocumentRoot /home/ubuntu/myApps/php-rds-test/public
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
    <Directory /home/ubuntu/myApps/php-rds-test/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Next, edit the default Apache configuration file:

```ssh
sudo nano /etc/apache2/sites-available/000-default.conf
```

Update the `DocumentRoot` line to point to your application's public directory, and add the `<Directory>` block to allow access to the public directory. Replace `/home/your-username/myApps/public` with the actual path to your application's public directory:

```conf
DocumentRoot  /home/ubuntu/myApps/php-rds-test/public
<Directory  /home/ubuntu/myApps/php-rds-test/public>
    AllowOverride All
    Require all granted
</Directory>
```

Enable the new virtual host configuration:

```ssh
sudo a2ensite myapp.conf
```

To activate the new configuration, reload Apache:

```ssh
sudo systemctl reload apache2
```

Step 10: Test the Web Server.

Open your web browser and navigate to your instance's public IP address. You should see a table displaying Brazilian state names.
