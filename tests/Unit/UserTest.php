<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_initials_use_first_letters_of_first_and_last_name(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Arvinne',
            'last_name' => 'Perez',
        ]);

        $this->assertSame('AP', $user->initials());
        $this->assertSame('Arvinne Perez', $user->name);
    }
}
