<?php

namespace Database\Seeders;

use App\Enum\NotificationTypeEnum;
use App\Repositories\NotificationRepository;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::NewContentFromCourse->value,
        ], [
            'is_enabled' => true,
            'heading' => 'Add New Content: Keep Your Course Fresh and Engaging',
            'content' => 'New content has been added to your enrolled course {course_title}'
        ]);

        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::NewCourseFromInstructor->value,
        ], [
            'is_enabled' => true,
            'heading' => 'Well Done! Your Course Is Now Ready for Learners Everywhere',
            'content' => 'Your instructor has added a new course {course_title}'
        ]);
        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::NewExamFromCourse->value,
        ], [
            'is_enabled' => true,
            'heading' => 'Are You Ready? New Assessment Is Waiting For You',
            'content' => 'New Exam has added to your enrolled course {course_title}',
        ]);
        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::NewQuizFromCourse->value,
        ], [
            'is_enabled' => true,
            'heading' => 'Are You Ready? New Assessment Is Waiting For You',
            'content' => 'New Quiz has added to your enrolled course {course_title}',
        ]);
        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::CustomNotification->value,
        ], [
            'is_enabled' => true,
            'heading' => 'New notification from ' . config('app.name'),
            'content' => 'Flash Sale! Enroll in {course_title} at 60% Off – 24 Hours Only!',
        ]);
        NotificationRepository::query()->updateOrCreate([
            'type' => NotificationTypeEnum::NewEnrollmentNotification->value,
        ], [
            'is_enabled' => true,
            'heading' => 'New course enrollment',
            'content' => 'Congratulations! You’re now enrolled in {course_title}. Let’s unlock new opportunities together!',
        ]);
    }
}
