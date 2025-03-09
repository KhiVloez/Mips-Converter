<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle the POST request from JavaScript and forward it to Flask API
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = "http://127.0.0.1:5000/chat"; // Flask API URL

    // Retrieve JSON input from JavaScript fetch request
    $json_data = file_get_contents("php://input");

    // Debugging: Log received JSON
    file_put_contents("debug_log.txt", "Received JSON: " . $json_data . "\n", FILE_APPEND);

    // Ensure we received valid JSON
    if (!$json_data) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit;
    }

    // Set up HTTP request options
    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => $json_data
        ]
    ];

    // Create context for HTTP request
    $context = stream_context_create($options);

    try {
        // Send the request to Flask API and capture the response
        $result = file_get_contents($url, false, $context);

        // Check if Flask responded
        if ($result === FALSE) {
            throw new Exception("Failed to fetch response from Flask API.");
        }

        // Debugging: Log Flask response
        file_put_contents("debug_log.txt", "Flask Response: " . $result . "\n", FILE_APPEND);

        // Ensure JSON response
        header('Content-Type: application/json');
        echo $result;
    } catch (Exception $e) {
        // Return JSON error response
        header('Content-Type: application/json');
        echo json_encode(["error" => "Failed to connect to the API: " . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Code to MIPS Converter New</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script>
        async function sendMessage() {
            const inputText = document.getElementById("codeInput").value;
            const resultBox = document.getElementById("result");
            const errorBox = document.getElementById("errorBox");
            const convertBtn = document.getElementById("convertBtn");

            // Clear previous results and errors
            errorBox.style.display = "none";
            resultBox.value = "Processing...";
            convertBtn.disabled = true;
            convertBtn.textContent = "Converting...";

            // Create the GPT prompt
            const promptText = `Convert the following code to MIPS assembly language. Provide only the MIPS code without explanation: ${inputText}`;

            try {
                // Send request to PHP backend
                const response = await fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ prompt: promptText })
                });

                // Ensure JSON response
                const data = await response.json();

                if (response.ok) {
                    resultBox.value = data.response;
                } else {
                    throw new Error(data.error || "Unknown error occurred");
                }
            } catch (error) {
                console.error("Error:", error);
                errorBox.textContent = "Error: " + error.message;
                errorBox.style.display = "block";
                resultBox.value = "";
            } finally {
                convertBtn.disabled = false;
                convertBtn.textContent = "Convert to MIPS";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Code to MIPS Converter New</h1>
        <p>Enter your code and convert it to MIPS assembly</p>

        <div class="error" id="errorBox" style="display:none;"></div>

        <textarea id="codeInput" placeholder="Enter code here..."></textarea>
        <button type="button" id="convertBtn" onclick="sendMessage()">Convert to MIPS</button>
        <textarea id="result" readonly placeholder="MIPS output will appear here"></textarea>
    </div>
</body>
</html>
