from flask import Flask, request, jsonify
import openai
from flask_cors import CORS
import os
import json
import logging

# Set up logging
logging.basicConfig(
    level=logging.DEBUG,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler("flask_api.log"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Initialize Flask app
app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Get API key from environment variable
openai.api_key = os.getenv("OPENAI_API_KEY")
if not openai.api_key:
    logger.warning("OPENAI_API_KEY environment variable not set!")

@app.route('/chat', methods=['POST'])
def talktoforce():
    logger.info("Received request to /chat endpoint")
    
    try:
        # Log raw request data for debugging
        raw_data = request.get_data().decode('utf-8')
        logger.debug(f"Raw request data: {raw_data}")
        
        # Get JSON data from request
        data = request.get_json(force=True)  # Using force=True to handle potential content-type issues
        logger.info(f"Parsed JSON data: {data}")
        
        # Validate request data
        if not data or 'prompt' not in data:
            logger.error("Missing 'prompt' field in request")
            return jsonify({"error": "Missing 'prompt' field in request"}), 400
        
        # Extract prompt
        prompt = data['prompt']
        logger.info(f"Processing prompt (first 100 chars): {prompt[:100]}...")
        
        # Call OpenAI API
        logger.info("Calling OpenAI API")
        response = openai.ChatCompletion.create(
            model='gpt-4',
            messages=[{"role": "user", "content": prompt}]
        )
        
        # Extract and log response
        gpt_reply = response['choices'][0]['message']['content']
        logger.info(f"Received response from OpenAI (first 100 chars): {gpt_reply[:100]}...")
        
        # Return success response
        return jsonify({"response": gpt_reply})
    
    except json.JSONDecodeError as e:
        logger.error(f"JSON decode error: {str(e)}")
        return jsonify({"error": f"Invalid JSON in request: {str(e)}"}), 400
    
    except openai.error.OpenAIError as e:
        logger.error(f"OpenAI API error: {str(e)}")
        return jsonify({"error": f"OpenAI API error: {str(e)}"}), 500
    
    except Exception as e:
        logger.error(f"Unexpected error: {str(e)}", exc_info=True)
        return jsonify({"error": f"Internal server error: {str(e)}"}), 500

if __name__ == '__main__':
    logger.info("Starting Flask API server")
    app.run(debug=True)