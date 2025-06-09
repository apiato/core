<?php

declare(strict_types=1);

if (!function_exists('this_is_a_test_function_to_test_functions_file')) {
    function this_is_a_test_function_to_test_functions_file(): string
    {
        return 'functions file loaded';
    }
}
