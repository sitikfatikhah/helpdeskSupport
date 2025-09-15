<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Department;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'user_id' => User::factory(),
        'department_id' => Department::factory(),
        'ticket_number' => 'TCK-' . now()->format('y') . $this->faker->unique()->numerify('#####'),
        'open_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
        'close_time' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
        'priority_level' => $this->faker->randomElement(['Low', 'Medium', 'High', 'Critical']),
        'category' => $this->faker->randomElement(['Software', 'Hardware', 'Network', 'Other']),
        'description' => $this->faker->paragraph,
        'type_device' => $this->faker->randomElement(['Laptop', 'Desktop', 'Printer', 'Router']),
        'operation_system' => $this->faker->randomElement(['Windows 10', 'Windows 11', 'macOS', 'Linux']),
        'software_or_application' => $this->faker->randomElement(['Microsoft Word', 'Excel', 'Chrome', 'Zoom']),
        'error_message' => $this->faker->sentence,
        'step_taken' => $this->faker->paragraph,
        'ticket_status' => $this->faker->randomElement(['Open', 'In Progress', 'Resolved', 'Closed']),
    ];
}

}
