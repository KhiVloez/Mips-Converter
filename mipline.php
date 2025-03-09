<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $url = "http://127.0.0.1:5000/chat"; 

    $json_data = file_get_contents("php://input");

    file_put_contents("debug_log.txt", "Received JSON: " . $json_data . "\n", FILE_APPEND);

    if (!$json_data) {
        echo json_encode(["error" => "Invalid JSON input"]);
        exit;
    }

    $options = [
        "http" => [
            "header"  => "Content-Type: application/json",
            "method"  => "POST",
            "content" => $json_data
        ]
    ];

    $context = stream_context_create($options);

    try {
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            throw new Exception("Failed to fetch response from Flask API.");
        }

        file_put_contents("debug_log.txt", "Flask Response: " . $result . "\n", FILE_APPEND);

        header('Content-Type: application/json');
        echo $result;
    } catch (Exception $e) {
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
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    
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

            errorBox.style.display = "none";
            resultBox.value = "Processing...";
            convertBtn.disabled = true;
            convertBtn.textContent = "Converting...";

            const promptText = `Convert the following code to MIPS assembly language. Provide only the MIPS code without explanation: ${inputText}`;
            
            try {
                const response = await fetch(window.location.href, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ prompt: promptText })
                });

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

    errorBox.style.display = "none";
    datapathContainer.innerHTML = "<p>Generating datapath diagram...</p>";
    generateBtn.disabled = true;
    generateBtn.textContent = "Generating...";

    const promptText = `Generate a detailed, academically accurate SVG diagram of a MIPS single-cycle datapath for the instruction: "${instructionText}".

Implement the SVG with the following detailed code specifications:

1. SVG STRUCTURE:
   Use this base SVG structure with a responsive viewBox and clear organization:
   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 600">
     <!-- Background -->
     <rect width="900" height="600" fill="#f8f9fa" />
     
     <!-- Title -->
     <text x="450" y="30" font-family="Arial" font-size="20" font-weight="bold" text-anchor="middle">MIPS Single Cycle Datapath - ${instructionText}</text>
     
     <!-- Component groups with comments before each section -->
     <!-- PC and Instruction Fetch components -->
     <!-- ... -->
     
     <!-- Control Unit components -->
     <!-- ... -->
     
     <!-- Register File components -->
     <!-- ... -->
     
     <!-- ALU components -->
     <!-- ... -->
     
     <!-- Data Memory components -->
     <!-- ... -->
     
     <!-- Legends and Labels -->
     <!-- ... -->
   </svg>

2. ARROW IMPLEMENTATION:
   For all data path arrows, use this specific pattern to ensure proper arrowheads:
   <!-- For each path with arrow -->
   <path d="M[startX] [startY] H[endX]" stroke="#0066cc" stroke-width="3" fill="none" />
   <polygon points="[endX],[endY] [endX-10],[endY-5] [endX-10],[endY+5]" fill="#0066cc" />
   
   <!-- For complex paths with multiple segments -->
   <path d="M[x1] [y1] H[x2] V[y2] H[x3]" stroke="#0066cc" stroke-width="3" fill="none" />
   <polygon points="[x3],[y2] [x3-10],[y2-5] [x3-10],[y2+5]" fill="#0066cc" />

3. COMPONENT IMPLEMENTATIONS:
   - PC Register:
     <rect x="70" y="150" width="60" height="100" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
     <text x="100" y="200" font-family="Arial" font-size="14" text-anchor="middle">PC</text>
   
   - Instruction Memory:
     <rect x="170" y="150" width="120" height="100" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
     <text x="230" y="185" font-family="Arial" font-size="14" text-anchor="middle">Instruction</text>
     <text x="230" y="205" font-family="Arial" font-size="14" text-anchor="middle">Memory</text>
   
   - Control Unit (active):
     <ellipse cx="370" cy="120" rx="40" ry="60" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
     <text x="370" y="120" font-family="Arial" font-size="14" text-anchor="middle">Control</text>
     <text x="370" y="140" font-family="Arial" font-size="14" text-anchor="middle">Unit</text>
   
   - Register File:
     <rect x="380" y="240" width="140" height="140" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
     <text x="450" y="270" font-family="Arial" font-size="14" text-anchor="middle">Registers</text>
     <text x="450" y="290" font-family="Arial" font-size="10" text-anchor="middle">Read register 1</text>
     <text x="450" y="310" font-family="Arial" font-size="10" text-anchor="middle">Read register 2</text>
     <text x="450" y="330" font-family="Arial" font-size="10" text-anchor="middle">Write register</text>
     <text x="450" y="350" font-family="Arial" font-size="10" text-anchor="middle">Write data</text>
     <text x="450" y="370" font-family="Arial" font-size="10" text-anchor="middle">Read data 1/2</text>
   
   - ALU:
     <polygon points="610,310 640,280 640,340" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
     <text x="630" y="310" font-family="Arial" font-size="14" text-anchor="middle">ALU</text>
   
   - Data Memory:
     <rect x="670" y="260" width="120" height="100" fill="#[FILL_COLOR]" stroke="#[STROKE_COLOR]" stroke-width="2" />
     <text x="730" y="295" font-family="Arial" font-size="14" text-anchor="middle" fill="#[TEXT_COLOR]">Data</text>
     <text x="730" y="315" font-family="Arial" font-size="14" text-anchor="middle" fill="#[TEXT_COLOR]">Memory</text>
   
   - Multiplexer (vertical, 2-way):
     <rect x="[X]" y="[Y]" width="20" height="60" fill="#[FILL_COLOR]" stroke="#[STROKE_COLOR]" stroke-width="2" />
     <text x="[X+10]" y="[Y+25]" font-family="Arial" font-size="12" text-anchor="middle" fill="#[TEXT_COLOR]">M</text>
     <text x="[X+10]" y="[Y+40]" font-family="Arial" font-size="12" text-anchor="middle" fill="#[TEXT_COLOR]">u</text>
     <text x="[X+10]" y="[Y+55]" font-family="Arial" font-size="12" text-anchor="middle" fill="#[TEXT_COLOR]">x</text>
     <path d="M[X-10] [Y+15] H[X]" stroke="#[STROKE_COLOR]" stroke-width="2" fill="none" />
     <text x="[X-5]" y="[Y+10]" font-family="Arial" font-size="10" fill="#[TEXT_COLOR]">0</text>
     <path d="M[X-10] [Y+45] H[X]" stroke="#[STROKE_COLOR]" stroke-width="2" fill="none" />
     <text x="[X-5]" y="[Y+55]" font-family="Arial" font-size="10" fill="#[TEXT_COLOR]">1</text>

4. CONTROL SIGNALS:
   For each control signal, implement with a dashed line:
   <path d="M[startX] [startY] L[endX] [endY]" stroke="#cc0000" stroke-width="2" fill="none" stroke-dasharray="5,5" />
   <text x="[labelX]" y="[labelY]" font-family="Arial" font-size="12" fill="#[ACTIVE ? '#009900' : '#666666']">[SIGNAL_NAME] = [VALUE]</text>

5. LEGEND IMPLEMENTATION:
   <rect x="680" y="480" width="180" height="100" fill="white" stroke="black" stroke-width="1" />
   <text x="770" y="500" font-family="Arial" font-size="14" text-anchor="middle" font-weight="bold">Legend</text>
   
   <rect x="690" y="510" width="20" height="10" fill="#b3d9ff" stroke="#0066cc" stroke-width="2" />
   <text x="720" y="520" font-family="Arial" font-size="12" text-anchor="start">Active Components</text>
   
   <rect x="690" y="530" width="20" height="10" fill="#e6e6e6" stroke="#999999" stroke-width="2" />
   <text x="720" y="540" font-family="Arial" font-size="12" text-anchor="start">Inactive Components</text>
   
   <line x1="690" y1="550" x2="710" y2="550" stroke="#0066cc" stroke-width="3" />
   <text x="720" y="555" font-family="Arial" font-size="12" text-anchor="start">Active Paths</text>
   
   <line x1="690" y1="565" x2="710" y2="565" stroke="#cccccc" stroke-width="2" />
   <text x="720" y="570" font-family="Arial" font-size="12" text-anchor="start">Inactive Paths</text>

6. INSTRUCTION-SPECIFIC HIGHLIGHTING:
   Based on instruction type (R-type, I-type, J-type), correctly color these datapath components using these values:
   - Active components: fill="#b3d9ff" stroke="#0066cc" text color="#000000" line-width="3"
   - Inactive components: fill="#e6e6e6" stroke="#999999" text color="#666666" line-width="2"

   For "${instructionText}", determine the active/inactive components and color appropriately:
   - R-type (add, sub, and, or, slt): 
     * Active: PC→Instr Mem→Registers→ALU→WriteBack, RegDst MUX=1, ALUOp=10
     * Inactive: Memory, MemtoReg MUX, Branch paths
   
   - lw: 
     * Active: PC→Instr Mem→Registers→ALU→Memory→WriteBack, ALUSrc MUX=1, MemtoReg MUX=1
     * Inactive: RegDst MUX=0, Branch paths
   
   - sw: 
     * Active: PC→Instr Mem→Registers→ALU→Memory, ALUSrc MUX=1
     * Inactive: RegDst MUX, MemtoReg MUX, WriteBack path
   
   - beq: 
     * Active: PC→Instr Mem→Registers→ALU→Branch calculation, Branch path
     * Inactive: RegDst MUX, MemtoReg MUX, Memory, WriteBack

7. CONTROL SIGNAL TABLE:
   <rect x="30" y="480" width="200" height="100" fill="white" stroke="black" stroke-width="1" />
   <text x="130" y="500" font-family="Arial" font-size="14" text-anchor="middle" font-weight="bold">Control Signals for {INSTR_TYPE}</text>
   
   <!-- Add all control signals in a table format with accurate values -->
   <text x="40" y="520" font-family="Arial" font-size="12" fill="#[RegDst_active ? '#009900' : '#666666']">RegDst = {RegDst_value}</text>
   <text x="40" y="535" font-family="Arial" font-size="12" fill="#[ALUSrc_active ? '#009900' : '#666666']">ALUSrc = {ALUSrc_value}</text>
   <!-- Continue with other signals -->

Output only clean, well-structured SVG code without any explanation or additional text.`;


    try {
        const response = await fetch("http://127.0.0.1:5000/talkclaude", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ prompt: promptText })
        });

        const data = await response.json();

        if (response.ok) {
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