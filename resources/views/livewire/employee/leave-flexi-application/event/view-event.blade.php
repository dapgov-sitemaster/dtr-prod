<div>
    <div>
        {{ $this->eventInfolist }}
    </div>

    @if($events->tag != App\Enums\Events::HOL->value && $events->tag != App\Enums\Events::FLAG->value && $events->tag != App\Enums\Events::SUS->value)
    <div class="my-4">
        {{ $this->table }}
    </div>
    @endif
</div>
