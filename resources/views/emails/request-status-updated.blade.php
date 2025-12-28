<x-mail::message>
# Request Status Updated

Dear {{ $request->first_name }},

Your document request status has been updated.

## Status Update

<x-mail::panel>
**Tracking Code:** {{ $request->tracking_id }}

**Previous Status:** {{ $oldStatus }}

**Current Status:** {{ $request->status }}

**Document Type:** {{ $request->documentType->name }}

@if($request->estimated_completion_date)
**Estimated Completion Date:** {{ $request->estimated_completion_date->format('F d, Y') }}
@endif

@if($request->completed_at)
**Date Completed:** {{ $request->completed_at->format('F d, Y h:i A') }}
@endif

@if($request->admin_notes)
**Admin Notes:**
{{ $request->admin_notes }}
@endif
</x-mail::panel>

## Status Description

@if($request->status === 'Pending')
Your request is currently pending review. We will begin processing it shortly.
@elseif($request->status === 'Verified')
Your request has been verified and is ready for processing.
@elseif($request->status === 'Processing')
Your document is currently being prepared. This may take a few business days.
@elseif($request->status === 'Ready')
Your document is ready for pickup! Please visit the registrar's office during office hours.
@elseif($request->status === 'Completed')
Your request has been completed. Thank you for using the BNHS eDocument System!
@elseif($request->status === 'Rejected')
Unfortunately, your request has been rejected. Please check the admin notes above for more information or contact the registrar's office.
@else
Your request status has been updated. Please check your dashboard for more details.
@endif

<x-mail::button :url="$dashboardUrl">
View Your Dashboard
</x-mail::button>

If you have any questions or concerns, please contact the registrar's office.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Bato National High School<br>
Toledo City, Cebu
</x-mail::subcopy>
</x-mail::message>

