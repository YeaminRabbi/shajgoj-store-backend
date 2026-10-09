<?php

test('the homepage redirects to the admin dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin');
});
