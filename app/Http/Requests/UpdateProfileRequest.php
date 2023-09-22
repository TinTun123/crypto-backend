<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // $user = $this->user();
        
        // return !($user->isVerified && ($this->has('id_front') || $this->has('id_back')));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif,jpg',
                'max:20480'
            ],
            'id_front' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif,jpg',
                'max:20480'
            ],
            'id_back' => [
                'nullable',
                'image',
                'mimes:jpeg,png,gif,jpg',
                'max:20480'
            ],
            'password' => [
                'nullable',
                'confirmed',
                'string',
                'min:8',             // Minimum length of 8 characters (you can adjust this)
                'regex:/[A-Z]/',     // Requires at least one uppercase letter
                'regex:/[a-z]/',     // Requires at least one lowercase letter
                'regex:/[0-9]/',
            ],
            'user_level' => ['nullable', 'integer'],
            'address' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'], // Adjust the max length as needed
            'birthday' => ['nullable', 'date'], // You can add more specific date format rules if needed
            'status' => ['boolean'],
            'isVerified' => ['boolean'],
            'note' => ['string']
        ];
    }

    public function prepareForValidation()
{
    $this->merge([
        'isVerified' => filter_var($this->input('isVerified'), FILTER_VALIDATE_BOOLEAN),
        'status' => filter_var($this->input('status'), FILTER_VALIDATE_BOOLEAN),
    ]);
}
}
