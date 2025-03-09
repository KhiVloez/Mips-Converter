import google.generativeai as genai
import os
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

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

if __name__ == '__main__':
    app.run(debug=True)
