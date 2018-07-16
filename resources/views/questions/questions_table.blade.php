<div class="table-responsive">
    <table class="table table-bordered table-striped table-questions">
        <thead>
        <tr>
            <th rowspan="2">ID</th>
            <th rowspan="2">Question</th>
            <th colspan="4">Answers</th>
            <th rowspan="2">Correct Answer</th>
        </tr>
        <tr>
            <th>1</th>
            <th>2</th>
            <th>3</th>
            <th>4</th>
        </tr>
        </thead>
        <tbody>

        @forelse($questions as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->question }}</td>
                <td class="answer-1-{{$item->correct_answer}}">{{ $item->answer1 }}</td>
                <td class="answer-2-{{$item->correct_answer}}">{{ $item->answer2 }}</td>
                <td class="answer-3-{{$item->correct_answer}}">{{ $item->answer3 }}</td>
                <td class="answer-4-{{$item->correct_answer}}">{{ $item->answer4 }}</td>
                <td>{{ $item->correct_answer }}</td>
            </tr>
        @empty
        @endforelse
        </tbody>
    </table>
</div>