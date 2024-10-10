@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($slot == 'eDTR')
<img src="{{ asset('image/edtr-banner.png') }}" class="logo" alt="eDTR Banner" style="width: auto;">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
