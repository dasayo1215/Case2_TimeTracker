<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

abstract class UserTestCase extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 一般ユーザー（role = user）を作成
        $this->user = User::factory()->create([
            'role' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // webガード（一般ユーザー用）でログイン状態にする
        $this->actingAs($this->user, 'web');
    }

    /**
     * 一般ユーザーとしてGETリクエストを送る
     */
    protected function getJsonAsUser(string $uri, array $params = [])
    {
        return $this->getJson($uri, $params);
    }

    /**
     * 一般ユーザーとしてPOSTリクエストを送る
     */
    protected function postJsonAsUser(string $uri, array $data = [])
    {
        return $this->postJson($uri, $data);
    }
}
