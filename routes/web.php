<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Task Manager API',
        'version' => 'v1',
        'status' => 'active',
        'note'    => 'This API is in read-only mode. Add, update, and delete operations are disabled. Use the test credentials below to explore the API.',
        'test_credentials' => [
            'email'    => 'admin@example.com',
            'password' => 'admin123',
            'note'     => 'Login via POST /api/v1/login to get a Bearer token, then use it to access protected endpoints.',
        ],
        'endpoints' => [
            'auth' => [
                'register' => 'POST /api/v1/register',
                'login' => 'POST /api/v1/login',
                'logout' => 'POST /api/v1/logout',
                'verify_email' => 'GET /api/v1/email/verify/{id}/{hash}',
                'resend_verification' => 'POST /api/v1/email/verification-notification',
            ],
            'tasks' => [
                'list' => 'GET /api/v1/tasks',
                'create' => 'POST /api/v1/tasks',
                'show' => 'GET /api/v1/tasks/{id}',
                'update' => 'PUT /api/v1/tasks/{id}',
                'delete' => 'DELETE /api/v1/tasks/{id}',
                'restore' => 'POST /api/v1/tasks/{id}/restore',
            ],
            'categories' => [
                'list' => 'GET /api/v1/categories',
                'create' => 'POST /api/v1/categories',
                'show' => 'GET /api/v1/categories/{id}',
                'update' => 'PUT /api/v1/categories/{id}',
                'delete' => 'DELETE /api/v1/categories/{id}',
                'restore' => 'POST /api/v1/categories/{id}/restore',
            ],
            'notifications' => [
                'list' => 'GET /api/v1/notifications',
                'mark_read' => 'POST /api/v1/notifications/{id}/read',
            ],
        ],
        'docs' => null,
    ]);
});
