@foreach($testShift->testSessionSubjects as $testSessionSubject)
    {{ $testSessionSubject->subject->name }}
    @if(!$loop->last), @endif
@endforeach 