<?php

namespace App\Listeners;

use App\Models\Pelanggan;
use App\Services\GuestAccountProvisioner;
use Illuminate\Auth\Events\Registered;

class MergeGuestOrdersOnRegister
{
    /**
     * Buat listener baru dengan dependensi GuestAccountProvisioner.
     */
    public function __construct(
        protected GuestAccountProvisioner $provisioner
    ) {}

    /**
     * Tangani event pendaftaran pengguna.
     */
    public function handle(Registered $event): void
    {
        /** @var Pelanggan|null $user */
        $user = $event->user;

        if ($user instanceof Pelanggan) {
            $this->provisioner->mergeOrdersToUser($user);
        }
    }
}

