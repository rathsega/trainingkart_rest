<?php

namespace App\Models;

use CodeIgniter\Model;

class LessonModel extends Model
{
    protected $table = 'lesson';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'duration', 'course_id', 'section_id', 'chapter_id', 'video_type', 'video_url', 'date_added', 'last_modified', 'lesson_type', 'attachment', 'attachment_type', 'summary', 'is_free', 'order', 'video_type_for_mobile_application', 'video_url_for_mobile_application', 'duration_for_mobile_application', 'cloud_video_id', 'caption', 'quiz_attempt'];
}
