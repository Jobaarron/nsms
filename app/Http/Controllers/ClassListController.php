<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Section;
use App\Models\FacultyAssignment;
use Illuminate\Support\Facades\Auth;

class ClassListController extends Controller
{
    /**
     * Display class lists with filtering options
     */
    public function index(Request $request)
    {
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        // Get filter parameters
        $selectedGrade = $request->get('grade_level');
        $selectedStrand = $request->get('strand');
        $selectedTrack = $request->get('track');
        $selectedSection = $request->get('section');
        
        // Get all available grade levels from students
        $availableGrades = Student::where('academic_year', $currentAcademicYear)
                                 ->where('is_active', true)
                                 ->distinct()
                                 ->pluck('grade_level')
                                 ->sort()
                                 ->values();
        
        // Grade level order for proper sorting
        $gradeOrder = [
            'Nursery', 'Junior Casa', 'Senior Casa', 'Kinder',
            'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6',
            'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12'
        ];
        
        // Sort grades according to proper order
        $orderedGrades = collect($gradeOrder)->filter(function($grade) use ($availableGrades) {
            return $availableGrades->contains($grade);
        });
        
        // Get available strands for selected grade (if Grade 11 or 12)
        $availableStrands = collect();
        if ($selectedGrade && in_array($selectedGrade, ['Grade 11', 'Grade 12'])) {
            $availableStrands = Student::where('grade_level', $selectedGrade)
                                     ->where('academic_year', $currentAcademicYear)
                                     ->where('is_active', true)
                                     ->whereNotNull('strand')
                                     ->distinct()
                                     ->pluck('strand')
                                     ->sort()
                                     ->values();
        }
        
        // Get available tracks for selected strand (if TVL)
        $availableTracks = collect();
        if ($selectedStrand === 'TVL') {
            $availableTracks = Student::where('grade_level', $selectedGrade)
                                    ->where('strand', 'TVL')
                                    ->where('academic_year', $currentAcademicYear)
                                    ->where('is_active', true)
                                    ->whereNotNull('track')
                                    ->distinct()
                                    ->pluck('track')
                                    ->sort()
                                    ->values();
        }
        
        // Get available sections for selected grade/strand/track
        $availableSections = collect();
        if ($selectedGrade) {
            $sectionsQuery = Student::where('grade_level', $selectedGrade)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true);
            
            if ($selectedStrand) {
                $sectionsQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $sectionsQuery->where('track', $selectedTrack);
            }
            
            $availableSections = $sectionsQuery->distinct()
                                             ->pluck('section')
                                             ->sort()
                                             ->values();
        }
        
        // Get students for selected filters
        $students = collect();
        $classInfo = null;
        $classAdviser = null;
        $subjectTeachers = collect();
        
        if ($selectedGrade && $selectedSection) {
            // Build the query
            $studentsQuery = Student::where('grade_level', $selectedGrade)
                                   ->where('section', $selectedSection)
                                   ->where('academic_year', $currentAcademicYear)
                                   ->where('is_active', true);
            
            // Add strand/track filters if applicable
            if ($selectedStrand) {
                $studentsQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $studentsQuery->where('track', $selectedTrack);
            }
            
            // Get students ordered by name
            $students = $studentsQuery->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->get();
            
            // Build class info string
            $classInfo = $selectedGrade;
            if ($selectedStrand) {
                $classInfo .= ' ' . $selectedStrand;
                if ($selectedTrack) {
                    $classInfo .= '-' . $selectedTrack;
                }
            }
            $classInfo .= ' - Section ' . $selectedSection;
            
            // Get class adviser
            $adviserQuery = FacultyAssignment::where('grade_level', $selectedGrade)
                                           ->where('section', $selectedSection)
                                           ->where('assignment_type', 'class_adviser')
                                           ->where('academic_year', $currentAcademicYear)
                                           ->where('status', 'active')
                                           ->with(['teacher.user']);
            
            if ($selectedStrand) {
                $adviserQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $adviserQuery->where('track', $selectedTrack);
            }
            
            $classAdviser = $adviserQuery->first();
            
            // Get subject teachers
            $teachersQuery = FacultyAssignment::where('grade_level', $selectedGrade)
                                            ->where('section', $selectedSection)
                                            ->where('assignment_type', 'subject_teacher')
                                            ->where('academic_year', $currentAcademicYear)
                                            ->where('status', 'active')
                                            ->with(['teacher.user', 'subject']);
            
            if ($selectedStrand) {
                $teachersQuery->where('strand', $selectedStrand);
            }
            
            if ($selectedTrack) {
                $teachersQuery->where('track', $selectedTrack);
            }
            
            $subjectTeachers = $teachersQuery->get();
        }
        
        return view('admin.class-lists', compact(
            'orderedGrades',
            'availableStrands',
            'availableTracks',
            'availableSections',
            'students',
            'classInfo',
            'classAdviser',
            'subjectTeachers',
            'selectedGrade',
            'selectedStrand',
            'selectedTrack',
            'selectedSection',
            'currentAcademicYear'
        ));
    }
    
    /**
     * Get sections via AJAX for dynamic filtering
     */
    public function getSections(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $strand = $request->get('strand');
        $track = $request->get('track');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel) {
            return response()->json(['success' => false, 'message' => 'Grade level is required']);
        }
        
        $sectionsQuery = Student::where('grade_level', $gradeLevel)
                               ->where('academic_year', $currentAcademicYear)
                               ->where('is_active', true);
        
        if ($strand) {
            $sectionsQuery->where('strand', $strand);
        }
        
        if ($track) {
            $sectionsQuery->where('track', $track);
        }
        
        $sections = $sectionsQuery->distinct()
                                 ->pluck('section')
                                 ->sort()
                                 ->values();
        
        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }
    
    /**
     * Get strands via AJAX for dynamic filtering
     */
    public function getStrands(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel || !in_array($gradeLevel, ['Grade 11', 'Grade 12'])) {
            return response()->json(['success' => true, 'strands' => []]);
        }
        
        $strands = Student::where('grade_level', $gradeLevel)
                         ->where('academic_year', $currentAcademicYear)
                         ->where('is_active', true)
                         ->whereNotNull('strand')
                         ->distinct()
                         ->pluck('strand')
                         ->sort()
                         ->values();
        
        return response()->json([
            'success' => true,
            'strands' => $strands
        ]);
    }
    
    /**
     * Get tracks via AJAX for dynamic filtering
     */
    public function getTracks(Request $request)
    {
        $gradeLevel = $request->get('grade_level');
        $strand = $request->get('strand');
        $currentAcademicYear = date('Y') . '-' . (date('Y') + 1);
        
        if (!$gradeLevel || $strand !== 'TVL') {
            return response()->json(['success' => true, 'tracks' => []]);
        }
        
        $tracks = Student::where('grade_level', $gradeLevel)
                        ->where('strand', 'TVL')
                        ->where('academic_year', $currentAcademicYear)
                        ->where('is_active', true)
                        ->whereNotNull('track')
                        ->distinct()
                        ->pluck('track')
                        ->sort()
                        ->values();
        
        return response()->json([
            'success' => true,
            'tracks' => $tracks
        ]);
    }
}
