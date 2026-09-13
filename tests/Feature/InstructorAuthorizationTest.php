<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Instructor;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InstructorAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'instructor']);
        Role::firstOrCreate(['name' => 'student']);
        Category::factory()->create();
    }

    public function test_admin_can_update_course()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $admin->assignRole('admin');

        $instructorUser = User::factory()->create();
        $instructorUser->assignRole('instructor');
        $instructor = Instructor::create(['user_id' => $instructorUser->id, 'cv' => 'test', 'title' => 'Dr.']);
        
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $response = $this->actingAs($admin)->put("/admin/course/{$course->id}", [
            'title' => 'Updated Title',
        ]);

        // It might fail validation, but it should NOT be 403.
        $response->assertStatus(302);
        // We ensure it didn't return 403 Forbidden.
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_instructor_can_update_own_course()
    {
        $instructorUser = User::factory()->create();
        $instructorUser->assignRole('instructor');
        $instructor = Instructor::create(['user_id' => $instructorUser->id, 'cv' => 'test', 'title' => 'Dr.']);
        
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);

        $response = $this->actingAs($instructorUser)->put("/admin/course/{$course->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(302);
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_instructor_cannot_update_foreign_course()
    {
        $instructorUserA = User::factory()->create();
        $instructorUserA->assignRole('instructor');
        $instructorA = Instructor::create(['user_id' => $instructorUserA->id, 'cv' => 'test', 'title' => 'Dr.']);

        $instructorUserB = User::factory()->create();
        $instructorUserB->assignRole('instructor');
        $instructorB = Instructor::create(['user_id' => $instructorUserB->id, 'cv' => 'test', 'title' => 'Dr.']);
        
        $course = Course::factory()->create(['instructor_id' => $instructorB->id, 'title' => 'Original Title']);

        $response = $this->actingAs($instructorUserA)->put("/admin/course/{$course->id}", [
            'title' => 'Hacked Title',
            'description' => 'A valid description',
            'regular_price' => 10,
        ]);

        $response->assertStatus(403);
        
        // Assert course is unchanged
        $this->assertEquals('Original Title', Course::find($course->id)->title);
    }

    public function test_instructor_cannot_create_course_for_another_instructor()
    {
        $instructorUserA = User::factory()->create();
        $instructorUserA->assignRole('instructor');
        $instructorA = Instructor::create(['user_id' => $instructorUserA->id, 'cv' => 'test', 'title' => 'Dr.']);

        $instructorUserB = User::factory()->create();
        $instructorUserB->assignRole('instructor');
        $instructorB = Instructor::create(['user_id' => $instructorUserB->id, 'cv' => 'test', 'title' => 'Dr.']);

        // A tries to create a course using B's ID
        $response = $this->actingAs($instructorUserA)->post('/admin/course', [
            'title' => 'Hacked Course',
            'instructor_id' => $instructorB->id,
            // Add required fields
            'category_id' => Category::factory()->create()->id,
            'is_active' => 1,
            'is_free' => 1,
            'description' => 'A valid description',
            'regular_price' => 10,
        ]);

        // It may succeed but the instructor_id MUST be overridden to Instructor A
        $course = Course::where('title', 'Hacked Course')->first();
        if ($course) {
            $this->assertEquals($instructorA->id, $course->instructor_id);
            $this->assertNotEquals($instructorB->id, $course->instructor_id);
        }
    }

    public function test_instructor_cannot_create_chapter_on_foreign_course()
    {
        $instructorUserA = User::factory()->create();
        $instructorUserA->assignRole('instructor');
        $instructorA = Instructor::create(['user_id' => $instructorUserA->id, 'cv' => 'test', 'title' => 'Dr.']);

        $instructorUserB = User::factory()->create();
        $instructorUserB->assignRole('instructor');
        $instructorB = Instructor::create(['user_id' => $instructorUserB->id, 'cv' => 'test', 'title' => 'Dr.']);
        
        $courseB = Course::factory()->create(['instructor_id' => $instructorB->id]);

        $response = $this->actingAs($instructorUserA)->post('/admin/chapter', [
            'title' => 'Hacked Chapter',
            'course_id' => $courseB->id,
            'serial_number' => 1,
            'content' => ['test content'],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('chapters', ['title' => 'Hacked Chapter']);
    }

    public function test_instructor_cannot_manage_category()
    {
        $instructorUser = User::factory()->create();
        $instructorUser->assignRole('instructor');
        $instructor = Instructor::create(['user_id' => $instructorUser->id, 'cv' => 'test', 'title' => 'Dr.']);
        
        $category = Category::factory()->create();

        $response = $this->actingAs($instructorUser)->delete("/admin/category/{$category->id}");

        // Middleware role:admin should block
        $response->assertStatus(403);
        
        $response = $this->actingAs($instructorUser)->post('/admin/category', [
            'title' => 'Hacked Category',
        ]);
        
        $response->assertStatus(403);
    }
}
