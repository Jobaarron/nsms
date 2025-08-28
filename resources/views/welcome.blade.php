<x-layout>
    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8 mx-auto text-center hero-content">
                    <h4 class="text-uppercase fw-bold mb-3 text-accent">Welcome to Nicolites Portal</h4>
                    <h1 class="display-3 fw-bold mb-4">Excellence in Education</h1>
                    <p class="lead mb-5 fs-5">
                        Nicolites Montessori School is a non-sectarian private school offering complete Pre-school Programs, Elementary, Junior High School and Senior High School with the following strands: ABM, HUMSS, STEM and TVL. NMS was established in 2004, it is one of the fastest-growing private schools in Nasugbu, Batangas.
                    </p>
                    <a href="/enroll"> 
                        <button class="btn btn-custom btn-lg">ENROLL NOW!</button>
                      </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Bulletins Section -->
    <section id="bulletins" class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="page-header mb-3">Latest Bulletins & Announcements</h2>
                    <p class="page-subheader">Stay updated with the latest news and announcements from Nicolites Montessori School</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="content-card p-4 h-100">
                        <div class="bulletin-date mb-2">
                            {{-- <small class="text-muted">{{ date('M d, Y') }}</small> --}}
                        </div>
                        <h5 class="fw-bold text-primary mb-3">Group Color Uniform</h5>
                        <p class="mb-3">We are now accepting applications for the upcoming school year. Early bird discounts available until March 31, 2024.</p>
                        {{-- <a href="#" class="btn btn-outline-primary btn-sm">Read More</a> --}}
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="content-card p-4 h-100">
                        <div class="bulletin-date mb-2">
                            {{-- <small class="text-muted">{{ date('M d, Y', strtotime('-3 days')) }}</small> --}}
                        </div>
                        <h5 class="fw-bold text-primary mb-3">Facility Updates</h5>
                        <p class="mb-3">New 4-Storey Building (E) to be completed by 2024.</p>
                        {{-- <a href="#" class="btn btn-outline-primary btn-sm">Read More</a> --}}
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="content-card p-4 h-100">
                        <div class="bulletin-date mb-2">
                            {{-- <small class="text-muted">{{ date('M d, Y', strtotime('-1 week')) }}</small> --}}
                        </div>
                        <h5 class="fw-bold text-primary mb-3">NMS Videos</h5>
                        <p class="mb-3">Enjoy watching & listening to our music!</p>
                        {{-- <a href="#" class="btn btn-outline-primary btn-sm">Read More</a> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs & Courses Section -->
    <section id="programs" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="page-header mb-3">Our Academic Programs</h2>
                    <p class="page-subheader">Comprehensive education from early childhood to senior high school</p>
                </div>
            </div>
            <div class="row g-4">
                <!-- Pre-School -->
                <div class="col-lg-6 col-md-6">
                    <div class="content-card p-4 h-100 program-card">
                        <div class="program-icon mb-3">
                            <i class="ri-bear-smile-line fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Pre-School Program</h4>
                        <p class="mb-3">Montessori-based early childhood education focusing on holistic development through play-based learning.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="ri-check-line text-success me-2"></i>Nursery </li>
                            <li><i class="ri-check-line text-success me-2"></i>Kindergarten</li>
                            <li><i class="ri-check-line text-success me-2"></i>Montessori Method</li>
                            <li><i class="ri-check-line text-success me-2"></i>Creative Arts & Music</li>
                        </ul>
                        <a href="#" class="btn btn-custom">Learn More</a>
                    </div>
                </div>

                <!-- Elementary -->
                <div class="col-lg-6 col-md-6">
                    <div class="content-card p-4 h-100 program-card">
                        <div class="program-icon mb-3">
                            <i class="ri-book-open-line fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Elementary Program</h4>
                        <p class="mb-3">Complete elementary education with strong foundation in core subjects and character development.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="ri-check-line text-success me-2"></i>Grades 1-6</li>
                            <li><i class="ri-check-line text-success me-2"></i>Core Academic Subjects</li>
                            <li><i class="ri-check-line text-success me-2"></i>Values Education</li>
                            <li><i class="ri-check-line text-success me-2"></i>Extracurricular Activities</li>
                        </ul>
                        <a href="#" class="btn btn-custom">Learn More</a>
                    </div>
                </div>

                <!-- Junior High School -->
                <div class="col-lg-6 col-md-6">
                    <div class="content-card p-4 h-100 program-card">
                        <div class="program-icon mb-3">
                            <i class="ri-graduation-cap-line fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Junior High School</h4>
                        <p class="mb-3">Comprehensive secondary education preparing students for senior high school and beyond.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="ri-check-line text-success me-2"></i>Grades 7-10</li>
                            <li><i class="ri-check-line text-success me-2"></i>Enhanced Curriculum</li>
                            <li><i class="ri-check-line text-success me-2"></i>Laboratory Sciences</li>
                            <li><i class="ri-check-line text-success me-2"></i>Research Projects</li>
                        </ul>
                        <a href="#" class="btn btn-custom">Learn More</a>
                    </div>
                </div>

                <!-- Senior High School -->
                <div class="col-lg-6 col-md-6">
                    <div class="content-card p-4 h-100 program-card">
                        <div class="program-icon mb-3">
                            <i class="ri-award-line fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Senior High School</h4>
                        <p class="mb-3">Specialized tracks preparing students for college and career readiness.</p>
                        <ul class="list-unstyled mb-4">
                            <li><i class="ri-check-line text-success me-2"></i>ABM (Accountancy, Business & Management)</li>
                            <li><i class="ri-check-line text-success me-2"></i>HUMSS (Humanities & Social Sciences)</li>
                            <li><i class="ri-check-line text-success me-2"></i>STEM (Science, Technology, Engineering & Math)</li>
                            <li><i class="ri-check-line text-success me-2"></i>TVL (Technical-Vocational-Livelihood)</li>
                        </ul>
                        <a href="#" class="btn btn-custom">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="page-header mb-3">Get In Touch</h2>
                    <p class="page-subheader">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                </div>
            </div>
            <div class="row g-5">
                <!-- Contact Form (STATIC / WIP)-->
                <div class="col-lg-8">
                    <div class="content-card p-4">
                        <h4 class="fw-bold mb-4">Send us a Message</h4>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject *</label>
                                    <select class="form-select" id="subject" required>
                                        <option value="">Choose a subject...</option>
                                        <option value="enrollment">Enrollment Inquiry</option>
                                        <option value="academic">Academic Information</option>
                                        <option value="admission">Admission Requirements</option>
                                        <option value="facilities">School Facilities</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message *</label>
                                    <textarea class="form-control" id="message" rows="5" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-custom btn-lg">
                                        <i class="ri-send-plane-line me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="content-card p-4 h-100">
                        <h4 class="fw-bold mb-4">Contact Information</h4>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="ri-map-pin-line fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Address</h6>
                                    <p class="mb-0 text-muted">
                                        San Roque St., Brgy 4<br>
                                        Nasugbu, Batangas<br>
                                        4231 Philippines
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="ri-phone-line fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Phone</h6>
                                    <p class="mb-0 text-muted">
                                        <a href="tel:+63431234567" class="text-decoration-none">(043) 416-0149</a><br>
                                        <a href="tel:+639171234567" class="text-decoration-none">0917-708-6667</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="ri-mail-line fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="mb-0 text-muted">
                                        {{-- <a href="mailto:info@nicolites.edu.ph" class="text-decoration-none">admissions@nicolitesmontessoriscool.com</a><br> --}}
                                        <a href="mailto:admissions@nicolites.edu.ph" class="text-decoration-none">admissions@nicolites.edu.ph</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="ri-time-line fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Office Hours</h6>
                                    <p class="mb-0 text-muted">
                                        Monday - Friday: 7:00 AM - 5:00 PM<br>
                                        Saturday: 8:00 AM - 12:00 PM<br>
                                        Sunday: Closed
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="social-links-contact mt-4">
                            <h6 class="fw-bold mb-3">Follow Us</h6>
                            <div class="d-flex gap-3">
                                <a href="https://www.facebook.com/NicolitesMontessoriSchool/" target="_blank" class="btn btn-outline-primary btn-sm" aria-label="Facebook">
                                    <i class="ri-facebook-fill"></i>
                                </a>
                                {{-- <a href="#" class="btn btn-outline-primary btn-sm" aria-label="Instagram">
                                    <i class="ri-instagram-line"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm" aria-label="Twitter">
                                    <i class="ri-twitter-fill"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm" aria-label="YouTube">
                                    <i class="ri-youtube-line"></i>
                                </a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section id="why-choose-us" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="page-header mb-3">Why Choose Nicolites?</h2>
                    <p class="page-subheader">Discover what makes us one of the fastest-growing schools in Nasugbu, Batangas</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="content-card p-4 text-center h-100 feature-card">
                        <div class="feature-icon mb-3">
                            <i class="ri-award-line fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">20+ Years of Excellence</h5>
                        <p class="text-muted">Established in 2004, we have been providing quality education for over two decades.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="content-card p-4 text-center h-100 feature-card">
                        <div class="feature-icon mb-3">
                            <i class="ri-user-star-line fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Qualified Teachers</h5>
                        <p class="text-muted">Our dedicated faculty members are highly trained and passionate about education.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="content-card p-4 text-center h-100 feature-card">
                        <div class="feature-icon mb-3">
                            <i class="ri-microscope-line fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Modern Facilities</h5>
                        <p class="text-muted">State-of-the-art classrooms, laboratories, and learning environments for optimal education.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="content-card p-4 text-center h-100 feature-card">
                        <div class="feature-icon mb-3">
                            <i class="ri-heart-line fs-1 text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Holistic Development</h5>
                        <p class="text-muted">We focus on academic excellence while nurturing character and values.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Stats Section -->
    <section id="stats" class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold mb-2">Value here</h2>
                        <p class="mb-0">Label here</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold mb-2">Value here</h2>
                        <p class="mb-0">Label here</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold mb-2">Value here</h2>
                        <p class="mb-0">Label here</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold mb-2">Value here</h2>
                        <p class="mb-0">Label here</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section id="cta" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="content-card p-5 text-center cta-card">
                        <h2 class="fw-bold mb-3">Ready to Join Our School Community?</h2>
                        <p class="lead mb-4">Take the first step towards your child's bright future. Enroll now and be part of the Nicolites family.</p>
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="/enroll" class="btn btn-custom btn-lg">
                                <i class="ri-user-add-line me-2"></i>Start Enrollment
                            </a>
                            <a href="#contact" class="btn btn-outline-primary btn-lg">
                                <i class="ri-phone-line me-2"></i>Contact Us
                            </a>  {{-- WIP --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layout>