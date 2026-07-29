@props([
    'chart',
    'variant' => 'modal',
])

<div @class([
    'product-size-chart-table-wrap',
    'product-size-chart-table-wrap--inline' => $variant === 'inline',
])>
    <table class="product-size-chart-table">
        <thead>
            <tr>
                <th scope="col" class="product-size-chart-col-size">سایز</th>
                @foreach($chart['columns'] as $column)
                    <th scope="col">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($chart['rows'] as $row)
                <tr>
                    <th scope="row" class="product-size-chart-col-size">{{ $row['size'] }}</th>
                    @foreach($row['values'] as $value)
                        <td @class(['is-empty' => blank($value)]) dir="ltr">{{ filled($value) ? $value : '—' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
