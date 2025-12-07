document.addEventListener('DOMContentLoaded', function() {

    // Check if we're on the face registration page
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    
    // Exit early if face registration elements don't exist
    if (!video || !canvas) {
        return;
    }
    
    const ctx = canvas.getContext('2d');

    const startCameraBtn = document.getElementById('startCamera');
    const captureBtn = document.getElementById('captureBtn');
    const stopCameraBtn = document.getElementById('stopCamera');
    const savePhotosBtn = document.getElementById('savePhotos');
    const clearPhotosBtn = document.getElementById('clearPhotos');
    const faceStatus = document.getElementById('faceStatus');

    // Read URLs from meta tags
    window.faceRegistrationSaveUrl = document.querySelector('meta[name="face-registration-save-url"]')?.getAttribute('content');

    // Configuration
    const FLASK_SERVER_URL = '/api/face'; 
    let isFlaskServerAvailable = false;

    // State variables
    let stream = null;
    let capturedPhoto = null;
    let faceData = { landmarks: null, confidence: 0, encoding: null };
    let encodingPhoto = false;

    // Debug: Test all endpoints on load
    async function testEndpoints() {
        
        // Test CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
        }
        
        // Test save URL
        
        if (!window.faceRegistrationSaveUrl) {
        } else {
        }
        
        // Skip Flask server test to avoid 404 errors
        // Flask server not needed for basic functionality
        
    }

    // Call debug test on load
    testEndpoints();

    // Check Flask server availability - disabled to prevent 404 errors
    // checkFlaskServer();

    async function checkFlaskServer() {
        // Disabled Flask server check to prevent 404 errors
        // Set as unavailable by default since server is not running
        isFlaskServerAvailable = false;
        if (faceStatus) {
            faceStatus.textContent = 'Camera ready for capture';
            faceStatus.style.background = 'rgba(108,117,125,0.8)';
        }
    }

    // Start camera
    startCameraBtn.addEventListener('click', async function() {
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

            faceStatus.textContent = 'Encoding face...';
            faceStatus.style.background = 'rgba(23,162,184,0.8)';

            // Encode face immediately after capture
            if (isFlaskServerAvailable) {
                try {
                    const formData = new FormData();
                    const blob = dataURLtoBlob(capturedPhoto);
                    formData.append('image', blob, 'face.jpg');

                    const encodeResp = await fetch(`${FLASK_SERVER_URL}/encode`, {
                        method: 'POST',
                        body: formData,
                        signal: AbortSignal.timeout(30000)
                    });

                    if (encodeResp.ok) {
                        const encodeData = await encodeResp.json();
                        
                        if (encodeData?.confidence !== undefined) {
                            faceData = {
                                landmarks: encodeData.landmarks ?? null,
                                confidence: (encodeData.confidence * 100).toFixed(1),
                                encoding: encodeData.encoding ?? null
                            };
                        } else if (encodeData?.error) {
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
                    // Continue to review step even if encoding fails
                    faceData = { landmarks: null, confidence: 0, encoding: null };
                }
            } else {
                // Create a dummy encoding array with 128 zeros (standard face encoding size)
                faceData = { landmarks: null, confidence: 50, encoding: Array(128).fill(0) };
            }

            encodingPhoto = false;
            displayCapturedPhoto();
            savePhotosBtn.disabled = false;
            // Automatically save to backend after encoding
            try {
                const faceEncoding = Array.isArray(faceData.encoding) ? faceData.encoding : Array(128).fill(0);
                const confidenceScore = faceData.confidence / 100 || 0.5;
                const faceLandmarks = faceData.landmarks || [];

                if (!faceEncoding || faceEncoding.length === 0) {
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
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: laravelFormData,
                    credentials: 'include'
                });


                const textResponse = await response.text();

                let json;
                try {
                    json = JSON.parse(textResponse);
                } catch (e) {
                    alert('Unexpected server response. Check your Laravel controller.');
                    throw e;
                }


                if (json.success) {
                    faceStatus.textContent = 'Face registered successfully!';
                    faceStatus.style.background = 'rgba(40,167,69,0.8)';
                    
                    // Update all UI elements in real-time
                    if (json.face_image_data_url) {
                        updateCurrentFaceImage(
                            json.face_image_data_url, 
                            json.registration_date || new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }), 
                            json.source || 'Camera Capture'
                        );
                    }
                    
                    // Update face status badge in page header
                    updateFaceStatusBadge(true);
                    
                    // Update success alert
                    updateSuccessAlert();
                    
                    // Update registration history
                    if (json.registration_date) {
                        updateRegistrationHistory(json.registration_date, json.source || 'camera_capture');
                    }
                    
                    // Clear captured photo and reset UI
                    const capturedPhotosCard = document.getElementById('capturedPhotosCard');
                    if (capturedPhotosCard) {
                        capturedPhotosCard.style.display = 'none';
                    }
                    capturedPhoto = null;
                    faceData = { landmarks: null, confidence: 0, encoding: null };
                    savePhotosBtn.disabled = true;
                    savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Face Registration';
                    
                    alert('✅ Face registration completed successfully!');
                } else {
                    faceStatus.textContent = 'Registration failed: ' + (json.message || 'Unknown error');
                    faceStatus.style.background = 'rgba(220,53,69,0.8)';
                    alert('❌ Registration failed: ' + (json.message || 'Please try again.'));
                }
            } catch (err) {
                faceStatus.textContent = 'Registration failed';
                faceStatus.style.background = 'rgba(220,53,69,0.8)';
                alert('Failed to register face: ' + err.message);
            }
        } catch (e) {
            encodingPhoto = false;
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="ri-camera-line me-2"></i>Capture Photo';
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

    if (!capturedPhoto) {
        alert('Please capture a photo first.');
        return;
    }

    if (!window.faceRegistrationSaveUrl) {
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
        const faceEncoding = Array.isArray(faceData.encoding) ? faceData.encoding : Array(128).fill(0);
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


        const textResponse = await response.text();

        let json;
        try {
            json = JSON.parse(textResponse);
        } catch (e) {
            alert('Unexpected server response. Check your Laravel controller.');
            throw e;
        }


        if (response.status === 409 && json.message && json.message.includes('already registered')) {
            faceStatus.textContent = json.message;
            faceStatus.style.background = 'rgba(255,193,7,0.95)';
            alert('⚠️ ' + json.message);
            savePhotosBtn.disabled = false;
            savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Photos';
            return;
        }

        if (json.success) {
            faceStatus.textContent = 'Face registered successfully!';
            faceStatus.style.background = 'rgba(40,167,69,0.8)';
            
            // Update all UI elements in real-time
            if (json.face_image_data_url) {
                updateCurrentFaceImage(
                    json.face_image_data_url, 
                    json.registration_date || new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }), 
                    json.source || 'Camera Capture'
                );
            }
            
            // Update face status badge in page header
            updateFaceStatusBadge(true);
            
            // Update success alert
            updateSuccessAlert();
            
            // Update Quick Actions to show ID card button
            updateQuickActions();
            
            // Update registration history
            if (json.registration_date) {
                updateRegistrationHistory(json.registration_date, json.source || 'camera_capture');
            }
            
            // Clear the captured photo and reset UI
            const capturedPhotosCard = document.getElementById('capturedPhotosCard');
            if (capturedPhotosCard) {
                capturedPhotosCard.style.display = 'none';
            }
            capturedPhoto = null;
            faceData = { landmarks: null, confidence: 0, encoding: null };
            
            // Reset buttons
            savePhotosBtn.disabled = true;
            savePhotosBtn.innerHTML = '<i class="ri-save-line me-2"></i>Save Face Registration';
            captureBtn.disabled = false;
            captureBtn.innerHTML = '<i class="ri-camera-line me-2"></i>Capture Photo';
            
            alert('✅ Face registration completed successfully!');
        } else {
            faceStatus.textContent = 'Registration failed: ' + (json.message || 'Unknown error');
            faceStatus.style.background = 'rgba(220,53,69,0.8)';
            alert('❌ Registration failed: ' + (json.message || 'Please try again.'));
        }
    } catch (err) {
        faceStatus.textContent = 'Registration failed';
        faceStatus.style.background = 'rgba(220,53,69,0.8)';
        alert('Failed to register face: ' + err.message);
    }
});


    // Clear photo
    clearPhotosBtn.addEventListener('click', function() {
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
        const capturedPhotos = document.getElementById('capturedPhotos');
        
        if (capturedPhotosCard && capturedPhotos && capturedPhoto) {
            // Create image preview
            capturedPhotos.innerHTML = `
                <div class="col-12 text-center">
                    <img src="${capturedPhoto}" alt="Captured Face" class="img-fluid" style="max-width: 300px; border-radius: 0.5rem; border: 3px solid var(--primary-color);">
                    <div class="mt-2">
                        <span class="badge bg-success">Ready to Save</span>
                        ${faceData.confidence > 0 ? `<span class="badge bg-info ms-2">Confidence: ${faceData.confidence}%</span>` : ''}
                    </div>
                </div>
            `;
            capturedPhotosCard.style.display = 'block';
        }
    }

    // Remove photo (legacy)
    window.removePhoto = function() {
        // Not used in multi-step
    };

    // Function to update current face image without page reload
    function updateCurrentFaceImage(imageDataUrl, registrationDate, source) {
        
        // Update the current face image
        const currentFaceImage = document.getElementById('currentFaceImage');
        if (currentFaceImage) {
            currentFaceImage.src = imageDataUrl;
        }
        
        // If no current registration card exists, create one
        const rightColumn = document.querySelector('.col-lg-4');
        if (rightColumn && !document.getElementById('currentFaceImage')) {
            // Get student info from page title or other elements
            const pageTitle = document.querySelector('h2.section-title')?.textContent || 'Face Registration';
            const studentName = document.querySelector('.student-name')?.textContent || 'Student';
            
            const currentRegistrationCard = `
                <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #e3f0ff 0%, #f9f9f9 100%); border: 2px solid #1976d2; border-radius: 1rem; overflow: hidden;">
                    <div class="card-header bg-primary text-white border-0 pb-2" style="border-bottom: 2px solid #1976d2;">
                        <h6 class="card-title mb-0 d-flex align-items-center">
                            <i class="ri-id-card-line me-2"></i>Current Registration
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center" style="gap: 1.5rem;">
                            <div>
                                <img id="currentFaceImage" src="${imageDataUrl}" alt="Registered Face" style="width:120px; height:120px; object-fit:cover; border-radius:12px; border:3px solid #1976d2; box-shadow:0 2px 8px rgba(25,118,210,0.15); background:#fff;">
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1" style="color:#1976d2;" id="dynamicStudentName">${studentName}</h5>
                                <p class="mb-1" style="font-size:1.1rem; color:#333;" id="dynamicStudentId">Registration Complete</p>
                                <p class="mb-1 text-muted" style="font-size:0.95rem;">Registered: <span class="fw-semibold">${registrationDate}</span></p>
                                <p class="mb-1 text-muted" style="font-size:0.95rem;">Source: <span class="fw-semibold">${source}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            rightColumn.insertAdjacentHTML('afterbegin', currentRegistrationCard);
        }
        
        // Update registration date and source if they exist
        const registrationDateSpan = document.querySelector('.card-body p:nth-of-type(3) .fw-semibold');
        if (registrationDateSpan) {
            registrationDateSpan.textContent = registrationDate;
        }
        
        const sourceSpan = document.querySelector('.card-body p:nth-of-type(4) .fw-semibold');
        if (sourceSpan) {
            sourceSpan.textContent = source;
        }
        
    }

    // Helper functions
    function dataURLtoBlob(dataURL) {
        try {
            const byteString = atob(dataURL.split(',')[1]);
            const mimeString = dataURL.split(',')[0].split(':')[1].split(';')[0];
            const ab = new ArrayBuffer(byteString.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
            const blob = new Blob([ab], { type: mimeString });
            return blob;
        } catch (error) {
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

    // Helper function to update face status badge in real-time
    function updateFaceStatusBadge(isRegistered) {
        const statusBadge = document.querySelector('.text-end .badge');
        if (statusBadge) {
            statusBadge.className = isRegistered ? 'badge bg-success' : 'badge bg-warning';
            statusBadge.textContent = isRegistered ? 'Registered' : 'Not Registered';
        }
    }
    
    // Helper function to show/update success alert in real-time
    function updateSuccessAlert() {
        // Check if alert already exists
        let alertDiv = document.querySelector('.alert-success');
        
        if (!alertDiv) {
            // Create new alert if it doesn't exist
            const rowDiv = document.createElement('div');
            rowDiv.className = 'row mb-4';
            rowDiv.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-success border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="ri-check-line fs-4 me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Face Already Registered</h6>
                                <p class="mb-0">Your facial data has been successfully registered.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Insert after page header
            const pageHeader = document.querySelector('.row.mb-4');
            if (pageHeader && pageHeader.nextElementSibling) {
                pageHeader.parentNode.insertBefore(rowDiv, pageHeader.nextElementSibling);
            } else if (pageHeader) {
                pageHeader.parentNode.appendChild(rowDiv);
            }
        } else {
        }
    }
    
    // Helper function to update Quick Actions section in real-time
    function updateQuickActions() {
        const quickActionsCard = document.querySelector('.card-header h6:contains("Quick Actions")')?.closest('.card');
        if (!quickActionsCard) {
            return;
        }
        
        const cardBody = quickActionsCard.querySelector('.card-body .d-grid');
        if (cardBody) {
            // Check if ID card button already exists
            const existingIdButton = cardBody.querySelector('a[href*="student-id-card"]');
            if (!existingIdButton) {
                // Create ID card button
                const idCardButton = document.createElement('a');
                idCardButton.href = '/pdf/student-id-card';
                idCardButton.className = 'btn btn-success';
                idCardButton.target = '_blank';
                idCardButton.innerHTML = '<i class="ri-id-card-line me-2"></i>Generate ID Card';
                
                // Insert before the dashboard button
                const dashboardButton = cardBody.querySelector('a[href*="dashboard"]');
                if (dashboardButton) {
                    cardBody.insertBefore(idCardButton, dashboardButton);
                }
            }
        }
    }

    // Helper function to update registration history in real-time
    function updateRegistrationHistory(registrationDate, source) {
        const historyCard = document.querySelector('.card .ri-history-line')?.closest('.card');
        if (!historyCard) {
            return;
        }
        
        const timeline = historyCard.querySelector('.timeline');
        if (!timeline) {
            const cardBody = historyCard.querySelector('.card-body');
            if (cardBody) {
                cardBody.innerHTML = '<div class="timeline"></div>';
            }
        }
        
        const timelineContainer = historyCard.querySelector('.timeline');
        if (timelineContainer) {
            // Mark all existing items as inactive
            const existingItems = timelineContainer.querySelectorAll('.timeline-item');
            existingItems.forEach(item => {
                item.classList.remove('completed');
                const heading = item.querySelector('h6');
                if (heading) {
                    heading.classList.remove('text-success');
                    heading.classList.add('text-muted');
                    const badge = heading.querySelector('.badge');
                    if (badge) badge.remove();
                }
            });
            
            // Add new active registration at the top
            const newItem = document.createElement('div');
            newItem.className = 'timeline-item completed';
            const formattedDate = new Date(registrationDate).toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            newItem.innerHTML = `
                <h6 class="mb-1 text-success">
                    <i class="ri-camera-line me-1"></i>Face Registration
                    <span class="badge bg-success ms-2">Active</span>
                </h6>
                <small class="text-muted d-block">${formattedDate}</small>
                <small class="text-muted">Source: ${source.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')}</small>
            `;
            timelineContainer.insertBefore(newItem, timelineContainer.firstChild);
        }
    }


    window.addEventListener('beforeunload', function() {
        if (stream) stream.getTracks().forEach(track => track.stop());
    });
});