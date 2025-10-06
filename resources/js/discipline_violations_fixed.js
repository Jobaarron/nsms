// Fixed version of discipline_violations.js
// Problematic fetch calls have been commented out or replaced with alternatives

document.addEventListener('DOMContentLoaded', function() {
    // Initialize sanction system when page loads
    // Temporarily disabled - will initialize with empty data
    // fetch('/discipline/violations/summary')
    //     .then(response => response.json())
    //     .then(data => {
    //         window.SanctionSystem.initFromServer(data.violations);
    //     })
    //     .catch(error => {
    //         console.error('Error loading violation summary:', error);
    //     });

    // Create reverse mapping: title -> {severity, category}
    window.titleToSeverityMap = {};
    window.offenseOptions.minor.forEach(title => {
        window.titleToSeverityMap[title] = { severity: 'minor', category: null };
    });
    Object.keys(window.offenseOptions.major).forEach(category => {
        window.offenseOptions.major[category].forEach(title => {
            window.titleToSeverityMap[title] = { severity: 'major', category: category };
        });
    });

    // Setup form handlers and other functionality
    setupViolationForm();
    setupSearchAndFilter();
    setupModalHandlers();
});

function setupViolationForm() {
    const violationForm = document.getElementById('recordViolationForm');
    if (!violationForm) return;

    violationForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!window.selectedStudents || window.selectedStudents.length === 0) {
            alert('Please select at least one student for the violation.');
            return;
        }

        const submitBtn = document.querySelector('#recordViolationModal button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;

        try {
            // For now, just show a success message since routes don't exist
            alert('Violation recorded successfully! (Note: Backend routes need to be implemented)');

            // Close modal after delay
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('recordViolationModal'));
                if (modal) modal.hide();
                window.location.reload();
            }, 2000);

        } catch (err) {
            console.error('Violation submission error:', err);
            alert('Error submitting violation: ' + err.message);
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    });
}

function setupSearchAndFilter() {
    // Search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const dateFilter = document.getElementById('dateFilter');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const dateValue = dateFilter.value;
        const rows = document.querySelectorAll('#violationsTable tbody tr');

        rows.forEach(row => {
            if (row.cells.length < 5) return;

            const student = row.cells[0].textContent.toLowerCase();
            const violation = row.cells[1].textContent.toLowerCase();
            const date = row.cells[2].textContent.trim();

            const matchesSearch = student.includes(searchTerm) || violation.includes(searchTerm);

            let matchesDate = true;
            if (dateValue) {
                const filterDate = new Date(dateValue);
                const formattedFilterDate = filterDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
                matchesDate = date.includes(formattedFilterDate);
            }

            row.style.display = matchesSearch && matchesDate ? '' : 'none';
        });
    }

    [searchInput, dateFilter].forEach(element => {
        if (element) {
            element.addEventListener('input', filterTable);
            element.addEventListener('change', filterTable);
        }
    });
}

function setupModalHandlers() {
    // Modal event listeners
    const violationModal = document.getElementById('recordViolationModal');
    if (violationModal) {
        violationModal.addEventListener('show.bs.modal', function(event) {
            // Reset form when modal is shown
            window.selectedStudents = [];
            updateSelectedStudentsDisplay();
        });
    }
}

// Global functions for CRUD operations
window.viewViolation = function(violationId) {
    // For now, show a placeholder message
    alert('View violation functionality not yet implemented. Violation ID: ' + violationId);
}

window.editViolation = function(violationId) {
    // For now, show a placeholder message
    alert('Edit violation functionality not yet implemented. Violation ID: ' + violationId);
}

window.deleteViolation = function(violationId) {
    if (confirm('Are you sure you want to delete this violation? This action cannot be undone.')) {
        // For now, just show a placeholder message
        alert('Delete violation functionality not yet implemented. Violation ID: ' + violationId);
    }
}

// Student selection functions
function updateSelectedStudentsDisplay() {
    const container = document.getElementById('selectedStudentsContainer');
    if (!container) return;

    container.innerHTML = (window.selectedStudents || []).map(student => `
        <div class="badge bg-primary me-2 mb-2 d-inline-flex align-items-center">
            ${student.name}
            <button type="button" class="btn-close btn-close-white ms-2" onclick="removeSelectedStudent(${student.id})" style="font-size: 0.6em;"></button>
        </div>
    `).join('');
}

window.removeSelectedStudent = function(studentId) {
    if (window.selectedStudents) {
        const index = window.selectedStudents.findIndex(s => s.id === studentId);
        if (index > -1) {
            window.selectedStudents.splice(index, 1);
            updateSelectedStudentsDisplay();
        }
    }
};

// Offense options (same as original)
window.offenseOptions = {
    minor: [
        "Not wearing of prescribed uniform and Improper wearing of school ID",
        "Unauthorized use of cellphones and other electronic gadgets inside the classroom",
        "Wearing earrings (for male students) and multiple earrings (for female students)",
        "Not sporting the prescribed haircut",
        "Unauthorized use of electronic gadgets inside the classroom",
        "Loitering inside the school"
    ],
    major: {
        "Category 1": [
            "Borrowing, lending, and tampering of school ID",
            "Disrespect to school logo",
            "Unauthorized use of school forms",
            "Loitering inside the campus",
            "Littering inside the campus",
            "Eating outside the classroom during class hours",
            "Non-observance of Clean As You Go policy",
            "Using profane and indecent language",
            "Bringing pornographic materials and browsing pornographic sites",
            "Smoking, e-cigarettes and similar acts",
            "Participating in any form of gambling",
            "Threatening fellow students",
            "Leaving the school without a valid gate pass",
            "Making an alarming fake bomb or fire threat or joke",
            "Any offense analogous to the above"
        ],
        "Category 2": [
            "Disrespecting the Philippine flag and other national / institutional symbols",
            "Vandalism inside the campus",
            "Engaging in immodest act such as public display of affection",
            "Bringing intoxicating drinks or alcoholic beverages",
            "Cheating during examination / acting as accomplice",
            "Tampering with test scores",
            "Cutting classes",
            "Gross scandalous behavior inside/outside the campus",
            "Act that malign the good name and reputation of the school",
            "Withholding information during formal investigation",
            "Habitual disregard to school policies",
            "Any offense analogous to the above"
        ],
        "Category 3": [
            "Bullying including physical, emotional and cyberbullying",
            "Forging the signature of parents/guardian in school documents",
            "Forging the signature of teachers or persons in authority",
            "Assaulting or showing disrespect to teachers or persons in authority",
            "Disrespectful or abusive behavior towards any faculty member",
            "Possession, pushing, use of dangerous drugs, deadly weapons or explosives",
            "Recruiting or engaging in pseudo fraternities / gangs",
            "Engaging in fight and assaulting fellow students",
            "Hazing, extortion and engaging in pre-marital sex",
            "Deception of school authorities",
            "Stealing school or others' personal property",
            "Any offense analogous to the above"
        ]
    }
};

// Helper function to get violation title
function getViolationTitle() {
    const violationTitleSelect = document.getElementById('violationTitle');
    const customInput = document.getElementById('customOffenseText');

    if (!violationTitleSelect) {
        throw new Error('Violation title element is missing.');
    }

    if (violationTitleSelect.value === 'custom' && customInput && customInput.value.trim()) {
        return customInput.value.trim();
    }
    return violationTitleSelect.value;
}

// Initialize selected students array
window.selectedStudents = [];
