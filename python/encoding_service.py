from flask import Flask, request, jsonify
import numpy as np
import cv2
from insightface.app import FaceAnalysis

app = Flask(__name__)

# Load InsightFace once at startup
face_app = FaceAnalysis(name="buffalo_l", providers=["CPUExecutionProvider"])
# Adjust det_size if you want faster detection vs accuracy
face_app.prepare(ctx_id=0, det_size=(640, 640))

@app.route("/generate_encoding", methods=["POST"])
def generate_encoding():
    if "image" not in request.files:
        return jsonify({"error": "No image file provided (field name 'image')"}), 400

    file = request.files["image"].read()
    img_arr = np.frombuffer(file, np.uint8)
    img = cv2.imdecode(img_arr, cv2.IMREAD_COLOR)
    if img is None:
        return jsonify({"error": "Invalid image"}), 400

    faces = face_app.get(img)
    if not faces:
        return jsonify({"error": "No face detected"}), 400

    # Use the largest face if multiple
    faces = sorted(faces, key=lambda f: (f.bbox[2]-f.bbox[0])*(f.bbox[3]-f.bbox[1]), reverse=True)
    f = faces[0]

    emb = f.embedding.astype(float).tolist()  # 512-dim vector (buffalo_l)
    landmarks = f.kps.astype(float).tolist()  # 5 landmarks
    bbox = f.bbox.astype(float).tolist()

    return jsonify({
        "encoding": emb,
        "landmarks": landmarks,
        "bbox": bbox,
        "confidence": float(f.det_score) if hasattr(f, "det_score") else None
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001)
