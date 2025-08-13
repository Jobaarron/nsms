from flask import Flask, request, jsonify
from flask_cors import CORS
import base64, numpy as np, cv2, face_recognition

app = Flask(__name__)
CORS(app)

@app.route('/encode-face', methods=['POST'])
def encode_face():
    try:
        data = request.get_json() or {}
        image_base64 = data.get('image_base64')
        if not image_base64:
            return jsonify({'error': 'image_base64 is required'}), 400

        # remove data URI prefix if present
        if ',' in image_base64:
            image_base64 = image_base64.split(',', 1)[1]

        img_bytes = base64.b64decode(image_base64)
        np_img = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(np_img, cv2.IMREAD_COLOR)
        if img is None:
            return jsonify({'error': 'Invalid image'}), 400

        boxes = face_recognition.face_locations(img)
        if not boxes:
            return jsonify({'error': 'No face detected'}), 400

        encodings = face_recognition.face_encodings(img, boxes)
        if not encodings:
            return jsonify({'error': 'Face encoding failed'}), 400

        landmarks = face_recognition.face_landmarks(img, boxes)

        return jsonify({
            'encoding': encodings[0].tolist(),
            'confidence': 1.0,  # placeholder (face_recognition doesn't give confidence)
            'landmarks': landmarks[0] if landmarks else None
        }), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
