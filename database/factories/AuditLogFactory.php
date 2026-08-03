<?php

namespace Database\Factories;

use App\Enums\LogAction;
use App\Enums\LogCategory;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        $action = $this->faker->randomElement(LogAction::cases());
        
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'action' => $action,
            'category' => $action->category(),
            'severity' => $action->defaultSeverity(),
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
            'device_fingerprint' => hash('sha256', $this->faker->uuid),
            'url' => $this->faker->url,
            'method' => $this->faker->randomElement(['GET', 'POST', 'PATCH', 'DELETE']),
            'payload' => ['data' => 'sanitized'],
            'description' => $this->faker->sentence,
            'created_at' => $this->faker->dateTimeBetween('-30 days'),
        ];
    }
}