<?php

namespace App\Importers;

use CodedSultan\JobEngine\Contracts\ImporterInterface;
use Illuminate\Support\Facades\Hash;

class UserImporter implements ImporterInterface
{
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
        ];
    }

    public function transform(array $row): array
    {
        return [
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => isset($row['password']) ? Hash::make($row['password']) : null,
        ];
    }
}
