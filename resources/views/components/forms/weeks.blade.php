@props([
    'checkedDays'=>[],
    'fieldName'=>'',
    'selectedWeek'=>'',
    'fieldRequired'=>false,
    'type'=>'checkbox'
])

@if($type =='checkbox')

    @foreach(range(0,\Carbon\Carbon::DAYS_PER_WEEK-1) as $day)
        <div {{ $attributes->merge(['class' => 'mr-3 mb-2']) }} >
            <x-forms.checkbox :fieldLabel="now()->startOfWeek($day)->translatedFormat('l')"
                              :fieldName="$fieldName"
                              :checked="in_array($day, $checkedDays)"
                              :fieldId="'open_'.$day" :fieldValue="$day"
            />
        </div>
    @endforeach

@else

    <x-forms.select :fieldLabel="__('modules.attendance.weekStartFrom')" fieldName="week_start_from" fieldId="week_start_from"
                    fieldRequired="true">
        @foreach(range(0,\Carbon\Carbon::DAYS_PER_WEEK-1) as $day)
            <option value="{{$day}}" @selected ($selectedWeek == $day)>
                {{now()->startOfWeek($day)->translatedFormat('l')}}
            </option>
        @endforeach

    </x-forms.select>
 @endif
