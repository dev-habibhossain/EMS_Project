<?php

test('guests visiting home are sent to the staff login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});
