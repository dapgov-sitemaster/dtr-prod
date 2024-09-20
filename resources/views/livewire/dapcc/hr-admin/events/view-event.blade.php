<div>
    <div>
        {{ $this->eventInfolist }}
    </div>

    @if($events->tag != App\Enums\Dapcc\Events::HOL->value && $events->tag != App\Enums\Dapcc\Events::FLAG->value && $events->tag != App\Enums\Dapcc\Events::SUS->value)
        <div class="my-4">
            {{ $this->table }}
        </div>
    @endif
</div>
