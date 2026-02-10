<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_email_is_required(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'invalid-email',
            'password' => 'password',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    #[Test]
    public function it_validates_password_is_required(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
        ], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    public function it_passes_validation_with_valid_data(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'password' => 'password123',
        ], $rules);

        $this->assertFalse($validator->fails());
    }
}

