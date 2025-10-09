
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'face_encoding',
        'face_image_data',
        'face_image_mime_type',
        'confidence_score',
        'face_landmarks',
        'source',
        'registered_at',
        'registered_by',
        'is_active',
        'device_id'
    ];

    protected $casts = [
        'face_encoding' => 'array',
        'face_landmarks' => 'array',
        'registered_at' => 'datetime',
        'is_active' => 'boolean',
        'confidence_score' => 'float',
    ];

    protected $dates = [
        'registered_at'
    ];

    /**
     * Get the student that owns the face registration
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who registered the face
     */
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    /**
     * Scope a query to only include active registrations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include registrations for a specific student
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Get face image as data URL for display
     */
    public function getFaceImageDataUrlAttribute()
    {
        if ($this->face_image_data && $this->face_image_mime_type) {
            return "data:{$this->face_image_mime_type};base64,{$this->face_image_data}";
        }
        return null;
    }

    /**
     * Check if face registration has image data
     */
    public function hasFaceImage()
    {
        return !empty($this->face_image_data);
    }

    /**
     * Create face registration from student ID photo
     */
    public static function createFromStudentIdPhoto(Student $student, array $faceEncoding, $confidenceScore = 0.0)
    {
        return self::create([
            'student_id' => $student->id,
            'face_encoding' => $faceEncoding,
            'face_image_data' => $student->id_photo,
            'face_image_mime_type' => $student->id_photo_mime_type,
            'confidence_score' => $confidenceScore,
            'source' => 'id_photo',
            'registered_at' => now(),
            'is_active' => true
        ]);
    }
}
