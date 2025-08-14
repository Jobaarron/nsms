# face_recognition_api.py
import os
os.environ["ALBUMENTATIONS_CHECK_FOR_UPDATES"] = "false"  # Disable albumentations online check

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import insightface

app = Flask(__name__)
CORS(app)

# Load InsightFace model (Buffalo_L)
model = insightface.app.FaceAnalysis()
model.prepare(ctx_id=-1)  # CPU only

@app.route('/encode-face', methods=['POST'])
def encode_face():
    try:
        data = request.get_json() or {}
        image_base64 = data.get('image_base64')
        if not image_base64:
            return jsonify({'error': 'image_base64 is required'}), 400

        # Remove data URI prefix if present
        if ',' in image_base64:
            image_base64 = image_base64.split(',', 1)[1]

        # Decode base64 image
        img_bytes = base64.b64decode(image_base64)
        np_img = np.frombuffer(img_bytes, np.uint8)
        img = cv2.imdecode(np_img, cv2.IMREAD_COLOR)
        if img is None:
            return jsonify({'error': 'Invalid image'}), 400

        # Detect faces
        faces = model.get(img)
        if not faces:
            return jsonify({'error': 'No face detected'}), 400

        face = faces[0]  # take first detected face
        embedding = face.embedding.tolist()
        landmarks = face.kps.tolist()  # 5-point landmarks: left_eye, right_eye, nose, mouth_left, mouth_right
        confidence = float(face.det_score)

        return jsonify({
            'encoding': embedding,
            'landmarks': landmarks,
            'confidence': confidence
        }), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting Flask server with InsightFace (CPU)...")
    app.run(host='0.0.0.0', port=5000)
