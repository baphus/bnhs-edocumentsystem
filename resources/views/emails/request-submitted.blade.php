<x-mail::message>
# Request Submitted Successfully

Dear {{ $request->first_name }},

Your document request has been successfully submitted to the BNHS eDocument System. We have received your request and will begin processing it shortly.

## Request Details

<x-mail::panel>
**Tracking Code:** {{ $request->tracking_id }}

**Document Type:** {{ $request->documentType->name }}

**Purpose:** {{ $request->purpose }}

**Quantity:** {{ $request->quantity ?? 1 }}

**Status:** {{ $request->status }}

**Estimated Completion Date:** {{ $request->estimated_completion_date ? $request->estimated_completion_date->format('F d, Y') : 'To be determined' }}

**Date Requested:** {{ $request->created_at->format('F d, Y h:i A') }}
</x-mail::panel>

## Next Steps

Your request is currently under review. We will notify you via email when there are any status updates.

<x-mail::button :url="$dashboardUrl">
View Your Dashboard
</x-mail::button>

**Important:** Please save your tracking code ({{ $request->tracking_id }}) for future reference. You can use it to track your request status at any time.

If you have any questions or concerns, please contact the registrar's office.

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Bato National High School<br>
Toledo City, Cebu
</x-mail::subcopy>
</x-mail::message>

