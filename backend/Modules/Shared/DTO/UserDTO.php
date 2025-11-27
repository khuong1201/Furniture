<?php
namespace Modules\Shared\DTO;

class UserDTO
{
    public int $id;
    public string $uuid;
    public string $name;
    public string $email;
    public bool $isActive;
    public array $roles;
    public array $permissions;

    public function __construct(
        int $id,
        string $uuid,
        string $name,
        string $email,
        bool $isActive = true,
        array $roles = [],
        array $permissions = []
    ) {
        $this->id = $id;
        $this->uuid = $uuid;
        $this->name = $name;
        $this->email = $email;
        $this->isActive = $isActive;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public static function fromModel($user): self
    {
        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->roles->flatMap(fn($r) => $r->permissions->pluck('name'))->unique()->values()->toArray();
        return new self(
            (int) $user->id,
            (string) $user->uuid,
            (string) $user->name,
            (string) $user->email,
            (bool) ($user->is_active ?? true),
            $roles,
            $permissions
        );
    }
}
