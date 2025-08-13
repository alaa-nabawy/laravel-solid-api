<?php

namespace App\Http\Requests\Auth\Api;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed', // pragma: allowlist secret
            'password_confirmation' => 'required|string', // pragma: allowlist secret
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'                  => 'Name is required',
            'name.max'                       => 'Name cannot exceed 255 characters',
            'email.required'                 => 'Email is required',
            'email.email'                    => 'Please provide a valid email address',
            'email.unique'                   => 'This email is already registered',
            'password.required'              => 'Password is required',
            'password.min'                   => 'Password must be at least 8 characters',
            'password.confirmed'             => 'Password confirmation does not match',
            'password_confirmation.required' => 'Password confirmation is required',
        ];
    }
}
