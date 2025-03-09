from flask import Flask, request, jsonify
import openai
from flask_cors import CORS
import os

app = Flask(__name__)
CORS(app)  

openai.api_key = os.getenv("OPENAI_API_KEY")

@app.route('/chat', methods=['POST'])
def talktoforce():
    try:
        
        data = request.get_json

        prompt = data['prompt']

        if not data or 'prompt' not in data:
            return jsonify({"error": "Missing 'prompt' field in request"}), 400

        response = openai.ChatCompletion.create(
            model = 'gpt-4',
            messages=[{"role": "user", "content": prompt}]
        )
        gpt_reply = response['choices'][0]['messages']['content']

        return jsonify({"response": gpt_reply})
    except openai.error.OpenAIError as e:
        return jsonify({"error": f"OpenAI API error: {str(e)}"}), 500
    except Exception as e:
        return jsonify({"error": f"Internal server error: {str(e)}"}), 500    
      
if __name__ == '__main__':
    app.run(debug=True)
