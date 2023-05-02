<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Load the Composer autoloader for the AWS SDK
require realpath(__DIR__ . '/../vendor/autoload.php');
// Import the SecretsManagerClient class
use Aws\SecretsManager\SecretsManagerClient;
// Replace these values with your actual values
$host = 'YOUR_RDS_ENDPOINT.us-east-1.rds.amazonaws.com';
$port = 3306;
$dbname = 'CustomerData';
$username = 'admin';
$secretName = 'YOUR-SECRET-NAME';
$region = 'us-east-1';
// Instantiate the Secrets Manager client
$secretsManager = new SecretsManagerClient([
    'version' => 'latest',
    'region' => $region,
]);
// Try to retrieve the RDS password from AWS Secrets Manager
try {
    $result = $secretsManager->getSecretValue([
        'SecretId' => $secretName,
    ]);
} catch (Exception $e) {
    echo "Error retrieving RDS password: " . $e->getMessage();
    exit();
}
// Decode the JSON secret string and extract the password
$secrets = json_decode($result['SecretString'], true);
$password = $secrets['password'];
// Connect to the database using the extracted credentials
$conn = new mysqli($host, $username, $password, $dbname, $port);
// Check for connection errors and display an error message if any
if ($conn->connect_error) {
    die("Could not connect to the database: " . $conn->connect_error);
}
// Define the SQL query to fetch the first 30 records from the tblTypeState table
$sql = "SELECT * FROM tblTypeState LIMIT 30";
$result = $conn->query($sql);
// Start rendering the HTML output for the results table
echo '
<div class="container">
    <h1 class="mt-4 mb-4">Result</h1>
    <table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Id</th>
        <th>Abbreviation</th>
        <th>Name</th>
    </tr>
    </thead>
    <tbody>';
// Check if the query returned any rows and display them in the table
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
        <td>".$row["idTypeState"]."</td>
        <td>".$row["dsAbbreviation"]."</td>
        <td>".$row["dsType"]."</td>
        </tr>";
    }
} else {
    // If no rows were returned, display a "No results found" message
    echo "<tr><td colspan='3'>No results found.</td></tr>";
}
// Close the table body, table, and container div elements
echo '
    </tbody>
    </table>
</div>';
// Close the database connection
$conn->close();
?>
</body>
</html>