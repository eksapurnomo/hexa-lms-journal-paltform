<?php

namespace App\Enum;

enum NotificationTypeEnum: string
{
    case NewContentFromCourse = 'new_content_from_course';
    case NewCourseFromInstructor = 'new_course_from_instructor';
    case NewExamFromCourse = 'new_exam_from_course';
    case NewQuizFromCourse = 'new_quiz_from_course';
    case CustomNotification = 'new_custom_notification_from_admin';
    case NewEnrollmentNotification = 'new_enrollment_notification';
}
