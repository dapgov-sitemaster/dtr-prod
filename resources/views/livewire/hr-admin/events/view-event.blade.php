<div>
    @if($event)
        <div>
            {{ $this->eventInfolist }}
        </div>
        @if($event->tag != App\Enums\Events::HOL->value && $event->tag != App\Enums\Events::FLAG->value && $event->tag != App\Enums\Events::SUS->value)
        <div class="my-4">
            {{ $this->table }}
        </div>
        @endif
    @endif
</div>
