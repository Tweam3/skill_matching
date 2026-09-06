<?php

namespace Tests\Feature\Search;

use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'Full_Name' => 'Admin',
            'Email' => 'admin@test.com',
            'Password_Hash' => 'hash',
            'Role' => 'Admin',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
        ]);

        $this->admin = User::where('Email', 'admin@test.com')->first();

        Skill::insert([
            ['Skill_ID' => 1, 'Skill_Title' => 'PHP', 'Category' => 'Programming', 'Subcategory' => 'Backend'],
            ['Skill_ID' => 2, 'Skill_Title' => 'JavaScript', 'Category' => 'Programming', 'Subcategory' => 'Frontend'],
            ['Skill_ID' => 3, 'Skill_Title' => 'Python', 'Category' => 'Programming', 'Subcategory' => 'Backend'],
            ['Skill_ID' => 4, 'Skill_Title' => 'MySQL', 'Category' => 'Database', 'Subcategory' => 'Databases'],
            ['Skill_ID' => 5, 'Skill_Title' => 'React', 'Category' => 'Programming', 'Subcategory' => 'Frontend'],
            ['Skill_ID' => 6, 'Skill_Title' => 'Graphic Design', 'Category' => 'Design', 'Subcategory' => null],
            ['Skill_ID' => 7, 'Skill_Title' => 'SEO', 'Category' => 'Marketing', 'Subcategory' => null],
            ['Skill_ID' => 8, 'Skill_Title' => 'Content Writing', 'Category' => 'Writing', 'Subcategory' => null],
        ]);

        Auth::login($this->admin);
    }

    private function createRequest(int $skillId, string $title, string $serviceMode, string $status = 'Open'): SkillRequest
    {
        $request = SkillRequest::create([
            'User_ID' => $this->admin->User_ID,
            'Skill_ID' => $skillId,
            'Title' => $title,
            'Description' => "Description for {$title}",
            'Status' => $status,
            'Service_Mode' => $serviceMode,
        ]);
        $request->skills()->attach([$skillId]);

        return $request;
    }

    public function test_search_page_renders_with_filters(): void
    {
        $response = $this->get('/search');

        $response->assertStatus(200);
        $response->assertSee('Search Requests');
        $response->assertSee('Service Mode');
        $response->assertSee('Category Tags');
        $response->assertSee('Remote');
        $response->assertSee('Face-to-Face');
    }

    public function test_filter_by_remote_service_mode(): void
    {
        $this->createRequest(1, 'Remote PHP Job', 'Remote');
        $this->createRequest(1, 'Remote JS Job', 'Remote');
        $this->createRequest(6, 'Face-to-Face Design', 'Face-to-Face');
        $this->createRequest(5, 'Hybrid React', 'Hybrid');

        $response = $this->get('/search?service_mode=Remote');

        $response->assertStatus(200);
        $response->assertSee('Remote PHP Job');
        $response->assertSee('Remote JS Job');
        $response->assertDontSee('Face-to-Face Design');
        $response->assertDontSee('Hybrid React');
    }

    public function test_filter_by_face_to_face_service_mode(): void
    {
        $this->createRequest(1, 'Remote PHP Job', 'Remote');
        $this->createRequest(6, 'Face-to-Face Design', 'Face-to-Face');

        $response = $this->get('/search?service_mode=Face-to-Face');

        $response->assertStatus(200);
        $response->assertSee('Face-to-Face Design');
        $response->assertDontSee('Remote PHP Job');
    }

    public function test_filter_by_category_tag(): void
    {
        $this->createRequest(1, 'PHP Remote', 'Remote');
        $this->createRequest(6, 'Design Face-to-Face', 'Face-to-Face');
        $this->createRequest(7, 'SEO Marketing', 'Remote');

        $response = $this->get('/search?categories[]=Programming');

        $response->assertStatus(200);
        $response->assertSee('PHP Remote');
        $response->assertDontSee('Design Face-to-Face');
        $response->assertDontSee('SEO Marketing');
    }

    public function test_filter_by_multiple_category_tags(): void
    {
        $this->createRequest(1, 'PHP Programming', 'Remote');
        $this->createRequest(6, 'Design Design', 'Face-to-Face');
        $this->createRequest(7, 'SEO Marketing', 'Remote');
        $this->createRequest(8, 'Writing Writing', 'Hybrid');

        $response = $this->get('/search?categories[]=Programming&categories[]=Design');

        $response->assertStatus(200);
        $response->assertSee('PHP Programming');
        $response->assertSee('Design Design');
        $response->assertDontSee('SEO Marketing');
        $response->assertDontSee('Writing Writing');
    }

    public function test_combined_service_mode_and_category_filter(): void
    {
        $this->createRequest(1, 'Remote PHP', 'Remote');
        $this->createRequest(1, 'Face-to-Face PHP', 'Face-to-Face');
        $this->createRequest(6, 'Remote Design', 'Remote');

        $response = $this->get('/search?service_mode=Remote&categories[]=Programming');

        $response->assertStatus(200);
        $response->assertSee('Remote PHP');
        $response->assertDontSee('Face-to-Face PHP');
        $response->assertDontSee('Remote Design');
    }

    public function test_keyword_search(): void
    {
        $this->createRequest(1, 'PHP Developer Needed', 'Remote');
        $this->createRequest(6, 'Graphic Designer Wanted', 'Face-to-Face');

        $response = $this->get('/search?keyword=PHP');

        $response->assertStatus(200);
        $response->assertSee('PHP Developer Needed');
        $response->assertDontSee('Graphic Designer Wanted');
    }

    public function test_all_category_tags_displayed_in_filter(): void
    {
        $response = $this->get('/search');

        $response->assertStatus(200);
        $response->assertSee('Programming');
        $response->assertSee('Database');
        $response->assertSee('Frontend');
        $response->assertSee('Design');
        $response->assertSee('Marketing');
        $response->assertSee('Writing');
    }

    public function test_results_are_paginated(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createRequest(1, "Request {$i}", 'Remote');
        }

        $response = $this->get('/search?service_mode=Remote');

        $response->assertStatus(200);
        $response->assertSee('Request 1');
        $response->assertSee('Request 20');
        $response->assertSee('page=2');
    }

    public function test_at_least_four_category_tags_available(): void
    {
        $categories = Skill::whereNotNull('Category')
            ->where('Category', '!=', '')
            ->pluck('Category')
            ->unique()
            ->sort()
            ->values();

        $this->assertGreaterThanOrEqual(4, $categories->count(), 'Should have at least 4 category tags');
    }

    public function test_service_mode_stored_on_request_creation(): void
    {
        $this->post('/requests', [
            'skill_ids' => [1],
            'title' => 'Test Service Mode',
            'description' => 'Test',
            'service_mode' => 'Face-to-Face',
        ]);

        $request = SkillRequest::where('Title', 'Test Service Mode')->first();
        $this->assertNotNull($request);
        $this->assertEquals('Face-to-Face', $request->Service_Mode);
    }

    public function test_search_excludes_non_open_requests(): void
    {
        $this->createRequest(1, 'Open Request', 'Remote', 'Open');
        $this->createRequest(1, 'Completed Request', 'Remote', 'Completed');
        $this->createRequest(1, 'Cancelled Request', 'Remote', 'Cancelled');

        $response = $this->get('/search?service_mode=Remote');

        $response->assertStatus(200);
        $response->assertSee('Open Request');
        $response->assertDontSee('Completed Request');
        $response->assertDontSee('Cancelled Request');
    }

    private function createProvider(string $name, string $email, array $skillIds, float $rating = 4.0, int $completed = 5): User
    {
        $provider = User::create([
            'Full_Name' => $name,
            'Email' => $email,
            'Password_Hash' => 'hash',
            'Role' => 'Student',
            'Is_Verified' => true,
            'Account_Status' => 'Active',
            'Avg_Rating' => $rating,
            'Total_Completed' => $completed,
        ]);
        $provider->skills()->attach($skillIds);

        return $provider;
    }

    public function test_debug_provider_query(): void
    {
        $provider = $this->createProvider('Alice Developer', 'alice@test.com', [1, 2], 4.8, 20);

        $this->assertDatabaseHas('users', ['Full_Name' => 'Alice Developer', 'Role' => 'Student']);

        $found = User::where('Role', '!=', 'Admin')
            ->where('Account_Status', 'Active')
            ->where(function ($q) {
                $q->where('Full_Name', 'like', '%PHP%')
                    ->orWhereHas('skills', function ($sq) {
                        $sq->where('Skill_Title', 'like', '%PHP%');
                    });
            })
            ->first();

        $this->assertNotNull($found, 'Provider should be found by keyword search');
        $this->assertEquals('Alice Developer', $found->Full_Name);
    }

    public function test_debug_provider_view_data(): void
    {
        $provider = $this->createProvider('Alice Developer', 'alice@test.com', [1, 2], 4.8, 20);

        $response = $this->get('/search?search_in=providers&keyword=PHP');

        $response->assertStatus(200);
        $viewData = $response->getOriginalContent()->getData();
        $providers = $viewData['providers'];

        $this->assertFalse($providers->isEmpty());
        $p = $providers->first();
        $this->assertInstanceOf(User::class, $p);
        $this->assertEquals('Alice Developer', $p->Full_Name);
        $this->assertEquals(4.8, $p->Avg_Rating);
        $this->assertNotEmpty($p->skills);
    }

    public function test_search_providers_by_keyword(): void
    {
        $this->createProvider('Alice Developer', 'alice@test.com', [1, 2], 4.8, 20);

        $response = $this->get('/search?search_in=providers&keyword=PHP');

        $response->assertStatus(200);
        $response->assertSee('Alice Developer');
        $response->assertSee('PHP');
    }

    public function test_message_button_shows_for_authenticated_users_on_provider_cards(): void
    {
        $provider = $this->createProvider('Alice Developer', 'alice@test.com', [1, 2], 4.8, 20);

        $response = $this->get('/search?search_in=providers&keyword=PHP');

        $response->assertStatus(200);
        $response->assertSee('Message');
        $response->assertSee('/messages?with=' . $provider->User_ID);
    }

    public function test_search_providers_by_category(): void
    {
        $this->createProvider('Alice Dev', 'alice@test.com', [1, 2], 4.8, 20);
        $this->createProvider('Bob DBA', 'bob@test.com', [4], 4.5, 10);

        $response = $this->get('/search?search_in=providers&categories[]=Programming');

        $response->assertStatus(200);
        $response->assertSee('Alice Dev');
        $response->assertDontSee('Bob DBA');
    }

    public function test_combined_search_returns_both(): void
    {
        $this->createRequest(1, 'Remote PHP Job', 'Remote');
        $this->createProvider('Alice Dev', 'alice@test.com', [1], 4.8, 20);

        $response = $this->get('/search?search_in=both&keyword=PHP');

        $response->assertStatus(200);
        $response->assertSee('Remote PHP Job');
        $response->assertSee('Alice Dev');
    }

    public function test_default_search_only_shows_requests(): void
    {
        $this->createProvider('Hidden Provider', 'hidden@test.com', [1], 4.5, 10);

        $response = $this->get('/search?keyword=PHP');

        $response->assertStatus(200);
        $response->assertDontSee('Hidden Provider');
    }

    public function test_search_in_providers_excludes_admins(): void
    {
        $this->createProvider('Public Provider', 'provider@test.com', [1], 4.5, 10);

        $response = $this->get('/search?search_in=providers');

        $response->assertStatus(200);
        $response->assertSee('Public Provider');
        $response->assertDontSee('admin@test.com');
    }

    public function test_filter_by_subcategory(): void
    {
        $this->createRequest(1, 'PHP Backend Job', 'Remote');
        $this->createRequest(2, 'JS Frontend Job', 'Remote');
        $this->createRequest(6, 'Design Job', 'Face-to-Face');

        $response = $this->get('/search?subcategories[]=Backend');

        $response->assertStatus(200);
        $response->assertSee('PHP Backend Job');
        $response->assertDontSee('JS Frontend Job');
        $response->assertDontSee('Design Job');
    }

    public function test_subcategories_displayed_in_filter(): void
    {
        $response = $this->get('/search');

        $response->assertStatus(200);
        $response->assertSee('Backend');
        $response->assertSee('Frontend');
        $response->assertSee('Databases');
    }

    public function test_admin_can_add_category_from_search(): void
    {
        $response = $this->post('/search/categories', [
            'category' => 'NewCategory',
            'subcategory' => 'NewSub',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('skills', [
            'Category' => 'NewCategory',
            'Subcategory' => 'NewSub',
        ]);
    }

    public function test_admin_can_add_category_without_subcategory(): void
    {
        $response = $this->post('/search/categories', [
            'category' => 'AnotherCategory',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('skills', ['Category' => 'AnotherCategory']);
    }

    public function test_admin_can_register_faculty_user(): void
    {
        $response = $this->post('/admin/users/register', [
            'name' => 'Dr. Faculty',
            'email' => 'faculty@test.com',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
            'role' => 'Faculty',
            'council' => 'HBM',
        ]);

        $response->assertStatus(302);
        $user = User::where('Email', 'faculty@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Faculty', $user->Role);
        $this->assertEquals('HBM', $user->Council);
    }

    public function test_admin_can_register_staff_user(): void
    {
        $response = $this->post('/admin/users/register', [
            'name' => 'Staff Member',
            'email' => 'staff@test.com',
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
            'role' => 'Staff',
        ]);

        $response->assertStatus(302);
        $user = User::where('Email', 'staff@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Staff', $user->Role);
    }
}
