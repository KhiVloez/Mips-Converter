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
    <title>Code to MIPS + Datapath Converter</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }
        
        p {
            text-align: center;
            margin-bottom: 20px;
            color: #7f8c8d;
        }
        
        textarea {
            width: 100%;
            min-height: 200px;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
        }
        
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        button:hover {
            background-color: #2980b9;
        }
        
        button:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
        }
        
        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .instruction-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .datapath-container {
            width: 100%;
            min-height: 500px;
            border: 1px solid #ddd;
            margin: 20px 0;
            padding: 10px;
            background-color: #f8f9fa;
            overflow: auto;
        }
    </style>
    <script>
        // Wait for the DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            // Tab functionality
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab content
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
        
        async function convertToMIPS() {
            const inputText = document.getElementById("codeInput").value;
            const resultBox = document.getElementById("mipsResult");
            const errorBox = document.getElementById("mipsErrorBox");
            const convertBtn = document.getElementById("convertMipsBtn");

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
        
        async function generateDatapath() {
            const instructionText = document.getElementById("instructionInput").value;
            const datapathContainer = document.getElementById("datapathResult");
            const errorBox = document.getElementById("datapathErrorBox");
            const generateBtn = document.getElementById("generateDatapathBtn");

            // Clear previous results and errors
            errorBox.style.display = "none";
            datapathContainer.innerHTML = "<p>Generating datapath diagram...</p>";
            generateBtn.disabled = true;
            generateBtn.textContent = "Generating...";

            // Create the GPT prompt for datapath generation
            const promptText = `Generate a detailed single-cycle datapath diagram for the MIPS instruction: "${instructionText}". 
            Create a detailed SVG diagram of a MIPS single-cycle datapath highlighting the components and paths active for this specific instruction. 
            Include ALU, registers, control unit, memory, multiplexers, and all necessary components. 
            Use different colors to show the active path. Provide only the SVG code without explanation.`;
            
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
                    // Display the SVG diagram
                    datapathContainer.innerHTML = data.response;
                } else {
                    throw new Error(data.error || "Unknown error occurred");
                }
            } catch (error) {
                console.error("Error:", error);
                errorBox.textContent = "Error: " + error.message;
                errorBox.style.display = "block";
                datapathContainer.innerHTML = "<p>Failed to generate datapath diagram.</p>";
            } finally {
                generateBtn.disabled = false;
                generateBtn.textContent = "Generate Datapath";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="mips-converter">Code to MIPS</div>
            <div class="tab" data-tab="datapath-generator">MIPS to Datapath</div>
        </div>
        
        <!-- Code to MIPS Converter Tab -->
        <div id="mips-converter" class="tab-content active">
            <h1>Code to MIPS Converter</h1>
            <p>Enter your code and convert it to MIPS assembly</p>
            
            <div class="error" id="mipsErrorBox" style="display:none;"></div>
            
            <textarea id="codeInput" placeholder="Enter your high-level code here (C, Java, Python, etc.)"></textarea>
            <button type="button" id="convertMipsBtn" onclick="convertToMIPS()">Convert to MIPS</button>
            <textarea id="mipsResult" readonly placeholder="MIPS assembly code will appear here"></textarea>
        </div>
        
        <!-- MIPS to Datapath Generator Tab -->
        <div id="datapath-generator" class="tab-content">
            <h1>MIPS to Single Cycle Datapath</h1>
            <p>Enter a MIPS instruction and generate a datapath diagram</p>
            
            <div class="error" id="datapathErrorBox" style="display:none;"></div>
            
            <input type="text" id="instructionInput" class="instruction-input" 
                   placeholder="Enter a MIPS instruction (e.g., add $t0, $t1, $t2)" />
            <button type="button" id="generateDatapathBtn" onclick="generateDatapath()">Generate Datapath</button>
            
            <div id="datapathResult" class="datapath-container">
                <p>Datapath diagram will appear here</p>
            </div>
        </div>
    </div>
</body>
</html>