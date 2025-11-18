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

    // Configuration - Detect environment
    const FLASK_SERVER_URL = '/api/face';
    let isFlaskServerAvailable = false;


    // State variables
    let stream = null;
    let capturedPhoto = null;
    let faceData = { landmarks: null, confidence: 0, encoding: null };
    let encodingPhoto = false;

    // Initialize URLs from meta tags or fallback
    function getUrlFromMeta(metaName, fallback) {
        const meta = document.querySelector(`meta[name="${metaName}"]`);
        return meta ? meta.getAttribute('content') : fallback;
    }
    
    if (!window.faceRegistrationSaveUrl) {
        window.faceRegistrationSaveUrl = getUrlFromMeta('face-registration-save-url', '/student/face-registration/save');
    }
    if (!window.faceRegistrationDeleteUrl) {
        window.faceRegistrationDeleteUrl = getUrlFromMeta('face-registration-delete-url', '/student/face-registration/delete');
    }

    // Debug: Test all endpoints on load
    async function testEndpoints() {
        console.log('=== DEBUG: Testing Endpoints ===');
        
        // Test CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        console.log('CSRF Token exists:', !!csrfToken);
        if (csrfToken) {
            console.log('CSRF Token length:', csrfToken.getAttribute('content')?.length);
        }
        
        // Test save URL
        console.log('faceRegistrationSaveUrl:', window.faceRegistrationSaveUrl);
        console.log('faceRegistrationDeleteUrl:', window.faceRegistrationDeleteUrl);
        
        if (!window.faceRegistrationSaveUrl) {
            console.error('❌ faceRegistrationSaveUrl is NOT defined!');
        } else {
            console.log('✅ faceRegistrationSaveUrl is defined');
        }
        
        // Test Flask server
        try {
            const flaskTest = await fetch(`${FLASK_SERVER_URL}/`, { 
                method: 'GET',
                signal: AbortSignal.timeout(3000)
            });
            console.log('Flask server accessible:', flaskTest.ok);
        } catch (error) {
            console.warn('Flask server test failed:', error.message);
        }
        
        console.log('=== DEBUG: Endpoint Tests Complete ===');
    }

    // Call debug test on load
    testEndpoints();

    // Check Flask server availability
    checkFlaskServer();

   async function checkFlaskServer() {
    try {
        const response = await fetch(`${FLASK_SERVER_URL}/`, { 
            method: 'GET', 
            signal: AbortSignal.timeout(5000) 
        });
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

            // Add face guide overlay
            addFaceGuide();

            faceStatus.textContent = 'Camera ready - Position your face';
            faceStatus.style.background = 'rgba(40,167,69,0.8)';
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Unable to access camera. Please check permissions.');
            faceStatus.textContent = 'Camera access denied';
            faceStatus.style.background = 'rgba(220,53,69,0.8)';
        }
    });

    function addFaceGuide() {
        // Remove existing guide if any
        const existingGuide = document.querySelector('.face-guide');
        if (existingGuide) existingGuide.remove();

        // Add face guide overlay only (no tips)
        const videoContainer = video.parentElement;
        const guide = document.createElement('div');
        guide.className = 'face-guide';
        videoContainer.style.position = 'relative';
        videoContainer.appendChild(guide);
    }

    // Capture photo
    captureBtn.addEventListener('click', async function() {
        if (encodingPhoto) return;
        encodingPhoto = true;
        captureBtn.disabled = true;
        captureBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Processing...';

        try {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);

            capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
            console.log('📸 Photo captured, data URL length:', capturedPhoto.length);

            faceStatus.textContent = 'Encoding face...';
            faceStatus.style.background = 'rgba(23,162,184,0.8)';

            // Encode face immediately after capture
            if (isFlaskServerAvailable) {
                try {
                    const formData = new FormData();
                    const blob = dataURLtoBlob(capturedPhoto);
                    formData.append('image', blob, 'face.jpg');

                    console.log('🔄 Sending face to Flask for encoding...');
                    const encodeResp = await fetch(`${FLASK_SERVER_URL}/encode-face`, {
                        method: 'POST',
                        body: formData,
                        signal: AbortSignal.timeout(30000)
                    });

                    if (encodeResp.ok) {
                        const encodeData = await encodeResp.json();
                        console.log('✅ Flask encoding response:', encodeData);
                        
                        if (encodeData?.confidence !== undefined) {
                            faceData = {
                                landmarks: encodeData.landmarks ?? null,
                                confidence: (encodeData.confidence * 100).toFixed(1),
                                encoding: encodeData.encoding ?? null
                            };
                            console.log('✅ Face encoded successfully. Confidence:', faceData.confidence);
                            console.log('✅ Encoding array length:', faceData.encoding ? faceData.encoding.length : 'null');
                        } else if (encodeData?.error) {
                            console.warn('❌ No face detected in photo');
                            alert('Face Detection: No face detected. Please try again.');
                            capturedPhoto = null;
                            encodingPhoto = false;
                            captureBtn.disabled = false;
                            captureBtn.innerHTML = '<i class="ri-camera-line me-2"></i>Capture Photo';
                            return;
                        }
                    } else {
                        throw new Error(`Encoding failed: ${encodeResp.status}`);
                    }
                } catch (encodeError) {
                    console.error('❌ Face encoding error:', encodeError);
                    // Continue to review step even if encoding fails
                    faceData = { landmarks: null, confidence: 0, encoding: null };
                }
            } else {
                console.log('ℹ️ Using default face data (Flask server unavailable)');
                faceData = { landmarks: null, confidence: 50, encoding: [] }; // Default if no AI
            }

            encodingPhoto = false;
            displayCapturedPhoto();
            savePhotosBtn.disabled = false;
            // Automatically save to backend after encoding
            try {
                const faceEncoding = Array.isArray(faceData.encoding) ? faceData.encoding : [];
                const confidenceScore = faceData.confidence / 100 || 0.5;
                const faceLandmarks = faceData.landmarks || [];

                if (!faceEncoding.length) {
                    alert('No valid face encoding detected. Please capture again.');
                    throw new Error('Empty encoding array');
                }

                // Get student_id from a hidden input or JS variable (update as needed)
                const studentIdInput = document.getElementById('studentId');
                const studentId = studentIdInput ? studentIdInput.value : window.currentStudentId;
                if (!studentId) {
                    alert('Student ID is required.');
                    throw new Error('Missing student_id');
                }

                const laravelFormData = new FormData();
                laravelFormData.append('student_id', studentId);
                laravelFormData.append('face_encoding', JSON.stringify(faceEncoding));
                laravelFormData.append('confidence_score', confidenceScore.toString());
                laravelFormData.append('face_landmarks', JSON.stringify(faceLandmarks));
                laravelFormData.append('face_image_data', capturedPhoto);
                laravelFormData.append('face_image_mime_type', 'image/jpeg');
                laravelFormData.append('source', 'camera_capture');
                laravelFormData.append('encoding_method', 'flask_encoding');

                const blob = dataURLtoBlob(capturedPhoto);
                laravelFormData.append('face_image', blob, 'face_capture.jpg');

                // Ensure the correct API endpoint is used for face registration
                let saveUrl = window.faceRegistrationSaveUrl;
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (!csrfToken) {
                    alert('CSRF token missing. Refresh the page.');
                    throw new Error('Missing CSRF token');
                }
                console.log('🚀 Sending data to Laravel:', saveUrl);
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: laravelFormData,
                    credentials: 'include'
                });

                console.log('📨 Laravel raw status:', response.status, response.statusText);

                const textResponse = await response.text();
                console.log('📄 Laravel raw response:', textResponse);

                let json;
                try {
                    json = JSON.parse(textResponse);
                } catch (e) {
                    console.error('❌ Laravel did not return JSON:', e);
                    alert('Unexpected server response. Check your Laravel controller.');
                    throw e;
                }

                console.log('✅ Parsed Laravel response:', json);

                if (json.success) {
                    console.log('🎉 Registration successful!');
                    faceStatus.textContent = 'Face registered successfully!';
                    faceStatus.style.background = 'rgba(40,167,69,0.8)';
                    alert('✅ Face registration completed successfully!');
                    // Update face image in UI if new image URL is returned
                    if (json.face_image_data_url) {
                        if (typeof updateCurrentFaceImage === 'function') {
                            updateCurrentFaceImage(json.face_image_data_url);
                        } else if (window.updateCurrentFaceImage) {
                            window.updateCurrentFaceImage(json.face_image_data_url);
                        }
                    }
                    // Optionally, clear captured photo and reset UI
                    capturedPhoto = null;
                    savePhotosBtn.disabled = true;
                    savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Face Registration';
                } else {
                    console.warn('⚠️ Registration failed:', json.message);
                    faceStatus.textContent = 'Registration failed: ' + (json.message || 'Unknown error');
                    faceStatus.style.background = 'rgba(220,53,69,0.8)';
                    alert('❌ Registration failed: ' + (json.message || 'Please try again.'));
                }
            } catch (err) {
                console.error('❌ Registration error:', err);
                faceStatus.textContent = 'Registration failed';
                faceStatus.style.background = 'rgba(220,53,69,0.8)';
                alert('Failed to register face: ' + err.message);
            }
            console.log('✅ Capture process completed');
        } catch (e) {
            encodingPhoto = false;
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="ri-camera-line me-2"></i>Capture Photo';
            console.error('❌ Capture error:', e);
            alert('Capture error: ' + e.message);
        }
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

        // Remove face guide and tips
        const guide = document.querySelector('.face-guide');
        const tips = document.querySelector('.capture-tips');
        if (guide) guide.remove();
        if (tips) tips.remove();

        faceStatus.textContent = 'Camera stopped';
        faceStatus.style.background = 'rgba(108,117,125,0.8)';
    });

    // Save photos (register face) - WITH COMPREHENSIVE DEBUGGING
    // Save photos (register face)
savePhotosBtn.addEventListener('click', async function() {
    console.log('💾 Save photos button clicked');

    if (!capturedPhoto) {
        alert('Please capture a photo first.');
        return;
    }

    if (!window.faceRegistrationSaveUrl) {
        console.error('❌ faceRegistrationSaveUrl is not defined!');
        alert('Save URL missing. Refresh the page.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token missing. Refresh the page.');
        return;
    }

    savePhotosBtn.disabled = true;
    savePhotosBtn.innerHTML = '<i class="ri-loader-4-line me-2"></i>Registering...';

    try {
        const faceEncoding = Array.isArray(faceData.encoding) ? faceData.encoding : [];
        const confidenceScore = faceData.confidence / 100 || 0.5;
        const faceLandmarks = faceData.landmarks || [];

        // Prepare form data
        const laravelFormData = new FormData();
        laravelFormData.append('face_encoding', JSON.stringify(faceEncoding));
        laravelFormData.append('confidence_score', confidenceScore);
        laravelFormData.append('face_landmarks', JSON.stringify(faceLandmarks));
        laravelFormData.append('face_image_data', capturedPhoto);
        laravelFormData.append('face_image_mime_type', 'image/jpeg');
        laravelFormData.append('source', 'camera_capture');

        const saveUrl = window.faceRegistrationSaveUrl;
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            alert('CSRF token missing. Refresh the page.');
            return;
        }

        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            },
            body: laravelFormData,
            credentials: 'include'
        });

        console.log('📨 Laravel raw status:', response.status, response.statusText);

        const textResponse = await response.text();
        console.log('📄 Laravel raw response:', textResponse);

        let json;
        try {
            json = JSON.parse(textResponse);
        } catch (e) {
            console.error('❌ Laravel did not return JSON:', e);
            alert('Unexpected server response. Check your Laravel controller.');
            throw e;
        }

        console.log('✅ Parsed Laravel response:', json);

        if (response.status === 409 && json.message && json.message.includes('already registered')) {
            faceStatus.textContent = json.message;
            faceStatus.style.background = 'rgba(255,193,7,0.95)';
            alert('⚠️ ' + json.message);
            savePhotosBtn.disabled = false;
            savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Photos';
            return;
        }

        if (json.success) {
            console.log('🎉 Registration successful!');
            faceStatus.textContent = 'Face registered successfully!';
            faceStatus.style.background = 'rgba(40,167,69,0.8)';
            alert('✅ Face registration completed successfully!');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            console.warn('⚠️ Registration failed:', json.message);
            faceStatus.textContent = 'Registration failed: ' + (json.message || 'Unknown error');
            faceStatus.style.background = 'rgba(220,53,69,0.8)';
            alert('❌ Registration failed: ' + (json.message || 'Please try again.'));
        }
    } catch (err) {
        console.error('❌ Registration error:', err);
        faceStatus.textContent = 'Registration failed';
        faceStatus.style.background = 'rgba(220,53,69,0.8)';
        alert('Failed to register face: ' + err.message);
    }
});


    // Clear photo
    clearPhotosBtn.addEventListener('click', function() {
        console.log('🗑️ Clearing captured photo');
        const capturedPhotosCard = document.getElementById('capturedPhotosCard');
        if (capturedPhotosCard) capturedPhotosCard.style.display = 'none';
        capturedPhoto = null;
        faceData = { landmarks: null, confidence: 0, encoding: null };
        savePhotosBtn.disabled = true;
        captureBtn.disabled = false;
        captureBtn.innerHTML = '<i class="ri-camera-line me-2"></i>Capture Photo';
        faceStatus.textContent = 'Photo cleared';
        faceStatus.style.background = 'rgba(255,193,7,0.8)';
    });

    // Display captured photo
    function displayCapturedPhoto() {
        const capturedPhotosCard = document.getElementById('capturedPhotosCard');
        const capturedImage = document.getElementById('capturedImage');
        if (capturedPhotosCard && capturedImage && capturedPhoto) {
            capturedImage.src = capturedPhoto;
            capturedPhotosCard.style.display = 'block';
            console.log('🖼️ Captured photo displayed');
        }
    }

    // Remove photo (legacy)
    window.removePhoto = function() {
        // Not used in multi-step
    };

    // Helper functions
    function dataURLtoBlob(dataURL) {
        try {
            const byteString = atob(dataURL.split(',')[1]);
            const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
            const blob = new Blob([ab], { type: mimeString });
            console.log('📸 Created blob:', { size: blob.size, type: blob.type });
            return blob;
        } catch (error) {
            console.error('❌ Error converting dataURL to blob:', error);
            return null;
        }
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
                },
                credentials: 'include'
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
});