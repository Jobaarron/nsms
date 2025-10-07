document.addEventListener('DOMContentLoaded', function() {
    console.log('Face registration JS loaded');

    // DOM Elements
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    const startCameraBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('captureBtn');
    const stopCameraBtn = document.getElementById('stopCamera');
    const savePhotosBtn = document.getElementById('savePhotos');
    const clearPhotosBtn = document.getElementById('clearPhotos');
    const faceStatus = document.getElementById('faceStatus');

    // Configuration
    const FLASK_SERVER_URL = 'http://192.168.1.18:5000';
    let isFlaskServerAvailable = false;

    // State variables
    let stream = null;
    let capturedPhoto = null;

    // Check Flask server availability
    checkFlaskServer();

    async function checkFlaskServer() {
        try {
            const response = await fetch(`${FLASK_SERVER_URL}/`, { method: 'GET', signal: AbortSignal.timeout(5000) });
            isFlaskServerAvailable = response.ok;
            if (isFlaskServerAvailable) console.log('Flask server is available');
        } catch (error) {
            isFlaskServerAvailable = false;
            console.warn('Flask server is not available:', error.message);
            faceStatus.textContent = 'Note: AI face encoding unavailable';
            faceStatus.style.background = 'rgba(255,193,7,0.8)';
        }
    }

    // Start camera
    startCameraBtn.addEventListener('click', async function() {
        console.log('Start camera button clicked');
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { width: 640, height: 480, facingMode: 'user' }
            });
            video.srcObject = stream;

            startCameraBtn.disabled = true;
            captureBtn.disabled = false;
            stopCameraBtn.disabled = false;

            faceStatus.textContent = 'Camera ready - Position your face';
            faceStatus.style.background = 'rgba(40,167,69,0.8)';
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Unable to access camera. Please check permissions.');
            faceStatus.textContent = 'Camera access denied';
            faceStatus.style.background = 'rgba(220,53,69,0.8)';
        }
    });

    // Capture photo
    captureBtn.addEventListener('click', function() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);

        capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
        displayCapturedPhoto();

        faceStatus.textContent = 'Photo captured successfully!';
        faceStatus.style.background = 'rgba(40,167,69,0.8)';
        savePhotosBtn.disabled = false;
    });

    // Stop camera
    stopCameraBtn.addEventListener('click', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            stream = null;
        }
        startCameraBtn.disabled = false;
        captureBtn.disabled = true;
        stopCameraBtn.disabled = true;

        faceStatus.textContent = 'Camera stopped';
        faceStatus.style.background = 'rgba(108,117,125,0.8)';
    });

    // Clear photo
    clearPhotosBtn.addEventListener('click', function() {
        capturedPhoto = null;
        displayCapturedPhoto();
        savePhotosBtn.disabled = true;
        faceStatus.textContent = 'Photo cleared';
        faceStatus.style.background = 'rgba(255,193,7,0.8)';
    });

    // Display captured photo
    function displayCapturedPhoto() {
        const container = document.getElementById('capturedPhotos');
        const card = document.getElementById('capturedPhotosCard');

        if (capturedPhoto) {
            card.style.display = 'block';
            container.innerHTML = `
                <div class="col-12 text-center">
                    <div class="position-relative d-inline-block">
                        <img src="${capturedPhoto}" class="img-fluid rounded" alt="Captured Photo" style="max-height: 300px;">
                        <button class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" onclick="removePhoto()">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Captured face for registration</small>
                    </div>
                </div>
            `;
        } else {
            card.style.display = 'none';
            container.innerHTML = '';
        }
    }

    // Remove photo
    window.removePhoto = function() {
        capturedPhoto = null;
        displayCapturedPhoto();
        savePhotosBtn.disabled = true;
        faceStatus.textContent = 'Photo removed';
        faceStatus.style.background = 'rgba(255,193,7,0.8)';
    };

    // Save photo
    savePhotosBtn.addEventListener('click', async function() {
        if (!capturedPhoto) {
            alert('Please capture a photo first.');
            return;
        }

        savePhotosBtn.disabled = true;
        savePhotosBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Registering Face...';

        try {
            let faceEncoding = null;
            let confidenceScore = 0.9;
            let faceLandmarks = [];

            if (isFlaskServerAvailable) {
                try {
                    faceStatus.textContent = 'Encoding face with AI...';
                    faceStatus.style.background = 'rgba(23,162,184,0.8)';

                    const formData = new FormData();
                    const blob = dataURLtoBlob(capturedPhoto);
                    formData.append('image', blob, 'face.jpg');

                    const encodeResp = await fetch(`${FLASK_SERVER_URL}/encode-face`, {
                        method: 'POST',
                        body: formData,
                        signal: AbortSignal.timeout(10000)
                    });

                    if (encodeResp.ok) {
                        const encodeData = await encodeResp.json();
                        if (!encodeData.error) {
                            faceEncoding = encodeData.encoding;
                            confidenceScore = encodeData.confidence || 0.9;
                            faceLandmarks = encodeData.landmarks || [];
                            console.log('Face encoded successfully via Flask');
                        }
                    }
                } catch (flaskError) {
                    console.warn('Flask encoding failed, using fallback:', flaskError);
                    isFlaskServerAvailable = false;
                }
            }

            // fallback encoding
            if (!faceEncoding) {
                faceStatus.textContent = 'Using basic image registration...';
                faceStatus.style.background = 'rgba(108,117,125,0.8)';

                faceEncoding = {
                    method: 'basic_image_storage',
                    timestamp: new Date().toISOString(),
                    image_hash: await generateSimpleHash(capturedPhoto)
                };
                confidenceScore = 0.7;
            }

            // prepare data for Laravel
            const laravelFormData = new FormData();
            laravelFormData.append('face_encoding', JSON.stringify(faceEncoding));
            laravelFormData.append('confidence_score', confidenceScore);
            laravelFormData.append('face_landmarks', JSON.stringify(faceLandmarks));
            laravelFormData.append('source', 'webcam');
            laravelFormData.append('encoding_method', faceEncoding.method || 'flask_encoding');
            laravelFormData.append('face_image', dataURLtoBlob(capturedPhoto), 'face_capture.jpg');

            const laravelResp = await fetch(window.faceRegistrationSaveUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: laravelFormData
            });

            const responseText = await laravelResp.text();
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                throw new Error(`Invalid server response: ${responseText.substring(0, 200)}`);
            }

            if (!laravelResp.ok || !result.success) {
                throw new Error(result.message || `Registration failed: ${laravelResp.status}`);
            }

            faceStatus.textContent = 'Face registered successfully!';
            faceStatus.style.background = 'rgba(40,167,69,0.8)';
            alert('Face registration completed successfully!');
            setTimeout(() => window.location.reload(), 1500);

        } catch (error) {
            console.error('Face registration error:', error);
            faceStatus.textContent = 'Registration failed';
            faceStatus.style.background = 'rgba(220,53,69,0.8)';
            alert('Failed to register face: ' + error.message);
            savePhotosBtn.disabled = false;
            savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Register Face';
        }
    });

    // Helper functions
    function dataURLtoBlob(dataURL) {
        const byteString = atob(dataURL.split(',')[1]);
        const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
        return new Blob([ab], { type: mimeString });
    }

    async function generateSimpleHash(dataURL) {
        const string = dataURL + Date.now();
        let hash = 0;
        for (let i = 0; i < string.length; i++) {
            const char = string.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(36);
    }

    // Delete face registration
    window.confirmDeleteFace = function() {
        if (confirm('Are you sure you want to delete your face registration data?')) {
            deleteFaceRegistration();
        }
    };

    async function deleteFaceRegistration() {
        try {
            const response = await fetch(window.faceRegistrationDeleteUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const text = await response.text();
            const data = JSON.parse(text);
            if (data.success) {
                alert('Face registration removed successfully!');
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to delete registration');
            }
        } catch (error) {
            console.error('Error deleting face registration:', error);
            alert('Failed to remove face registration. Please try again.');
        }
    }

    window.addEventListener('beforeunload', function() {
        if (stream) stream.getTracks().forEach(track => track.stop());
    });

    displayCapturedPhoto();
});
