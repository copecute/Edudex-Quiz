@foreach($rooms as $room)
<tr class="searchable-row">
    <td>
        <input type="checkbox" name="room_ids[]" 
               value="{{ $room->id }}" 
               class="form-check-input room-checkbox"
               data-code="{{ $room->code }}"
               data-name="{{ $room->name }}"
               data-facility="{{ $room->facility->name }}"
               data-capacity="{{ $room->capacity }}"
               {{ in_array($room->id, $assignedRoomIds) ? 'checked' : '' }}>
    </td>
    <td>{{ $room->code }}</td>
    <td>{{ $room->name }}</td>
    <td>{{ $room->facility->name }}</td>
    <td>{{ $room->capacity }}</td>
</tr>
@endforeach 