<?php

namespace App\Http\Requests\Client;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
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
        $client = $this->route('client');

        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('clients', 'email')->ignore($client->id),
            ],
        ];

        // Для супер-менеджера/админа можно изменить manager_id
        if (in_array($this->user()->role, [UserRole::SUPER_MANAGER->value, UserRole::ADMIN->value])) {
            $rules['manager_id'] = [
                'sometimes',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', UserRole::MANAGER->value);
                }),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Поле "Имя" должно быть строкой.',
            'name.max' => 'Поле "Имя" не должно превышать 255 символов.',
            'email.email' => 'Поле "Email" должно быть действительным адресом электронной почты.',
            'email.unique' => 'Клиент с таким email уже существует.',
            'manager_id.exists' => 'Выбранный менеджер не существует или не является менеджером.',
        ];
    }
}
