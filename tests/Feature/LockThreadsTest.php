<?php

namespace Tests\Feature;

use App\Thread;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LockThreadsTest extends TestCase
{
    /** @test */
    public function testNonAdministratorsCannotLockThreads()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create(Thread::class);

        $this->post(route('threads.lock', $thread))
            ->assertStatus(403);

        $this->assertFalse($thread->fresh()->locked, 'Failed asserting that the thread was unlocked.');
    }

    /** @test */
    public function testNonAdministratorsCannotUnlockThreads()
    {
        $this->withExceptionHandling()->signIn();

        $thread = create(Thread::class, ['locked' => true]);

        $this->delete(route('threads.unlock', $thread))
            ->assertStatus(403);

        $this->assertTrue($thread->fresh()->locked, 'Failed asserting that the thread was locked.');
    }

    /** @test */
    public function testAdministratorsCanLockThreads()
    {
        $this->signInAdmin();

        $thread = create(Thread::class);

        $this->post(route('threads.lock', $thread))
            ->assertStatus(204);

        $this->assertTrue($thread->fresh()->locked, 'Failed asserting that the thread was locked.');
    }

    /** @test */
    public function testAdministratorsCanUnlockThreads()
    {
        $this->signInAdmin();

        $thread = create(Thread::class, ['locked' => true]);

        $this->delete(route('threads.unlock', $thread))
            ->assertStatus(204);

        $this->assertFalse($thread->fresh()->locked, 'Failed asserting that the thread was unlocked.');
    }

    /** @test */
    public function testALockedThreadMayNotReceiveNewPosts()
    {
        $this->signIn();

        $thread = create(Thread::class, ['locked' => true]);

        $this->post($thread->path() . '/posts', [
            'body' => 'Foobar',
            'user_id' => Auth::id(),
        ])->assertStatus(422);
    }
}
