<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\CreatingDatabase;
use FalconERP\Skeleton\Falcon;
use FalconERP\Skeleton\Models\Erp\Setting;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Queue\InteractsWithQueue;

class IpCheckNotification // implements ShouldQueueAfterCommit
{
    // use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CreatingDatabase $event): void
    {
        if (!auth()->check()) {
            return;
        }

        if (Setting::byName('datahub_access')->isEmpty()) {
            return;
        }

        $result = Falcon::bigDataService('ip')?->search(request()->ip());

        Setting::updateOrCreate([
            'name' => 'ip_check',
        ], [
            'name'  => 'ip_check',
            'value' => json_encode([
                'email'   => auth()->user()->email,
                'ip'      => request()->ip(),
                'user_id' => auth()->user()->id,
                'result'  => $result,
            ]),
            'description' => 'IP check for user authentication',
        ]);
    }
}
