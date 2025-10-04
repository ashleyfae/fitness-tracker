<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\HomepageController
 */
class HomepageControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\HomepageController::__invoke()
     */
    public function test_can_redirect_to_routines(): void
    {
        $response = $this->get(route('home'));

        $response->assertRedirectToRoute('routines.index');
    }
}
