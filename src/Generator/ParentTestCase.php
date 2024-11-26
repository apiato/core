<?php

namespace Apiato\Core\Generator;

enum ParentTestCase
{
    case UNIT_TEST_CASE;
    case FUNCTIONAL_TEST_CASE;
    case API_TEST_CASE;
    case CLI_TEST_CASE;
    case WEB_TEST_CASE;
}
