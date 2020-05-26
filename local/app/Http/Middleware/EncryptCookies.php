<?php

namespace Komu4e\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        //
        '/callback/auto',
        '/viber/bot/komu4egrill',
    ];
}
