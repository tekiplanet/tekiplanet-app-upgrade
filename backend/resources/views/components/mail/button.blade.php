<table style="width: 100%; text-align: center; margin: 24px 0;">
    <tr>
        <td>
            <a href="{{ $url }}" class="button" target="_blank" style="background-color: {{ $color ?? config('app.primary_color', '#4f46e5') }};">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table> 