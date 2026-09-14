<?php
declare(strict_types=1);

/**
 * Class Room
 * Representing a bookable space / meeting room resource in the system.
 * Demonstrates OOP Encapsulation and Domain Model representation.
 */
class Room
{
    private string $id;
    private string $name;
    private string $type;
    private int $capacity;
    private float $hourlyRate;
    private array $amenities;
    private string $imageUrl;
    private string $description;
    private int $minHours;

    public function __construct(
        string $id,
        string $name,
        string $type,
        int $capacity,
        float $hourlyRate,
        array $amenities = [],
        string $imageUrl = '',
        string $description = '',
        int $minHours = 1
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->capacity = $capacity;
        $this->hourlyRate = $hourlyRate;
        $this->amenities = $amenities;
        $this->imageUrl = $imageUrl;
        $this->description = $description;
        $this->minHours = $minHours;
    }

    // --- Getters (Encapsulation) ---
    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getHourlyRate(): float
    {
        return $this->hourlyRate;
    }

    public function getAmenities(): array
    {
        return $this->amenities;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMinHours(): int
    {
        return $this->minHours;
    }

    /**
     * Calculate base room rental price for a given duration.
     */
    public function calculateBasePrice(float $hours): float
    {
        $billableHours = max($this->minHours, $hours);
        return round($billableHours * $this->hourlyRate, 2);
    }

    /**
     * Check if the room capacity can accommodate the number of attendees.
     */
    public function isSuitableFor(int $attendees): bool
    {
        return $attendees > 0 && $attendees <= $this->capacity;
    }

    /**
     * Convert Room entity to associative array for JSON serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'capacity' => $this->capacity,
            'hourlyRate' => $this->hourlyRate,
            'amenities' => $this->amenities,
            'imageUrl' => $this->imageUrl,
            'description' => $this->description,
            'minHours' => $this->minHours,
        ];
    }

    /**
     * Factory method to reconstruct a Room object from an associative array.
     */
    public static function fromArray(array $data): Room
    {
        return new self(
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['type'] ?? 'Standard Room',
            (int)($data['capacity'] ?? 1),
            (float)($data['hourlyRate'] ?? 0.0),
            (array)($data['amenities'] ?? []),
            $data['imageUrl'] ?? '',
            $data['description'] ?? '',
            (int)($data['minHours'] ?? 1)
        );
    }
}
