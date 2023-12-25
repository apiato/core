<?php

namespace Apiato\Core\Abstracts\Controllers;

abstract class WebController extends Controller
{
    /**
     * The type of this controller. This will be accessibly mirrored in the Actions.
     * Giving each Action the ability to modify it's internal business logic based on the UI type that called it.
     */
    public string $ui = 'web';
}
