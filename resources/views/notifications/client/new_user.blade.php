<x-cards.notification :notification="$notification"  link="javascript:;" :image="company()->logo_url" :title="$companyName"
    :time="$notification->created_at" />
