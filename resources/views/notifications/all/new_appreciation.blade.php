@php
    $iconCode = '<i class="bi bi-'.$notification->data['icon'].' f-12 text-white position-absolute notification-apr-icon"></i>
                    <i class="bi bi-hexagon-fill fs-30 icon-background" style="color: '.$notification->data['color_code'].'"></i>';
    $type = 'icon';
@endphp
<x-cards.notification :notification="$notification"
                      :link="route('appreciations.show', $notification->data['id'])"
                      :image="$iconCode"
                      :title="__('messages.congratulationNewAward', ['award' => ucfirst($notification->data['heading'])]) "
                      :time="$notification->created_at"
                      :type="$type"
/>
