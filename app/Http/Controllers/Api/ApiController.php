<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="SecureShare API",
 *     version="1.0.0",
 *     description="Secure Document Collaboration Platform API with RESTful endpoints for project management, document sharing, and team collaboration.",
 *     @OA\Contact(
 *         email="support@secureshare.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Development Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 */
class ApiController extends Controller
{
    //
}
