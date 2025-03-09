<?php
// Enable error reporting for debugging but don't display errors to output
error_reporting(E_ALL);
ini_set('display_errors', 0); // ← Changed from 1 to 0

// Handle the POST request from JavaScript
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Set content type to JSON to avoid HTML errors in output
    header('Content-Type: application/json');
    
    $url = "http://127.0.0.1:5000/chat"; // Flask API URL

    try {
        // Retrieve JSON input from JavaScript fetch request
        $json_data = file_get_contents("php://input");
        
        // Validate JSON
        $data = json_decode($json_data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON received: " . json_last_error_msg());
        }
        
        // Debugging: Log the received JSON
        file_put_contents("debug_log.txt", "Received JSON: " . $json_data . "\n", FILE_APPEND);

        // Set up HTTP request options with error handling
        $options = [
            "http" => [
                "header"  => "Content-Type: application/json\r\n",
                "method"  => "POST",
                "content" => $json_data,
                "ignore_errors" => true // This allows us to capture error responses
            ]
        ];

        // Create context for HTTP request
        $context = stream_context_create($options);

        // Send the request and capture the response
        $result = file_get_contents($url, false, $context);
        
        // Check for HTTP errors
        $status_line = $http_response_header[0];
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $status = $match[1];
        
        if ($status >= 400) {
            throw new Exception("API returned error status: $status");
        }
        
        // Debugging: Log Flask response
        file_put_contents("debug_log.txt", "Flask Response: " . $result . "\n", FILE_APPEND);
        
        // Validate that the response is valid JSON
        json_decode($result);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response received from API");
        }
        
        // Return the Flask response back to the frontend
        echo $result;
    } catch (Exception $e) {
        // Handle error and return as JSON
        echo json_encode([
            "error" => "Error: " . $e->getMessage()
        ]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Code to MIPS Converter</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f8f9fa;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .code-area {
            display: flex;
            margin: 20px 0;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .code-area {
                flex-direction: column;
            }
        }
        .input-section, .output-section {
            flex: 1;
            text-align: left;
        }
        textarea {
            width: 100%;
            min-height: 300px;
            padding: 15px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Consolas, Monaco, 'Courier New', monospace;
            font-size: 14px;
            resize: vertical;
        }
        button {
            margin-top: 15px;
            padding: 12px 25px;
            border: none;
            background-color: #3498db;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        #result {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: left;
            white-space: pre-wrap;
            font-family: Consolas, Monaco, 'Courier New', monospace;
        }
        .error {
            background-color: #fadbd8;
            color: #c0392b;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            display: none;
        }
        h2 {
            color: #2c3e50;
            font-size: 18px;
            margin-bottom: 5px;
        }
        #debugging {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: left;
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Code to MIPS Converter</h1>
        <p>Enter your code and convert it to MIPS assembly</p>
        
        <div class="error" id="errorBox"></div>
        
        <div class="code-area">
            <div class="input-section">
                <h2>Input Code</h2>
                <textarea id="codeInput" placeholder="Enter your code here (C, Java, Python, etc.)" required></textarea>
            </div>
            
            <div class="output-section">
                <h2>MIPS Output</h2>
                <textarea id="result" readonly placeholder="MIPS code will appear here"></textarea>
            </div>
        </div>
        
        <button type="button" id="convertBtn">Convert to MIPS</button>
        
        <div id="debugging"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Page loaded, JavaScript active.");
            
            const convertBtn = document.getElementById("convertBtn");
            const codeInput = document.getElementById("codeInput");
            const result = document.getElementById("result");
            const errorBox = document.getElementById("errorBox");
            const debugging = document.getElementById("debugging");
            
            // For debugging - uncomment to enable
            // debugging.style.display = "block";
            
            convertBtn.addEventListener("click", function() {
                // Clear previous results and errors
                errorBox.style.display = "none";
                result.value = "";
                debugging.textContent = "";
                
                // Get the input code
                const inputCode = codeInput.value.trim();
                
                // Validate input
                if (!inputCode) {
                    errorBox.textContent = "Please enter some code to convert";
                    errorBox.style.display = "block";
                    return;
                }
                
                // Show loading state
                convertBtn.disabled = true;
                convertBtn.textContent = "Converting...";
                result.value = "Processing...";
                
                // Create the prompt for the API
                const promptText = `Convert the following code to MIPS assembly language. Provide only the MIPS code without any explanation: ${inputCode}`;
                const requestData = { prompt: promptText };
                
                // For debugging
                // debugging.textContent = "Sending data: " + JSON.stringify(requestData);
                
                // Send request to the PHP script
                fetch(window.location.href, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    // For debugging raw response
                    return response.text().then(text => {
                        // debugging.textContent += "\n\nRaw response: " + text;
                        
                        try {
                            // Try to parse as JSON
                            return JSON.parse(text);
                        } catch (e) {
                            // If parsing fails, show the raw response in error
                            throw new Error("Invalid response: " + text);
                        }
                    });
                })
                .then(data => {
                    // Handle the response from Flask
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Display the MIPS code
                    result.value = data.response;
                })
                .catch(error => {
                    console.error("Error:", error);
                    errorBox.textContent = error.message;
                    errorBox.style.display = "block";
                    result.value = "";
                })
                .finally(() => {
                    // Reset button state
                    convertBtn.disabled = false;
                    convertBtn.textContent = "Convert to MIPS";
                });
            });
        });
    </script>
</body>
</html>
