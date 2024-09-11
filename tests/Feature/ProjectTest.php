<?php
use function Pest\Laravel\{get};

it('has a up page', function () {
    get('/up')->assertStatus(200);
});
