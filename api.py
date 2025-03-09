import google.generativeai as genai
import os
from flask import Flask, request, jsonify
from flask_cors import CORS
import anthropic
app = Flask(__name__)
CORS(app)
claude_api_key = os.getenv("CLAUDE_API_KEY")
api_key = os.getenv("GEMINI_API_KEY")
if not api_key:
    raise ValueError(" ERROR: GEMINI_API_KEY is missing. Set it in your environment variables.")

genai.configure(api_key=api_key)

@app.route('/chat', methods=['POST'])
def chat():
    try:
        print(" Received request from frontend")
        
        data = request.get_json()
        print(" Received JSON:", data)

        if not data or 'prompt' not in data:
            print(" ERROR: Missing 'prompt' field")
            return jsonify({"error": "Missing 'prompt' field"}), 400

        prompt = data['prompt']
        print(f" Sending prompt to Gemini: {prompt}")

        model_name = "gemini-1.5-pro"  
        response = genai.GenerativeModel(model_name).generate_content(prompt)

        print(" Raw API Response:", response)

        gpt_reply = response.text
        print(f" Gemini Response: {gpt_reply}")

        return jsonify({"response": gpt_reply})

    except Exception as e:
        print(" ERROR:", e)
        return jsonify({"error": f"Internal server error: {str(e)}"}), 500    

claude_client = anthropic.Anthropic(
    api_key=claude_api_key
)

@app.route('/talkclaude', methods=['POST'])
def talkclaude():
    try:
        data = request.get_json()

        if not data or 'prompt' not in data:
            return jsonify({"error": "Missing 'prompt' field"}), 400

        prompt = data['prompt']
        print(f" Sending request to Claude: {prompt}")

        response = claude_client.messages.create(
            model="claude-3-opus-20240229",
            max_tokens=500,
            messages=[{"role": "user", "content": prompt}]
        )

        claude_reply = response.content[0].text  

        print(f" Claude Response: {claude_reply}")

        return jsonify({"response": claude_reply})

    except anthropic.APIError as e:  
        print(f" Claude API Error: {e}")
        return jsonify({"error": f"Claude API error: {str(e)}"}), 500

    except Exception as e:
        print(f" ERROR (Claude Backend): {e}")
        return jsonify({"error": f"Internal server error: {str(e)}"}), 500  

if __name__ == '__main__':
    app.run(debug=True)
