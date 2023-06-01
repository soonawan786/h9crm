@php
    $content = "<div class='d-inline-block mr-1'>
<img class='taskEmployeeImg rounded-circle' src='".$user->image_url."' >
           </div>". htmlentities($user->userBadge());

  if($agent){
      $content .= ' ['.$user->email.'] ';
   }

   if($pill){
       $content = "<span class='badge badge-pill badge-light border'>".$content."</span>";
   }

@endphp
@if ($clientHistoryId==null)
<option
{{ !$selected ?: 'selected' }}
data-content="{!! $content !!}"
value="{{ $userID ?? $user->id }}">
{{ mb_ucfirst($user->name) }}
</option>
@else
<option
data-content="{!! $content !!}"
value="{{ $user->id }}" {{ ($clientHistoryId==$user->id)?'selected':'' }} >
{{ mb_ucfirst($user->name) }}
</option>
@endif

