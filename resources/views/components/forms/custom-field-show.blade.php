@if (isset($fields))
    @foreach ($fields as $field)
        @if (in_array($field->type, ['text', 'password', 'number']))
            <x-cards.data-row
                :label="$field->label"
                :value="$model->custom_fields_data['field_'.$field->id] ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'textarea')
            <x-cards.data-row
                :label="$field->label"
                html="true"
                :value="$model->custom_fields_data['field_'.$field->id] ?? '--'">
            </x-cards.data-row>

        @elseif($field->type == 'radio')
            <x-cards.data-row :label="$field->label"
                              :value="(!is_null($model->custom_fields_data['field_' . $field->id]) ? $model->custom_fields_data['field_' . $field->id] : '--')">
            </x-cards.data-row>

        @elseif($field->type == 'checkbox')
            <x-cards.data-row :label="$field->label"
                              :value="(!is_null($model->custom_fields_data['field_' . $field->id]) ? $model->custom_fields_data['field_' . $field->id] : '--')">
            </x-cards.data-row>

        @elseif($field->type == 'select')
            <x-cards.data-row :label="$field->label"
                              :value="(!is_null($model->custom_fields_data['field_' . $field->id]) && $model->custom_fields_data['field_' . $field->id] != '' ? $field->values[$model->custom_fields_data['field_' . $field->id]] : '--')">
            </x-cards.data-row>

        @elseif($field->type == 'date')
            <x-cards.data-row :label="$field->label"
                              :value="(!is_null($model->custom_fields_data['field_' . $field->id]) && $model->custom_fields_data['field_' . $field->id] != '' ? \Carbon\Carbon::parse($model->custom_fields_data['field_' . $field->id])->translatedFormat(company()->date_format) : '--')">
            </x-cards.data-row>
        @elseif($field->type == 'file')
            @php
                $fileValue = '--';
                if(!is_null($model->custom_fields_data['field_'.$field->id]) && $model->custom_fields_data['field_'.$field->id] != ''){
                    $fileValue = '<a href="'.asset_url_local_s3('custom_fields/' .$model->custom_fields_data['field_'.$field->id]).'" download>'.$model->custom_fields_data['field_'.$field->id].' <i class="fa fa-question-circle" data-toggle="popover" data-placement="top" data-content="' . __('app.downloadableFile') .'" data-html="true" data-trigger="hover"></i></a>';
                }
            @endphp

            <x-cards.data-row
            :label="$field->label"
            :value="$fileValue">
            </x-cards.data-row>
        @endif
    @endforeach
@endif
