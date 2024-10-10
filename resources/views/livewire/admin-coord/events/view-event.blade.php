<div>
    @if($event)
        <div>
            {{ $this->eventInfolist }}
        </div>

        @if($event->tag != App\Enums\Events::HOL && $events->tag != App\Enums\Events::FLAG && $events->tag != App\Enums\Events::SUS)
        <div class="my-4">
            {{ $this->table }}
        </div>
        @endif
    @endif
</div>
