<?php

namespace Apiato\Core\Controllers;

use Apiato\Core\Models\Model;
use Apiato\Core\Transformers\Transformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Spatie\Fractal\Facades\Fractal;

abstract class ApiController extends Controller
{
}
