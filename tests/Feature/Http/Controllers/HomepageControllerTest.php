<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\HomepageController
 */
class HomepageControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\HomepageController::__invoke()
     */
    public function testCanRedirectToRoutines(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirectToRoute('routines.index');
    }
}
