<?php

namespace app\Traits;

use Illuminate\Http\JsonResponse;


trait ValidationRulesTrait
{
    protected function userValidationRules(): array
    {
        return [
            'username' => ['required', 'string', 'unique:users'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'role' => ['required', 'string', Rule::in(['teacher', 'student'])],  // Handled in the AuthController
            'name' => ['required', 'string', 'unique:users'],
            'phone_number' => ['required', 'string', 'unique:users'],
            'country' => ['required', 'string'],
            'major' => ['required', 'string'],
        ];
    }

    protected function loginValidationRules(): array
    {
        return [
            'email' => 'required_without:username|email',
            'username' => 'required_without:email|string',
            'password' => 'required|string|min:6',
        ];
    }

    protected function studentUpdateValidationRules(): array
    {
        return [
            'name' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
            'major' => ['nullable', 'string'],
        ];
    }

}
