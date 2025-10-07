<x-student-layout>
    @push('styles')
        @vite('resources/css/index_student.css')
        <style>
            #video {
                width: 100%;
                max-width: 400px;
                height: 300px;
                object-fit: cover;
                border-radius: 0.5rem;
                background: #f8f9fa;
            }

            #canvas {
                display: none;
            }

            .face-preview {
                width: 150px;
                height: 150px;
                object-fit: cover;
                border-radius: 50%;
                border: 3px solid var(--primary-color);
            }

            .camera-container {
                position: relative;
                display: inline-block;
            }

            .camera-overlay {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 200px;
                height: 200px;
                border: 2px solid var(--primary-color);
                border-radius: 50%;
                pointer-events: none;
            }

            .face-status {
                position: absolute;
                bottom: 10px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.7);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                font-size: 0.875rem;
            }
        </style>
    @endpush

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="section-title mb-1">Face Registration</h2>
                    <p class="text-muted mb-0">Register your facial data for secure access</p>
                </div>
                <div class="text-end">
                    <small class="text-muted">Student ID: <strong>{{ $student->student_id }}</strong></small><br>
                    <small class="text-muted">Face Status: 
                        <span class="badge bg-{{ $student->hasFaceRegistered() ? 'success' : 'warning' }}">
                            {{ $student->hasFaceRegistered() ? 'Registered' : 'Not Registered' }}
                        </span>
                    </small>
                </div>
            </div>
        </div>
    </div>

    @if($student->hasFaceRegistered())
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success border-0 shadow-sm">
                    <div class="d-flex align-items-center">
                        <i class="ri-check-line fs-4 me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Face Already Registered</h6>
                            <p class="mb-0">Your facial data has been successfully registered. You can update it below if needed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Left Column - Camera -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-camera-line me-2"></i>Camera Capture
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="camera-container mb-4">
                        <video id="video" autoplay playsinline></video>
                        <div class="camera-overlay"></div>
                        <div class="face-status" id="faceStatus">Position your face in the circle</div>
                    </div>
                    <canvas id="canvas"></canvas>
                    
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <button type="button" class="btn btn-primary" id="startCamera">
                            <i class="ri-camera-line me-2"></i>Start Camera
                        </button>
                        <button type="button" class="btn btn-success" id="captureBtn" disabled>
                            <i class="ri-camera-3-line me-2"></i>Capture Photo
                        </button>
                        <button type="button" class="btn btn-secondary" id="stopCamera" disabled>
                            <i class="ri-stop-line me-2"></i>Stop Camera
                        </button>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Instructions:</h6>
                        <ul class="mb-0 text-start">
                            <li>Click "Start Camera" to begin</li>
                            <li>Position your face within the circle overlay</li>
                            <li>Ensure good lighting and look directly at the camera</li>
                            <li>Click "Capture Photo" when ready</li>
                            <li>Review and save your facial data</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Captured Photos -->
            <div class="card border-0 shadow-sm" id="capturedPhotosCard" style="display: none;">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">
                        <i class="ri-image-line me-2"></i>Captured Photos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row" id="capturedPhotos">
                        <!-- Captured photos will be displayed here -->
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" id="savePhotos" disabled>
                            <i class="ri-save-line me-2"></i>Save Face Registration
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="clearPhotos">
                            <i class="ri-delete-bin-line me-2"></i>Clear All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Information -->
        <div class="col-lg-4">
            @if($student->hasFaceRegistered())
                @php
                    $faceRegistration = $student->activeFaceRegistration()->first();
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">
                            <i class="ri-user-line me-2"></i>Current Registration
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        @if($faceRegistration && $faceRegistration->face_image_data_url)
                            <img src="{{ $faceRegistration->face_image_data_url }}" alt="Registered Face" class="face-preview mb-3">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 mx-auto face-preview">
                                <i class="ri-user-line fs-1 text-muted"></i>
                            </div>
                        @endif
                        <h6 class="fw-bold mb-1">{{ $student->full_name ?? ($student->first_name . ' ' . $student->last_name) }}</h6>
                        <p class="text-muted small mb-2">{{ $student->student_id }}</p>
                        @if($faceRegistration)
                            <small class="text-muted d-block">Registered: {{ $faceRegistration->registered_at->format('M d, Y') }}</small>
                            <small class="text-muted d-block">Source: {{ ucfirst(str_replace('_', ' ', $faceRegistration->source)) }}</small>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="card-title mb-0">
                        <i class="ri-information-line me-2"></i>About Face Registration
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary mb-2">Why register your face?</h6>
                    <ul class="small text-muted mb-3">
                        <li>Secure access to school facilities</li>
                        <li>Quick attendance marking</li>
                        <li>Enhanced security for student portal</li>
                        <li>Streamlined identification process</li>
                    </ul>

                    <h6 class="text-primary mb-2">Privacy & Security</h6>
                    <ul class="small text-muted mb-3">
                        <li>Your facial data is encrypted and secure</li>
                        <li>Only used for school identification purposes</li>
                        <li>You can update or remove it anytime</li>
                        <li>Complies with data protection standards</li>
                    </ul>

                    <h6 class="text-primary mb-2">Tips for best results</h6>
                    <ul class="small text-muted">
                        <li>Use good lighting (natural light preferred)</li>
                        <li>Look directly at the camera</li>
                        <li>Remove glasses if possible</li>
                        <li>Keep a neutral expression</li>
                        <li>Capture multiple angles</li>
                    </ul>
                </div>
            </div>

            @if($student->hasFaceRegistered())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h6 class="card-title mb-0">
                            <i class="ri-history-line me-2"></i>Registration History
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $faceRegistrations = $student->faceRegistrations()->orderBy('registered_at', 'desc')->get();
                        @endphp
                        
                        @if($faceRegistrations->count() > 0)
                            <div class="timeline">
                                @foreach($faceRegistrations as $registration)
                                    <div class="timeline-item {{ $registration->is_active ? 'completed' : '' }}">
                                        <h6 class="mb-1 {{ $registration->is_active ? 'text-success' : 'text-muted' }}">
                                            <i class="ri-camera-line me-1"></i>Face Registration
                                            @if($registration->is_active)
                                                <span class="badge bg-success ms-2">Active</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted d-block">{{ $registration->registered_at->format('M d, Y g:i A') }}</small>
                                        <small class="text-muted">Source: {{ ucfirst(str_replace('_', ' ', $registration->source)) }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="ri-history-line fs-3 text-muted mb-2"></i>
                                <p class="text-muted small mb-0">No registration history</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pb-0">
                    <h6 class="card-title mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                            <i class="ri-dashboard-line me-2"></i>Back to Dashboard
                        </a>
                        @if($student->hasFaceRegistered())
                            <button class="btn btn-outline-danger" onclick="confirmDeleteFace()">
                                <i class="ri-delete-bin-line me-2"></i>Remove Face Data
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            window.faceRegistrationSaveUrl = '{{ route("student.face-registration.save") }}';
            window.faceRegistrationDeleteUrl = '{{ route("student.face-registration.delete") }}';
        </script>
        @vite('resources/js/face-registration.js')
    @endpush
</x-student-layout>
